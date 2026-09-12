<?php

use App\Models\Credenciales;

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración predeterminada de autenticación
    |--------------------------------------------------------------------------
    |
    | Aquí indicamos cuál será el guard que Laravel utilizará por defecto.
    | En nuestro caso usamos "api", que trabajará con JWT.
    |
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
    | Un guard define cómo Laravel identifica al usuario.
    |
    | "web" utiliza sesiones.
    | "api" utiliza JWT.
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
    | Providers de usuarios
    |--------------------------------------------------------------------------
    |
    | El provider indica qué modelo debe utilizar Laravel para buscar
    | al usuario que intenta autenticarse.
    |
    | En SIGERU las credenciales se encuentran en la tabla "credenciales",
    | representada por el modelo Credenciales.
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
    |
    | Esta configuración pertenece al sistema de recuperación de contraseña
    | de Laravel. Actualmente SIGERU no utiliza esta funcionalidad,
    | pero dejamos configurado el provider correcto.
    |
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
    |
    | Define durante cuánto tiempo Laravel considera válida una confirmación
    | de contraseña antes de solicitarla nuevamente.
    |
    */

    'password_timeout' => env(
        'AUTH_PASSWORD_TIMEOUT',
        10800
    ),

];