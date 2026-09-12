<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración CORS
    |--------------------------------------------------------------------------
    |
    | Permite que el frontend se comunique con la API.
    | El token JWT se enviará mediante el header Authorization.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];