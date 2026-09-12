<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Usuario;

class VerificarRol
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $credencial = auth('api')->user();

        if (!$credencial) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No estás autenticado',
            ], 401);
        }

        $usuario = Usuario::find($credencial->idUsu);

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Usuario no encontrado',
            ], 404);
        }

        if (!in_array($usuario->tipo, $roles)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No tienes permisos para acceder a este recurso',
            ], 403);
        }

        return $next($request);
    }
}