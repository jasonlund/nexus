<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */
    'supportsCredentials' => false,
    'allowedOrigins' => ['https://nexus.thepylonshow.com', 'http://localhost:8080'],
    'allowedOriginsPatterns' => env('CORS_ALLOWED_ORIGINS_PATTERNS')
        ? explode(',', env('CORS_ALLOWED_ORIGINS_PATTERNS'))
        : [],
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => ['Authorization'],
    'maxAge' => 0,
];
