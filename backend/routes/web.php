<?php
/**
 * Main route definitions.
 * Maps URL paths to controller actions.
 */

// require_once __DIR__ . '/../controllers/HomeController.php';
// require_once __DIR__ . '/../controllers/AuthController.php';

// $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// // Simple routing table
// $routes = [
//     '/' => ['HomeController', 'index'],
//     '/login' => ['AuthController', 'showLogin'],
//     '/login/submit' => ['AuthController', 'login'],
// ];

// if (array_key_exists($uri, $routes)) {
//     [$controllerName, $method] = $routes[$uri];
//     $controller = new $controllerName();

//     echo $controller->$method();
// } else {
//     http_response_code(404);
//     echo "404 - Not Found";
// }
