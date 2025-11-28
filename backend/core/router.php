<?php
namespace Core;

/**
 * core/Router.php
 *
 * Purpose:
 *  - Register and dispatch routes.
 *
 * Flow:
 *  - Routes are stored in a static array keyed by method and path.
 *  - Handler format: either a Closure/function or an array: [ [MiddlewareClass,...], ControllerClass, 'method' ]
 *  - On dispatch: if middleware list provided, run each middleware::handle($request)
 *  - Then instantiate controller and call method with $request parameter.
 */

class Router {
    private static $routes = [];

    public function get($path, $handler) {
        self::$routes['GET'][$path] = $handler;
    }

    public function post($path, $handler) {
        self::$routes['POST'][$path] = $handler;
    }

    public function dispatch(Request $request) {
        $method = $request->method;
        $path = $request->uri;

        $handler = self::$routes[$method][$path] ?? null;
        if (!$handler) {
            Response::json(['error' => 'Not Found'], 404);
        }

        if (is_array($handler)) {
            [$middlewareList, $controllerClass, $methodName] = $handler;

            if (is_array($middlewareList)) {
                foreach ($middlewareList as $mw) {
                    $mw::handle($request);
                }
            }

            $controller = new $controllerClass();
            return $controller->$methodName($request);
        }

        if (is_callable($handler)) {
            return $handler($request);
        }

        Response::json(['error' => 'Invalid route handler'], 500);
    }
}
