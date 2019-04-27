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
    'allowedOrigins' => [ 'http://localhost', 'https://nexus.thepylonshow.com' ],
    'allowedOriginsPatterns' => [ '/localhost:\d/' ],
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['*'],
    'exposedHeaders' => [ 'Authorization' ],
    'maxAge' => 0,

];
