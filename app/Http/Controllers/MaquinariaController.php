<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Maquinaria;

class MaquinariaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $maquinarias = Maquinaria::all();

        return response()->json([
            'success' => true,
            'data' => $maquinarias,

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:Aplanadora,Bobcat,Retroexcavadora',
            'estado' => 'required|in:Inhabilitado,Disponible',
            'idCentro' => 'required|integer|exists:centro,idCentro',
        ]);

        $maquinaria = Maquinaria::create($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Maquinaria creada correctamente',
            'data' => $maquinaria,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $maquinaria = Maquinaria::find($id);

        if (!$maquinaria) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Maquinaria no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $maquinaria,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $maquinaria = Maquinaria::find($id);

        if (!$maquinaria) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Maquinaria no encontrada',
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'tipo' => 'sometimes|required|in:Aplanadora,Bobcat,Retroexcavadora',
            'estado' => 'sometimes|required|in:Inhabilitado,Disponible',
            'idCentro' => 'sometimes|required|integer|exists:centro,idCentro',
        ]);

        $maquinaria->update($validated);

        return response()->json([
            'success' => true,
            'mensaje' => 'Maquinaria actualizada correctamente',
            'data' => $maquinaria,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $maquinaria = Maquinaria::find($id);

        if (!$maquinaria) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Maquinaria no encontrada',
            ], 404);
        }

        $maquinaria->delete();

        return response()->json([
            'success' => true,
            'mensaje' => 'Maquinaria eliminada correctamente',
        ]);
    }
}
