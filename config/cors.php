<?php


return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // allow exact origins – no wildcard when you use cookies
    'allowed_origins' => [
        'https://menulink.xyz',
        'https://www.menulink.xyz',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // allow cookies / Authorization header
    'supports_credentials' => true,
];
