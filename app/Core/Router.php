<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($path, PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        $callback = $this->routes[$method][$path] ?? false;

        if (!$callback) {
            http_response_code(404);
            echo "Not Found";
            return;
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $method = $callback[1];
            return $controller->$method();
        }

        call_user_func($callback);
    }
}
