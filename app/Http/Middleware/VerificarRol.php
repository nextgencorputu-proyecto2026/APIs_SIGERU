<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No estás autenticado',
            ], 401);
        }

        if (!in_array($usuario['tipo'], $roles)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No tienes permisos para acceder a este recurso',
            ], 403);
        }

        return $next($request);
    }
}