<?php

use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\IncidenciaController;
use App\Http\Controllers\RutaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Recoleccion running',
    ]);
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas con JWT
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Rutas de recolección
    |--------------------------------------------------------------------------
    */

    Route::get('rutas', [RutaController::class, 'index'])
        ->middleware('rol:Administrador,Operario');

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
    | Incidencias
    |--------------------------------------------------------------------------
    */

    Route::apiResource('incidencias', IncidenciaController::class)
        ->parameters(['incidencias' => 'incidencia'])
        ->only(['index', 'show', 'store'])
        ->middleware('rol:Administrador,Operario,Chofer');

    Route::apiResource('incidencias', IncidenciaController::class)
        ->parameters(['incidencias' => 'incidencia'])
        ->only(['update', 'destroy'])
        ->middleware('rol:Administrador');

});
