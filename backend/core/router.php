<?php

class Router
{
    private array $routes = [];

    public function register(string $method, string $path, callable|array $handler)
    {
        $this->routes[strtoupper($method)][$path] = $handler;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = strtok($_SERVER['REQUEST_URI'], '?');

        if (isset($this->routes[$method][$uri])) {
            $handler = $this->routes[$method][$uri];

            if (is_array($handler)) {
                $controller = new $handler[0];
                $methodName = $handler[1];
                return $controller->$methodName(new Request(), new Response());
            }

            return call_user_func($handler, new Request(), new Response());
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}
