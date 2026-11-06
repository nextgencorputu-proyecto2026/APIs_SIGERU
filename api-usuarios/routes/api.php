<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CuadrillaController;

/*
|--------------------------------------------------------------------------
| API DE USUARIOS - SIGERU
|--------------------------------------------------------------------------
*/

// Comprobar que la API funciona
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Usuarios funcionando correctamente',
        'service' => 'usuarios',
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTENTICACION JWT
|--------------------------------------------------------------------------
*/

// Iniciar sesion (ruta publica)
Route::post('/auth/login', [AuthController::class, 'login']);

// Renovar token JWT
Route::post('/auth/refresh', [AuthController::class, 'refresh'])
    ->middleware('auth:api');

// Obtener usuario autenticado
Route::get('/auth/me', [AuthController::class, 'me'])
    ->middleware('auth:api');

/*
|--------------------------------------------------------------------------
| CRUD DE USUARIOS Y CUADRILLAS
|--------------------------------------------------------------------------
| Requiere un token JWT valido y rol Administrador.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api', 'rol:Administrador'])->group(function () {

    /*
    |----------------------------------------------------------------------
    | USUARIOS
    |----------------------------------------------------------------------
    */

    // Listar usuarios y buscar por nombre
    Route::get('/usuarios', [UsuarioController::class, 'index']);

    // Registrar usuario
    Route::post('/usuarios', [UsuarioController::class, 'store']);

    // Consultar usuario por ID
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);

    // Actualizar usuario
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);
    Route::patch('/usuarios/{id}', [UsuarioController::class, 'update']);

    // Eliminar usuario
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']);

    /*
    |----------------------------------------------------------------------
    | CUADRILLAS
    |----------------------------------------------------------------------
    */

    // Listar cuadrillas
    Route::get('/cuadrillas', [CuadrillaController::class, 'index']);

    // Crear cuadrilla
    Route::post('/cuadrillas', [CuadrillaController::class, 'store']);

    // Consultar cuadrilla por ID
    Route::get('/cuadrillas/{cuadrilla}', [CuadrillaController::class, 'show']);

    // Actualizar cuadrilla
    Route::put('/cuadrillas/{cuadrilla}', [CuadrillaController::class, 'update']);

    // Eliminar cuadrilla
    Route::delete('/cuadrillas/{cuadrilla}', [CuadrillaController::class, 'destroy']);

});
