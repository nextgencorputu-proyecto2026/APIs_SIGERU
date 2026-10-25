<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class RutaController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tipoResiduo' => 'nullable|in:Mixtos,Reciclables',
        ]);

        $rutas = Ruta::query()
            ->when(
                $validated['tipoResiduo'] ?? null,
                fn($query, $tipo) => $query->where('tipoResiduo', $tipo)
            )
            ->orderBy('descripcion')
            ->get(['idRuta', 'descripcion', 'horario', 'tipoResiduo']);

        return response()->json([
            'success' => true,
            'data' => $rutas
        ]);
    }

    public function show($id)
    {
        $ruta = Ruta::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ruta
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'horario' => 'required|date_format:H:i',
            'tipoResiduo' => 'required|in:Mixtos,Reciclables',
        ]);

        $id = DB::table('ruta')->insertGetId($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Ruta creada correctamente',
            'data' => Ruta::findOrFail($id)
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $ruta = Ruta::findOrFail($id);

        $validated = $request->validate([
            'descripcion' => 'sometimes|required|string|max:255',
            'horario' => 'sometimes|required|date_format:H:i',
            'tipoResiduo' => 'sometimes|required|in:Mixtos,Reciclables',
        ]);

        if (empty($validated)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Debe proporcionar al menos un campo para actualizar'
            ], 422);
        }

        // Verificar si se intenta cambiar el tipo de residuo.
        if (
            isset($validated['tipoResiduo']) &&
            $validated['tipoResiduo'] !== $ruta->tipoResiduo
        ) {
            // Comprobar si existen contenedores incompatibles asociados.
            $tieneContenedores = DB::table('contenedor')
                ->where('idRuta', $ruta->idRuta)
                ->where('tipo', '!=', $validated['tipoResiduo'])
                ->exists();

            if ($tieneContenedores) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se puede cambiar el tipo de residuo de la ruta porque tiene contenedores incompatibles asociados.'
                ], 409);
            }
        }

        DB::table('ruta')
            ->where('idRuta', $ruta->idRuta)
            ->update($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Ruta actualizada correctamente',
            'data' => $ruta->fresh()
        ]);
    }


    public function destroy($id)
    {
        $ruta = Ruta::findOrFail($id);

        $tablasRelacionadas = [
            'asigna_ruta',
            'contenedor',
            'cumple_barredora',
            'cumple_recoleccion',
            'se_realiza_en',
        ];

        foreach ($tablasRelacionadas as $tabla) {
            if (DB::table($tabla)->where('idRuta', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'No se puede eliminar la ruta porque tiene registros relacionados'
                ], 409);
            }
        }

        try {
            $ruta->delete();
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se puede eliminar la ruta porque está siendo utilizada'
            ], 409);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Ruta eliminada correctamente'
        ]);
    }
}
