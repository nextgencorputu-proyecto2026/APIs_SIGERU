<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CentroAcopio;

class CentroAcopioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $centrosAcopio = CentroAcopio::where('tipo', 'Centro de acopio')->get();

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
        'nombre' => 'required|string|max:100',
        'direccion' => 'required|string|max:200',
        'capacidad' => 'required|numeric|min:0',
        'tipo' => 'required|in:Centro de acopio',
        'ubicacionX' => 'required|numeric',
        'ubicacionY' => 'required|numeric',
    ]);

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
    $centroAcopio = CentroAcopio::where('tipo', 'Centro de acopio')->find($id);

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
    $centroAcopio = CentroAcopio::where('tipo', 'Centro de acopio')->find($id);

    if (!$centroAcopio) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Centro de acopio no encontrado',
        ], 404);
    }

    $validated = $request->validate([
        'nombre' => 'sometimes|required|string|max:100',
        'direccion' => 'sometimes|required|string|max:200',
        'capacidad' => 'sometimes|required|numeric|min:0',
        'ubicacionX' => 'sometimes|required|numeric',
        'ubicacionY' => 'sometimes|required|numeric',
    ]);

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
    $centroAcopio = CentroAcopio::where('tipo', 'Centro de acopio')->find($id);

    if (!$centroAcopio) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Centro de acopio no encontrado',
        ], 404);
    }

    $centroAcopio->delete();

    return response()->json([
        'success' => true,
        'mensaje' => 'Centro de acopio eliminado correctamente',
    ]);
}
}
