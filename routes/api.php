<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\CentroAcopioController;
use App\Http\Controllers\MaquinariaController;
use App\Http\Controllers\RutaController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
|
|
|
*/


Route::get('health', fn() => response()->json([
    'success' => true,
    'message' => 'API running',
]));

// Autenticación
Route::prefix('auth')->group(function () {

    Route::post('login', [AuthController::class, 'login']);

    Route::post('refresh', [AuthController::class, 'refresh']);

});


/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
|
| 
|
*/

Route::middleware('auth:api')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Usuario autenticado
    |--------------------------------------------------------------------------
    */

    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('rutas', [RutaController::class, 'index'])
        ->middleware('rol:Administrador,Operario');


    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */

    Route::get('usuarios', [UsuarioController::class, 'index'])
        ->middleware('rol:Administrador');

    Route::post('usuarios', [UsuarioController::class, 'store'])
        ->middleware('rol:Administrador');

    Route::get('usuarios/{id}', [UsuarioController::class, 'show'])
        ->middleware('rol:Administrador');

    Route::put('usuarios/{id}', [UsuarioController::class, 'update'])
        ->middleware('rol:Administrador');

    Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy'])
        ->middleware('rol:Administrador');


    /*
    |--------------------------------------------------------------------------
    | Camiones
    |--------------------------------------------------------------------------
    */

    Route::get('camiones', [CamionController::class, 'index'])
        ->middleware('rol:Administrador');

    Route::post('camiones', [CamionController::class, 'store'])
        ->middleware('rol:Administrador');

    Route::get('camiones/{id}', [CamionController::class, 'show'])
        ->middleware('rol:Administrador');

    Route::put('camiones/{id}', [CamionController::class, 'update'])
        ->middleware('rol:Administrador');

    Route::delete('camiones/{id}', [CamionController::class, 'destroy'])
        ->middleware('rol:Administrador');


    /*
    |--------------------------------------------------------------------------
    | Contenedores
    |--------------------------------------------------------------------------
    */

    Route::get('contenedores', [ContenedorController::class, 'index'])
        ->middleware('rol:Administrador,Operario');

    Route::post('contenedores', [ContenedorController::class, 'store'])
        ->middleware('rol:Administrador,Operario');

    Route::get('contenedores/{id}', [ContenedorController::class, 'show'])
        ->middleware('rol:Administrador,Operario');

    Route::put('contenedores/{id}', [ContenedorController::class, 'update'])
        ->middleware('rol:Administrador,Operario');

    Route::delete('contenedores/{id}', [ContenedorController::class, 'destroy'])
        ->middleware('rol:Administrador,Operario');


    /*
    |--------------------------------------------------------------------------
    | Centros de acopio
    |--------------------------------------------------------------------------
    */

    Route::get('centros-acopio', [CentroAcopioController::class, 'index'])
        ->middleware('rol:Administrador');

    Route::post('centros-acopio', [CentroAcopioController::class, 'store'])
        ->middleware('rol:Administrador');

    Route::get('centros-acopio/{id}', [CentroAcopioController::class, 'show'])
        ->middleware('rol:Administrador');

    Route::put('centros-acopio/{id}', [CentroAcopioController::class, 'update'])
        ->middleware('rol:Administrador');

    Route::delete('centros-acopio/{id}', [CentroAcopioController::class, 'destroy'])
        ->middleware('rol:Administrador');


    /*
    |--------------------------------------------------------------------------
    | Maquinarias
    |--------------------------------------------------------------------------
    */

    Route::get('maquinarias', [MaquinariaController::class, 'index'])
        ->middleware('rol:Administrador,Operario');

    Route::post('maquinarias', [MaquinariaController::class, 'store'])
        ->middleware('rol:Administrador,Operario');

    Route::get('maquinarias/{id}', [MaquinariaController::class, 'show'])
        ->middleware('rol:Administrador,Operario');

    Route::put('maquinarias/{id}', [MaquinariaController::class, 'update'])
        ->middleware('rol:Administrador,Operario');

    Route::delete('maquinarias/{id}', [MaquinariaController::class, 'destroy'])
        ->middleware('rol:Administrador,Operario');

});
