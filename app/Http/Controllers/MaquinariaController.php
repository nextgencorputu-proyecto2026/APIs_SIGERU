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
            'data' => $maquinarias,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $maquinaria = Maquinaria::create($request->all());

        return response()->json([
            'data' => $maquinaria,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $maquinaria = Maquinaria::find($id);

        return response()->json([
            'data' => $maquinaria,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $maquinaria = Maquinaria::find($id);
        $maquinaria->update($request->all());

        return response()->json([
            'data' => $maquinaria,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $maquinaria = Maquinaria::find($id);
        $maquinaria->delete();

        return response()->json([
            'mensaje' => 'Maquinaria eliminada',
        ]);
    }
}
