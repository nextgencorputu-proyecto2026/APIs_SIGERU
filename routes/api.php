<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\ContenedorController;
use App\Http\Controllers\CentroAcopioController;
use App\Http\Controllers\MaquinariaController;

Route::get('health', fn() => response()->json([
    'success' => true,
    'message' => 'API running',
]));

Route::get('usuarios', [UsuarioController::class, 'index']);
Route::post('usuarios', [UsuarioController::class, 'store']);
Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']);

Route::post('login', [UsuarioController::class, 'login']);

Route::get('camiones', [CamionController::class, 'index']);
Route::post('camiones', [CamionController::class, 'store']);
Route::get('camiones/{id}', [CamionController::class, 'show']);
Route::put('camiones/{id}', [CamionController::class, 'update']);
Route::delete('camiones/{id}', [CamionController::class, 'destroy']);

Route::get('contenedores', [ContenedorController::class, 'index']);
Route::post('contenedores', [ContenedorController::class, 'store']);
Route::get('contenedores/{id}', [ContenedorController::class, 'show']);
Route::put('contenedores/{id}', [ContenedorController::class, 'update']);
Route::delete('contenedores/{id}', [ContenedorController::class, 'destroy']);

Route::get('centros-acopio', [CentroAcopioController::class, 'index']);
Route::post('centros-acopio', [CentroAcopioController::class, 'store']);
Route::get('centros-acopio/{id}', [CentroAcopioController::class, 'show']);
Route::put('centros-acopio/{id}', [CentroAcopioController::class, 'update']);
Route::delete('centros-acopio/{id}', [CentroAcopioController::class, 'destroy']);

Route::get('maquinarias', [MaquinariaController::class, 'index']);
Route::post('maquinarias', [MaquinariaController::class, 'store']);
Route::get('maquinarias/{id}', [MaquinariaController::class, 'show']);
Route::put('maquinarias/{id}', [MaquinariaController::class, 'update']);
Route::delete('maquinarias/{id}', [MaquinariaController::class, 'destroy']);

