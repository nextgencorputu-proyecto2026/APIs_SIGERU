<?php

use App\Http\Controllers\CamionController;
use App\Http\Controllers\CentroAcopioController;
use App\Http\Controllers\MaquinariaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API de Gestión - SiGeRU
|--------------------------------------------------------------------------
*/

// Ruta pública para comprobar el funcionamiento.
Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API de Gestión funcionando',
    ]);
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas con JWT
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->group(function () {

    // CAMIONES - Solo Administrador
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

    // CENTROS DE ACOPIO - Solo Administrador
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

    // MAQUINARIAS - Administrador y Operario
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
