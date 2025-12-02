<?php

// Autoloading
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../core/',
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/services/',
        __DIR__ . '/../app/utils/',
        __DIR__ . '/../app/middlewares/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . ".php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load Config + App
$config = require __DIR__ . '/../config/config.php';
$app = new App($config);

// Load Routes
require __DIR__ . '/../app/routes/api.php';

// Run App
$app->run();
