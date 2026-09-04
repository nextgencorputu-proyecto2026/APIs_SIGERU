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
        $centrosAcopio = CentroAcopio::all();

        return response()->json([
            'data' => $centrosAcopio,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $centroAcopio = CentroAcopio::create($request->all());

        return response()->json([
            'data' => $centroAcopio,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $centroAcopio = CentroAcopio::find($id);

        return response()->json([
            'data' => $centroAcopio,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $centroAcopio = CentroAcopio::find($id);
        $centroAcopio->update($request->all());

        return response()->json([
            'data' => $centroAcopio,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $centroAcopio = CentroAcopio::find($id);
        $centroAcopio->delete();

        return response()->json([
            'mensaje' => 'Centro de acopio eliminado',
        ]);
    }
}
