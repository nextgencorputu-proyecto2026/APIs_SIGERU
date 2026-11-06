<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

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
        return response()->json([
            'data' => auth('api')->user(),
        ]);
    }

    public function refresh(): JsonResponse
    {
        try {
            $token = auth('api')->refresh();

            return $this->tokenResponse($token);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token inválido o expirado',
            ], 401);
        }
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
