<?php

namespace App\Http\Controllers;

use App\Models\Cuadrilla;
use App\Models\Ruta;
use App\Models\Vehiculo;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CuadrillaController extends Controller
{
    private const RELATIONS = ['integrantes', 'vehiculos', 'rutas'];

    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Cuadrilla::with(self::RELATIONS)->orderBy('idCuadrilla')->get()->map(fn ($cuadrilla) => $this->datos($cuadrilla))->values()]);
    }

    public function show(Cuadrilla $cuadrilla): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->datos($cuadrilla->load(self::RELATIONS))]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validar($request);

        $cuadrilla = DB::transaction(function () use ($request, $validated) {
            $cuadrilla = new Cuadrilla;
            $this->guardar($cuadrilla, $validated, $request);

            return $cuadrilla;
        });

        return response()->json(['success' => true, 'mensaje' => 'Cuadrilla creada correctamente', 'data' => $this->datos($cuadrilla->load(self::RELATIONS))], 201);
    }

    public function update(Request $request, Cuadrilla $cuadrilla): JsonResponse
    {
        $validated = $this->validar($request);

        DB::transaction(function () use ($request, $cuadrilla, $validated) {
            $locked = Cuadrilla::whereKey($cuadrilla->getKey())->lockForUpdate()->firstOrFail();
            $actual = $this->datos($locked->load(self::RELATIONS));
            $this->guardar($locked, array_replace($actual, $validated), $request);
        });

        return response()->json(['success' => true, 'mensaje' => 'Cuadrilla actualizada correctamente', 'data' => $this->datos($cuadrilla->fresh(self::RELATIONS))]);
    }

    private function validar(Request $request): array
    {
        $required = $request->isMethod('POST') ? 'required' : 'sometimes';

        return $request->validate([
            'idVehiculo' => [$required, 'required', 'integer', 'exists:vehiculos,idVehiculo'],
            'idChofer' => [$required, 'required', 'integer', Rule::exists('usuario', 'idUsu')->where('tipo', 'Chofer')],
            'idResponsable' => [$required, 'required', 'integer', Rule::exists('usuario', 'idUsu')->where('tipo', 'Operario')],
            'operarios' => ['sometimes', 'array', 'max:2'],
            'operarios.*' => ['required', 'integer', 'distinct', Rule::exists('usuario', 'idUsu')->where('tipo', 'Operario')],
            'rutas' => ['sometimes', 'array'],
            'rutas.*' => ['required', 'integer', 'distinct', 'exists:ruta,idRuta'],
        ]);
    }

    private function datos(Cuadrilla $cuadrilla): array
    {
        return [
            'idCuadrilla' => $cuadrilla->idCuadrilla,
            'idVehiculo' => $cuadrilla->vehiculos->first()?->idVehiculo,
            'idChofer' => $cuadrilla->integrantes->firstWhere('tipo', 'Chofer')?->idUsu,
            'idResponsable' => $cuadrilla->integrantes->first(fn ($u) => $u->tipo === 'Operario' && $u->pivot->esResponsable)?->idUsu,
            'operarios' => $cuadrilla->integrantes->filter(fn ($u) => $u->tipo === 'Operario' && ! $u->pivot->esResponsable)->pluck('idUsu')->values()->all(),
            'rutas' => $cuadrilla->rutas->pluck('idRuta')->unique()->values()->all(),
        ];
    }

    private function guardar(Cuadrilla $cuadrilla, array $data, Request $request): void
    {
        $vehiculo = Vehiculo::whereKey($data['idVehiculo'])->lockForUpdate()->firstOrFail();
        if ($vehiculo->estado !== 'Disponible') {
            throw ValidationException::withMessages(['idVehiculo' => 'El vehículo debe estar disponible.']);
        }
        $operarios = collect($data['operarios'] ?? [])->map(fn ($id) => (int) $id)->all();
        if (in_array((int) $data['idResponsable'], $operarios, true) || count($operarios) > ($vehiculo->tipo === 'Camioneta' ? 2 : 0)) {
            throw ValidationException::withMessages(['operarios' => 'No repita al responsable; solo una camioneta admite hasta dos operarios adicionales.']);
        }
        $rutas = collect($data['rutas'] ?? [])->all();
        $residuo = match ($vehiculo->tipo) {
            'Camión recolector mixtos' => 'Mixtos',
            'Camión recolector reciclables' => 'Reciclables',
            default => null,
        };
        if ($residuo && Ruta::whereIn('idRuta', $rutas)->where('tipoResiduo', '<>', $residuo)->exists()) {
            throw ValidationException::withMessages(['rutas' => 'Hay rutas incompatibles con el vehículo.']);
        }
        // Una composición en uso no puede cambiar el vehículo ni sus integrantes.
        if ($cuadrilla->exists) {
            $actual = $this->datos($cuadrilla->load(self::RELATIONS));
            $miembrosActuales = $cuadrilla->integrantes->pluck('idUsu')->sort()->values()->all();
            $miembrosNuevos = collect([$data['idChofer'], $data['idResponsable'], ...$operarios])->map(fn ($id) => (int) $id)->sort()->values()->all();
            $cambia = (int) $actual['idVehiculo'] !== (int) $data['idVehiculo'] || $miembrosActuales !== $miembrosNuevos || (int) $actual['idResponsable'] !== (int) $data['idResponsable'];
            $enUso = DB::table('atiende')->where('idCuadrilla', $cuadrilla->getKey())->whereNull('fechaHoraFin')->exists()
                || DB::table('cumple_recoleccion')->where('idCuadrilla', $cuadrilla->getKey())->whereNull('fechaHoraFin')->exists()
                || DB::table('cumple_barredora')->where('idCuadrilla', $cuadrilla->getKey())->whereNull('fechaHoraFin')->exists();
            if ($cambia && $enUso) {
                throw ValidationException::withMessages(['idCuadrilla' => 'La cuadrilla tiene una operación en curso.']);
            }
        }
        $cuadrilla->save();
        $miembros = [(int) $data['idChofer'] => ['esResponsable' => false], (int) $data['idResponsable'] => ['esResponsable' => true]];
        foreach ($operarios as $id) {
            $miembros[$id] = ['esResponsable' => false];
        }
        $cuadrilla->integrantes()->sync($miembros);
        $cuadrilla->vehiculos()->sync([$vehiculo->getKey()]);
        if ($cuadrilla->rutas->pluck('idRuta')->map(fn ($id) => (int) $id)->sort()->values()->all() !== collect($rutas)->map(fn ($id) => (int) $id)->sort()->values()->all()) {
            $cuadrilla->rutas()->syncWithPivotValues($rutas, ['idUsu' => $request->user('api')->idUsu]);
        }
    }

    public function destroy(Cuadrilla $cuadrilla): JsonResponse
    {
        try {
            DB::transaction(function () use ($cuadrilla) {
                $cuadrilla = Cuadrilla::whereKey($cuadrilla->getKey())->lockForUpdate()->firstOrFail();
                $cuadrilla->rutas()->detach();
                $cuadrilla->delete();
            });
        } catch (QueryException $e) {
            if (! in_array((int) ($e->errorInfo[1] ?? 0), [1451, 19], true)) {
                throw $e;
            }

            return response()->json(['success' => false, 'mensaje' => 'La cuadrilla tiene operaciones asociadas y no puede eliminarse.'], 409);
        }

        return response()->json(['success' => true, 'mensaje' => 'Cuadrilla eliminada correctamente']);
    }
}
