<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function add($route, $controller)
    {
        $this->routes[$route] = $controller;
    }

    public function run()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove query string if exists
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $segments = explode('/', trim(str_replace('\\', '/', ROOT_PATH), '/'));
        $lastSegment = end($segments);

        $uri = str_replace('/'.$lastSegment, '', $uri);
        
        // Check if route exists
        if (array_key_exists($uri, $this->routes)) {
            $controllerAction = $this->routes[$uri];
            $this->callController($controllerAction);
        } else {
            // Default to home if route not found
            $this->callController('Home@index');
        }
    }

    private function callController($controllerAction)
    {
        list($controller, $action) = explode('@', $controllerAction);
        
        // Try namespaced controller first
        $namespacedController = "App\\Controllers\\$controller";
        if (class_exists($namespacedController)) {
            $controllerInstance = new $namespacedController();
            
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                die("Method $action not found in controller $controller");
            }
            return;
        }
        
        // Fallback to non-namespaced (backward compatibility)
        $controllerFile = APP_PATH . '/controllers/' . $controller . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerInstance = new $controller();
            
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                die("Method $action not found in controller $controller");
            }
        } else {
            die("Controller $controller not found");
        }
    }
}