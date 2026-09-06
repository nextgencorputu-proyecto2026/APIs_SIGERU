<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Camion;

class CamionController extends Controller
{
    /**
     * Mostrar todos los camiones de la flota.
     */
    public function index()
    {
        $camiones = Camion::whereIn('tipo', [
            'Camión recolector mixtos',
            'Camión recolector reciclables',
            'Camión de traslado',
            'Camioneta',
            'Barredora',
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $camiones,
        ]);
    }

    /**
     * Crear un nuevo camión.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'matricula' => 'required|string|max:12|unique:vehiculos,matricula',
            'marca' => 'required|string|max:40',
            'tipo' => 'required|in:Camión recolector mixtos,Camión recolector reciclables,Camión de traslado,Camioneta,Barredora',
            'estado' => 'required|in:Disponible,En mantenimiento,Inhabilitado',
            'capacidad' => 'nullable|numeric|min:0.01',
        ]);

        if (
            in_array($validated['tipo'], [
                'Camión recolector mixtos',
                'Camión recolector reciclables',
                'Camión de traslado',
            ]) && (!isset($validated['capacidad']) || $validated['capacidad'] <= 0)
        ) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Los camiones deben tener una capacidad mayor a 0',
            ], 422);
        }

        if (in_array($validated['tipo'], ['Camioneta', 'Barredora'])) {
            $validated['capacidad'] = null;
        }

        $camion = Camion::create($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Vehículo creado correctamente',
            'data' => $camion,
        ], 201);
    }

    /**
     * Mostrar un camión específico.
     */
    public function show(string $id)
    {
        $camion = Camion::find($id);

        if (!$camion) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Vehículo no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $camion,
        ]);
    }

    /**
     * Actualizar un camión.
     */
    public function update(Request $request, string $id)
    {
        $camion = Camion::find($id);

        if (!$camion) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Vehículo no encontrado',
            ], 404);
        }

        $validated = $request->validate([
            'matricula' => 'sometimes|required|string|max:12|unique:vehiculos,matricula,' . $id . ',idVehiculo',
            'marca' => 'sometimes|required|string|max:40',
            'tipo' => 'sometimes|required|in:Camión recolector mixtos,Camión recolector reciclables,Camión de traslado,Camioneta,Barredora',
            'estado' => 'sometimes|required|in:Disponible,En mantenimiento,Inhabilitado',
            'capacidad' => 'nullable|numeric|min:0.01',
        ]);

        $tipoFinal = $validated['tipo'] ?? $camion->tipo;

        if (
            in_array($tipoFinal, [
                'Camión recolector mixtos',
                'Camión recolector reciclables',
                'Camión de traslado',
            ])
        ) {
            $capacidadFinal = $validated['capacidad'] ?? $camion->capacidad;

            if ($capacidadFinal === null || $capacidadFinal <= 0) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'Los camiones deben tener una capacidad mayor a 0',
                ], 422);
            }

            $validated['capacidad'] = $capacidadFinal;
        }

        if (in_array($tipoFinal, ['Camioneta', 'Barredora'])) {
            $validated['capacidad'] = null;
        }

        $camion->update($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Vehículo actualizado correctamente',
            'data' => $camion,
        ]);
    }

    /**
     * Eliminar un camión.
     */
    public function destroy(string $id)
    {
        $camion = Camion::find($id);

        if (!$camion) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Vehículo no encontrado',
            ], 404);
        }

        $camion->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Vehículo eliminado correctamente',
        ]);
    }
}