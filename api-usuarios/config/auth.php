<?php

use App\Models\Credenciales;

return [

    /*
    |--------------------------------------------------------------------------
    | Autenticación predeterminada
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards de autenticación
    |--------------------------------------------------------------------------
    |
    | api: autenticación mediante tokens JWT.
    | web: autenticación mediante sesiones.
    |
    */

    'guards' => [

        'web' => [
            'driver' => 'session',
            'provider' => 'credenciales',
        ],

        'api' => [
            'driver' => 'jwt',
            'provider' => 'credenciales',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de usuarios
    |--------------------------------------------------------------------------
    |
    | Utilizamos el modelo Credenciales de SiGeRU.
    |
    */

    'providers' => [

        'credenciales' => [
            'driver' => 'eloquent',
            'model' => Credenciales::class,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Recuperación de contraseñas
    |--------------------------------------------------------------------------
    */

    'passwords' => [

        'users' => [
            'provider' => 'credenciales',
            'table' => env(
                'AUTH_PASSWORD_RESET_TOKEN_TABLE',
                'password_reset_tokens'
            ),
            'expire' => 60,
            'throttle' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de confirmación de contraseña
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env(
        'AUTH_PASSWORD_TIMEOUT',
        10800
    ),

];
