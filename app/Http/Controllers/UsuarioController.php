<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Credenciales;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = Usuario::all();

        return response()->json([
            'data' => $usuarios,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $usuario = Usuario::create($request->all());

        return response()->json([
            'data' => $usuario,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $usuario = Usuario::find($id);

        return response()->json([
            'data' => $usuario,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::find($id);
        $usuario->update($request->all());

        return response()->json([
            'data' => $usuario,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $usuario = Usuario::find($id);
        $usuario->delete();

        return response()->json([
            'mensaje' => 'Usuario eliminado',
        ]);
    }

    /**
     * Login: valida email y contrasena contra la tabla credenciales.
     */
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        $credencial = Credenciales::where('mail', $email)
            ->where('contrasena', $password)
            ->first();

        if ($credencial) {

            $usuario = Usuario::find($credencial->idUsu);

            return response()->json([
                'success' => true,
                'mensaje' => 'Login correcto',
                'data' => $usuario,
            ]);

        } else {

            return response()->json([
                'success' => false,
                'mensaje' => 'Usuario o contraseña incorrectos',
            ]);

        }


    }
}