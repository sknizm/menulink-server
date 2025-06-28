<?php
// config/cors.php
return [
    'paths'                 => ['*'],   // apply to every URI
    'allowed_methods'       => ['*'],   // every verb
    'allowed_origins'       => ['*'],   // every Origin *
    'allowed_origins_patterns' => [],   // leave empty because we used '*'
    'allowed_headers'       => ['*'],   // every header
    'exposed_headers'       => [],
    'max_age'               => 0,
    'supports_credentials'  => false,   // MUST be false if allowed_origins = ['*']
];
