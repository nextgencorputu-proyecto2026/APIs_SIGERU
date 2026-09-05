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
            'data' => $contenedores,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $contenedor = Contenedor::create([
            'UbicacionX' => $request->ubicacionX,
            'UbicacionY' => $request->ubicacionY,
            'Estado' => $request->estado,
            'nivelLlenado' => $request->nivelLlenado,
            'tipoResiduo' => $request->tipoResiduo,
            'idRuta' => $request->idRuta,
        ]);

        return response()->json([
            'data' => $contenedor,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contenedor = Contenedor::find($id);

        return response()->json([
            'data' => $contenedor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $contenedor = Contenedor::find($id);
        $contenedor->update($request->all());

        return response()->json([
            'data' => $contenedor,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $contenedor = Contenedor::find($id);
        $contenedor->delete();

        return response()->json([
            'mensaje' => 'Contenedor eliminado',
        ]);
    }
}
