<?php

namespace App\Http\Controllers;

use App\Models\Cuadrilla;
use App\Models\Incidencia;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncidenciaController extends Controller
{
    private const RELATIONS = ['vehiculos', 'contenedores', 'reportantes', 'cuadrillas'];

    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Incidencia::with(self::RELATIONS)->orderByDesc('idIncidencia')->get()->map(fn ($incidencia) => $this->datos($incidencia))->values()]);
    }

    public function show(Incidencia $incidencia): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->datos($incidencia->load(self::RELATIONS))]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validar($request);

        $incidencia = DB::transaction(function () use ($request, $validated) {
            $incidencia = new Incidencia;
            $this->guardar($incidencia, $validated, $request);
            $incidencia->reportantes()->attach($request->user('api')->idUsu);

            return $incidencia;
        });

        return response()->json(['success' => true, 'mensaje' => 'Incidencia creada correctamente', 'data' => $this->datos($incidencia->load(self::RELATIONS))], 201);
    }

    public function update(Request $request, Incidencia $incidencia): JsonResponse
    {
        $validated = $this->validar($request);

        DB::transaction(function () use ($request, $incidencia, $validated) {
            $locked = Incidencia::whereKey($incidencia->getKey())->lockForUpdate()->firstOrFail();
            $this->guardar($locked, $validated, $request);
            $locked->controladores()->syncWithoutDetaching([$request->user('api')->idUsu]);
        });

        return response()->json(['success' => true, 'mensaje' => 'Incidencia actualizada correctamente', 'data' => $this->datos($incidencia->fresh(self::RELATIONS))]);
    }

    private function validar(Request $request): array
    {
        $required = $request->isMethod('POST') ? 'required' : 'sometimes';

        return $request->validate([
            'tipo' => [$required, 'required', Rule::in([...Incidencia::TIPOS_VEHICULO, ...Incidencia::TIPOS_CONTENEDOR])],
            'prioridad' => [$required, 'required', Rule::in(['Alta', 'Media', 'Baja'])],
            'estado' => ['sometimes', 'required', Rule::in(['Pendiente', 'En curso', 'Resuelta'])],
            'idVehiculo' => ['sometimes', 'nullable', 'integer', 'exists:vehiculos,idVehiculo'],
            'idContenedor' => ['sometimes', 'nullable', 'integer', 'exists:contenedor,idContenedor'],
            'idCuadrilla' => ['sometimes', 'nullable', 'integer', 'exists:cuadrilla,idCuadrilla'],
            'fechaCreacion' => ['prohibited'],
            'fechaResolucion' => ['prohibited'],
            'idUsu' => ['prohibited'],
        ]);
    }

    private function datos(Incidencia $incidencia): array
    {
        $cuadrilla = $incidencia->cuadrillas->sortByDesc('pivot.idAtencion')->first();

        return [
            'idIncidencia' => $incidencia->idIncidencia,
            'tipo' => $incidencia->tipo,
            'prioridad' => $incidencia->prioridad,
            'estado' => $incidencia->estado,
            'fechaCreacion' => $incidencia->fechaCreacion?->format('Y-m-d H:i:s'),
            'fechaResolucion' => $incidencia->fechaResolucion?->format('Y-m-d H:i:s'),
            'vehiculo' => $incidencia->vehiculos->first(),
            'contenedor' => $incidencia->contenedores->first(),
            'idCuadrilla' => $cuadrilla?->idCuadrilla,
            'reportantes' => $incidencia->reportantes->pluck('idUsu')->all(),
        ];
    }

    private function guardar(Incidencia $incidencia, array $data, Request $request): void
    {
        $incidencia->load(self::RELATIONS);
        $idVehiculo = array_key_exists('idVehiculo', $data) ? $data['idVehiculo'] : $incidencia->vehiculos->first()?->idVehiculo;
        $idContenedor = array_key_exists('idContenedor', $data) ? $data['idContenedor'] : $incidencia->contenedores->first()?->idContenedor;
        if (($idVehiculo !== null) === ($idContenedor !== null)) {
            throw ValidationException::withMessages(['idVehiculo' => 'Debe indicar exactamente un vehículo o un contenedor.']);
        }
        $tipo = $data['tipo'] ?? $incidencia->tipo;
        if (! in_array($tipo, $idVehiculo !== null ? Incidencia::TIPOS_VEHICULO : Incidencia::TIPOS_CONTENEDOR, true)) {
            throw ValidationException::withMessages(['tipo' => 'El tipo de incidencia no corresponde al elemento reportado.']);
        }
        $estado = $data['estado'] ?? $incidencia->estado ?? 'Pendiente';
        $administrador = Usuario::findOrFail($request->user('api')->idUsu)->tipo === 'Administrador';
        if (! $administrador && ($estado !== 'Pendiente' || array_key_exists('idCuadrilla', $data))) {
            throw ValidationException::withMessages(['estado' => 'Solo un administrador puede asignar cuadrillas o cambiar el estado.']);
        }
        $atencion = DB::table('atiende')->where('idIncidencia', $incidencia->getKey())->whereNull('fechaHoraFin')->first();
        if ($atencion && $estado === 'Pendiente') {
            throw ValidationException::withMessages(['estado' => 'Una incidencia con atención activa debe permanecer En curso o resolverse.']);
        }
        if ($estado === 'Resuelta' && array_key_exists('idCuadrilla', $data)) {
            throw ValidationException::withMessages(['idCuadrilla' => 'No se puede asignar una incidencia resuelta.']);
        }
        if ($estado === 'En curso' && ! $atencion && empty($data['idCuadrilla'])) {
            throw ValidationException::withMessages(['estado' => 'Para iniciar la atención debe asignar una cuadrilla.']);
        }
        if ($incidencia->exists && $incidencia->cuadrillas->isNotEmpty()
            && ((int) $idVehiculo !== (int) $incidencia->vehiculos->first()?->idVehiculo
                || (int) $idContenedor !== (int) $incidencia->contenedores->first()?->idContenedor)) {
            throw ValidationException::withMessages(['idVehiculo' => 'No se puede cambiar el elemento de una incidencia con historial de atención.']);
        }
        $cuadrilla = null;
        $vehiculoAtencion = null;
        if (! empty($data['idCuadrilla'])) {
            $cuadrilla = Cuadrilla::whereKey($data['idCuadrilla'])->lockForUpdate()->firstOrFail();
            $vehiculoAtencion = $cuadrilla->vehiculos()->lockForUpdate()->first();
            if (! $vehiculoAtencion || $vehiculoAtencion->estado !== 'Disponible') {
                throw ValidationException::withMessages(['idCuadrilla' => 'La cuadrilla debe tener un vehículo disponible.']);
            }
            if ($atencion && (int) $atencion->idCuadrilla !== (int) $cuadrilla->getKey()) {
                throw ValidationException::withMessages(['idCuadrilla' => 'La incidencia ya tiene una atención activa. Resuélvala antes de asignar otra.']);
            }
            $ocupado = DB::table('atiende')->whereNull('fechaHoraFin')->where('idIncidencia', '<>', $incidencia->getKey() ?? 0)
                ->where(fn ($q) => $q->where('idCuadrilla', $cuadrilla->getKey())->orWhere('idVehiculo', $vehiculoAtencion->getKey()))->exists();
            $ocupado = $ocupado || DB::table('cumple_recoleccion')->whereNull('fechaHoraFin')
                ->where(fn ($q) => $q->where('idCuadrilla', $cuadrilla->getKey())->orWhere('idVehiculo', $vehiculoAtencion->getKey()))->exists()
                || DB::table('cumple_barredora')->whereNull('fechaHoraFin')
                    ->where(fn ($q) => $q->where('idCuadrilla', $cuadrilla->getKey())->orWhere('idVehiculo', $vehiculoAtencion->getKey()))->exists();
            if ($ocupado) {
                throw ValidationException::withMessages(['idCuadrilla' => 'La cuadrilla o su vehículo tiene otra operación en curso.']);
            }
            $estado = 'En curso';
        } elseif (array_key_exists('idCuadrilla', $data) && $atencion) {
            throw ValidationException::withMessages(['idCuadrilla' => 'No se puede quitar una atención activa; conserve su historial resolviendo la incidencia.']);
        }
        $incidencia->fill(collect($data)->only(['tipo', 'prioridad'])->all());
        $incidencia->estado = $estado;
        $incidencia->fechaCreacion ??= now();
        $fin = now();
        if ($atencion && $fin->lte($atencion->fechaHoraIni)) {
            $fin = Carbon::parse($atencion->fechaHoraIni)->addSecond();
        }
        $incidencia->fechaResolucion = $estado === 'Resuelta' ? ($incidencia->fechaResolucion ?? $fin) : null;
        $incidencia->save();
        $incidencia->vehiculos()->sync($idVehiculo !== null ? [$idVehiculo] : []);
        $incidencia->contenedores()->sync($idContenedor !== null ? [$idContenedor] : []);
        if ($cuadrilla && ! $atencion) {
            $incidencia->cuadrillas()->attach($cuadrilla->getKey(), ['idVehiculo' => $vehiculoAtencion->getKey(), 'fechaHoraIni' => now()]);
        }
        if ($estado === 'Resuelta') {
            DB::table('atiende')->where('idIncidencia', $incidencia->getKey())->whereNull('fechaHoraFin')->update(['fechaHoraFin' => $fin]);
        }
    }

    public function destroy(Incidencia $incidencia): JsonResponse
    {
        try {
            DB::transaction(function () use ($incidencia) {
                $incidencia = Incidencia::whereKey($incidencia->getKey())->lockForUpdate()->firstOrFail();
                $incidencia->vehiculos()->detach();
                $incidencia->contenedores()->detach();
                $incidencia->reportantes()->detach();
                $incidencia->controladores()->detach();
                $incidencia->delete();
            });
        } catch (QueryException $e) {
            if (! in_array((int) ($e->errorInfo[1] ?? 0), [1451, 19], true)) {
                throw $e;
            }

            return response()->json(['success' => false, 'mensaje' => 'La incidencia tiene un historial de atención y no puede eliminarse.'], 409);
        }

        return response()->json(['success' => true, 'mensaje' => 'Incidencia eliminada correctamente']);
    }
}
