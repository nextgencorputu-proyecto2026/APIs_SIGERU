<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Credenciales;

class UsuarioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listar usuarios
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $nombre = $request->nombre;

        $usuarios = Usuario::leftJoin(
            'credenciales',
            'usuario.idUsu',
            '=',
            'credenciales.idUsu'
        )
            ->when($nombre, function ($query) use ($nombre) {

                $query->where(function ($q) use ($nombre) {

                    $q->where('usuario.nombre1', 'LIKE', '%' . $nombre . '%')
                        ->orWhere('usuario.nombre2', 'LIKE', '%' . $nombre . '%')
                        ->orWhere('usuario.apellido1', 'LIKE', '%' . $nombre . '%')
                        ->orWhere('usuario.apellido2', 'LIKE', '%' . $nombre . '%');

                });

            })
            ->select(
                'usuario.idUsu',
                'usuario.nombre1',
                'usuario.apellido1',
                'usuario.tipo',
                'usuario.idCentro',
                'credenciales.mail'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $usuarios,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Registrar usuario
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ci' => 'required|digits:8|unique:usuario,ci',
            'nombre1' => 'required|string|max:30',
            'nombre2' => 'nullable|string|max:30',
            'apellido1' => 'required|string|max:30',
            'apellido2' => 'nullable|string|max:30',
            'fec_nac' => 'required|date',
            'tipo' => 'required|in:Administrador,Operario,Chofer',
            'idCentro' => 'required|integer|exists:centro,idCentro',
            'email' => 'required|email|unique:credenciales,mail',
            'password' => ['required', 'string', 'min:6', 'regex:/[A-Z]/'],
        ]);

        $usuario = Usuario::create([
            'ci' => $validated['ci'],
            'nombre1' => $validated['nombre1'],
            'nombre2' => $validated['nombre2'] ?? null,
            'apellido1' => $validated['apellido1'],
            'apellido2' => $validated['apellido2'] ?? null,
            'fec_nac' => $validated['fec_nac'],
            'tipo' => $validated['tipo'],
            'idCentro' => $validated['idCentro'],
        ]);

        Credenciales::create([
            'idUsu' => $usuario->idUsu,
            'mail' => $validated['email'],
            'contrasena' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Usuario registrado correctamente',
            'data' => $usuario,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Mostrar usuario
    |--------------------------------------------------------------------------
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


    /*
    |--------------------------------------------------------------------------
    | Actualizar usuario
    |--------------------------------------------------------------------------
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


    /*
    |--------------------------------------------------------------------------
    | Eliminar usuario
    |--------------------------------------------------------------------------
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
}
