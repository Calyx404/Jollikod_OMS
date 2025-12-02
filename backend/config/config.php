<?php

return [
    'app_name' => 'Jollikod OMS',
    'base_url' => 'http://jollikod.order',

    'db' => [
        'host' => '127.0.0.1',
        'database' => 'jollikod_oms',
        'user' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],

    'session' => [
        'name' => 'jollikod_session',
        'lifetime' => 86400,
        'secure' => false,
        'httponly' => true,
        'path' => '/'
    ]
];
