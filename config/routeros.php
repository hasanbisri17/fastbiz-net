<?php

return [
    'default' => [
        'host' => env('ROUTEROS_HOST', '192.168.1.1'),
        'user' => env('ROUTEROS_USER', 'admin'),
        'password' => env('ROUTEROS_PASSWORD', ''),
        'port' => env('ROUTEROS_PORT', 8728),
    ],
];
