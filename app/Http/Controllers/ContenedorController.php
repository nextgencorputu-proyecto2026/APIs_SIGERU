<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contenedor;

class ContenedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $contenedores = Contenedor::all();

    return response()->json([
        'success' => true,
        'data' => $contenedores,
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'ubicacionX' => 'required|numeric',
        'ubicacionY' => 'required|numeric',
        'estado' => 'required|in:Inhabilitado,En mantenimiento,Disponible',
        'nivelLlenado' => 'required|numeric|min:0|max:100',
        'tipo' => 'required|in:Reciclables,Mixtos',
        'idRuta' => 'required|integer|exists:ruta,idRuta',
    ]);

    $contenedor = Contenedor::create($validated);

    return response()->json([
        'success' => true,
        'mensaje' => 'Contenedor creado correctamente',
        'data' => $contenedor,
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
{
    $contenedor = Contenedor::find($id);

    if (!$contenedor) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Contenedor no encontrado',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $contenedor,
    ]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $contenedor = Contenedor::find($id);

    if (!$contenedor) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Contenedor no encontrado',
        ], 404);
    }

    $validated = $request->validate([
        'ubicacionX' => 'sometimes|required|numeric',
        'ubicacionY' => 'sometimes|required|numeric',
        'estado' => 'sometimes|required|in:Inhabilitado,En mantenimiento,Disponible',
        'nivelLlenado' => 'sometimes|required|numeric|min:0|max:100',
        'tipo' => 'sometimes|required|in:Reciclables,Mixtos',
        'idRuta' => 'sometimes|required|integer|exists:ruta,idRuta',
    ]);

    $contenedor->update($validated);

    return response()->json([
        'success' => true,
        'mensaje' => 'Contenedor actualizado correctamente',
        'data' => $contenedor,
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
{
    $contenedor = Contenedor::find($id);

    if (!$contenedor) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Contenedor no encontrado',
        ], 404);
    }

    $contenedor->delete();

    return response()->json([
        'success' => true,
        'mensaje' => 'Contenedor eliminado correctamente',
    ]);
}
}
