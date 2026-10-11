<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CentroAcopio;
use Illuminate\Database\QueryException;
use Throwable;

class CentroAcopioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $centrosAcopio = CentroAcopio::orderBy('nombre')->get();

    return response()->json([
        'success' => true,
        'data' => $centrosAcopio,
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:60',
        'direccion' => 'required|string|max:120',
        'capacidad' => 'nullable|numeric|min:0.01',
        'tipo' => 'required|in:Central Operativa,Centro de acopio,Punto de depósito,Vertedero',
        'ubicacionX' => 'required|numeric|between:-99.9999,99.9999|decimal:0,4',
        'ubicacionY' => 'required|numeric|between:-99.9999,99.9999|decimal:0,4',
    ]);

    if ($validated['tipo'] === 'Central Operativa') {
        $validated['capacidad'] = null;
    } elseif (!isset($validated['capacidad']) || $validated['capacidad'] <= 0) {
        return response()->json(['success' => false, 'mensaje' => 'La capacidad debe ser mayor a 0'], 422);
    }

    $centroAcopio = CentroAcopio::create($validated);

    return response()->json([
        'success' => true,
        'mensaje' => 'Centro de acopio creado correctamente',
        'data' => $centroAcopio,
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
{
    $centroAcopio = CentroAcopio::find($id);

    if (!$centroAcopio) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Centro de acopio no encontrado',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $centroAcopio,
    ]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $centroAcopio = CentroAcopio::find($id);

    if (!$centroAcopio) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Centro de acopio no encontrado',
        ], 404);
    }

    $validated = $request->validate([
        'nombre' => 'sometimes|required|string|max:60',
        'direccion' => 'sometimes|required|string|max:120',
        'capacidad' => 'nullable|numeric|min:0.01',
        'tipo' => 'sometimes|required|in:Central Operativa,Centro de acopio,Punto de depósito,Vertedero',
        'ubicacionX' => 'sometimes|required|numeric|between:-99.9999,99.9999|decimal:0,4',
        'ubicacionY' => 'sometimes|required|numeric|between:-99.9999,99.9999|decimal:0,4',
    ]);

    $tipoFinal = $validated['tipo'] ?? $centroAcopio->tipo;
    $capacidadFinal = array_key_exists('capacidad', $validated)
        ? $validated['capacidad'] : $centroAcopio->capacidad;
    if ($tipoFinal === 'Central Operativa') {
        if (array_key_exists('capacidad', $validated) && $validated['capacidad'] !== null) {
            return response()->json(['success' => false, 'mensaje' => 'Una Central Operativa no admite capacidad. Para asignarle capacidad, cambie el tipo de centro.'], 422);
        }
        $validated['capacidad'] = null;
    } elseif ($capacidadFinal === null || $capacidadFinal <= 0) {
        return response()->json(['success' => false, 'mensaje' => 'La capacidad debe ser mayor a 0'], 422);
    } else {
        $validated['capacidad'] = $capacidadFinal;
    }

    $centroAcopio->update($validated);

    return response()->json([
        'success' => true,
        'mensaje' => 'Centro de acopio actualizado correctamente',
        'data' => $centroAcopio,
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $centroAcopio = CentroAcopio::find($id);
            if (!$centroAcopio) {
                return response()->json(['success' => false, 'mensaje' => 'Centro de acopio no encontrado'], 404);
            }
            $centroAcopio->delete();
            return response()->json(['success' => true, 'mensaje' => 'Centro de acopio eliminado correctamente']);
        } catch (QueryException $exception) {
            $codigo = (int) ($exception->errorInfo[1] ?? 0);
            $detalle = strtolower($exception->getMessage());
            if ($codigo === 1451 || ($codigo === 19 && str_contains($detalle, 'foreign key constraint failed'))) {
                $motivo = 'tiene registros relacionados que deben conservarse';
                foreach ([
                    'fk_usuario_centro' => 'tiene usuarios asociados',
                    'fk_maquinaria_centro' => 'tiene maquinaria asociada',
                    'fk_recoleccion_centro' => 'figura como destino de recolecciones',
                    'fk_traslada_origen' => 'figura como origen de traslados',
                    'fk_traslada_destino' => 'figura como destino de traslados',
                ] as $clave => $texto) {
                    if (str_contains($detalle, $clave)) { $motivo = $texto; break; }
                }
                return response()->json(['success' => false, 'mensaje' => 'No se puede eliminar el centro porque ' . $motivo . '.'], 409);
            }
            report($exception);
        } catch (Throwable $exception) {
            report($exception);
        }
        return response()->json(['success' => false, 'mensaje' => 'Ocurrió un error inesperado al eliminar el centro. Intente nuevamente; si persiste, contacte al administrador.'], 500);
    }
}
