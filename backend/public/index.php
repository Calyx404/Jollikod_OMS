<?php

spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use Core\Request;
use Core\Router;

$request = Request::capture();
$router = new Router();

foreach (glob(__DIR__ . '/../routes/*.php') as $routeFile) {
    require $routeFile;
}

$router->dispatch($request);
