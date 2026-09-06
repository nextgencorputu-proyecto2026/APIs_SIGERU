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
        'success' => true,
        'data' => $usuarios,
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'ci' => 'required|string|max:20|unique:usuario,ci',
        'nombre1' => 'required|string|max:50',
        'nombre2' => 'nullable|string|max:50',
        'apellido1' => 'required|string|max:50',
        'apellido2' => 'nullable|string|max:50',
        'fec_nac' => 'required|date',
        'tipo' => 'required|in:Administrador,Operario,Chofer',
        'idCentro' => 'required|integer|exists:centro,idCentro',
    ]);

    $usuario = Usuario::create($validated);

    return response()->json([
        'success' => true,
        'mensaje' => 'Usuario creado correctamente',
        'data' => $usuario,
    ], 201);
}

    /**
     * Display the specified resource.
     */
   public function show(string $id)
{
    $usuario = Usuario::find($id);

    if (!$usuario) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Usuario no encontrado',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $usuario,
    ]);
}
    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    $usuario = Usuario::find($id);

    if (!$usuario) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Usuario no encontrado',
        ], 404);
    }

    $validated = $request->validate([
        'ci' => 'sometimes|required|string|max:20|unique:usuario,ci,' . $id . ',idUsu',
        'nombre1' => 'sometimes|required|string|max:50',
        'nombre2' => 'nullable|string|max:50',
        'apellido1' => 'sometimes|required|string|max:50',
        'apellido2' => 'nullable|string|max:50',
        'fec_nac' => 'sometimes|required|date',
        'tipo' => 'sometimes|required|in:Administrador,Operario,Chofer',
        'idCentro' => 'sometimes|required|integer|exists:centro,idCentro',
    ]);

    $usuario->update($validated);

    return response()->json([
        'success' => true,
        'mensaje' => 'Usuario actualizado correctamente',
        'data' => $usuario,
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
{
    $usuario = Usuario::find($id);

    if (!$usuario) {
        return response()->json([
            'success' => false,
            'mensaje' => 'Usuario no encontrado',
        ], 404);
    }

    $usuario->delete();

    return response()->json([
        'success' => true,
        'mensaje' => 'Usuario eliminado correctamente',
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