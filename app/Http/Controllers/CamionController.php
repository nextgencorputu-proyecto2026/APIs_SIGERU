<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Camion;
use App\Models\Vehiculo;

class CamionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $camiones = Camion::all();

        return response()->json([
            'data' => $camiones,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $vehiculo = Vehiculo::create([
            'matricula' => $request->matricula,
            'marca' => $request->marca,
            'estado' => $request->estado,
        ]);

        $camion = Camion::create([
            'idVehiculo' => $vehiculo->idVehiculo,
            'tipo' => $request->tipo,
            'capacidad' => $request->capacidad,
            'idCentroAcopio' => $request->idCentroAcopio,
            'idVertedero' => $request->idVertedero,
        ]);

        return response()->json([
            'data' => $camion,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $camion = Camion::find($id);

        return response()->json([
            'data' => $camion,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $camion = Camion::find($id);
        $camion->update($request->all());

        return response()->json([
            'data' => $camion,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $camion = Camion::find($id);
        $camion->delete();

        return response()->json([
            'mensaje' => 'Camion eliminado',
        ]);
    }
}
