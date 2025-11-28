<?php
/**
 * public/index.php
 *
 * Purpose:
 *  - Single entrypoint for all requests (API or static HTML pages).
 *  - Load bootstrap and then dispatch routing.
 *
 * Flow:
 *  - Setup autoloader
 *  - Load all route files from /routes
 *  - Call Router->dispatch(Request::capture())
 */

require_once __DIR__ . '/../bootstrap/app.php';

// Simple PSR-4-like autoloader (for this monolith)
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

// Load route definitions
foreach (glob(__DIR__ . '/../routes/*.php') as $routeFile) {
    require $routeFile;
}

// Dispatch request (router will call controllers)
$router->dispatch($request);
