<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (
            !$token = auth('api')->attempt([
                'mail' => $credentials['email'],
                'password' => $credentials['password'],
            ])
        ) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        return $this->tokenResponse($token);
    }


    public function me(): JsonResponse
    {
        $credenciales = auth('api')->user();

        $usuario = $credenciales->usuario;

        return response()->json([
            'data' => [
                'idUsu' => $credenciales->idUsu,
                'mail' => $credenciales->mail,
                'nombre' => $usuario?->nombre1,
                'apellido' => $usuario?->apellido1,
                'tipo' => $usuario?->tipo,
            ],
        ]);
    }


    public function refresh(): JsonResponse
    {
        return $this->tokenResponse(
            auth('api')->refresh()
        );
    }

    private function tokenResponse(string $token): JsonResponse
    {
        return response()->json([
            'message' => 'Authenticated',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
        ]);
    }
}