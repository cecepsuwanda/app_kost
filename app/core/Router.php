<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    private array $globalMiddleware = [];
    private ?Application $app = null;

    public function add(string $route, string $controller, array $middleware = []): void
    {
        $this->routes[$route] = [
            'controller' => $controller,
            'middleware' => $middleware
        ];
    }

    public function addMiddleware(string $name, callable $middleware): void
    {
        $this->middleware[$name] = $middleware;
    }

    public function addGlobalMiddleware(callable $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }

    public function setApplication(Application $app): void
    {
        $this->app = $app;
    }

    public function run(): void
    {
        $uri = $this->getCurrentUri();
        
        // Find matching route
        $route = $this->findRoute($uri);

        if ($route) {            
            $this->executeRoute($route);
        } else {
            // Default to home if route not found
            $this->executeRoute([
                'controller' => 'Home@index',
                'middleware' => []
            ]);
        }
    }

    private function getCurrentUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove query string if exists
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Remove base path from URI (app_kost)
        $basePath = '/app_kost';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Ensure URI starts with /
        if (empty($uri)) {
            $uri = '/';
        }
        
        return $uri;
    }

    private function findRoute(string $uri): ?array
    {   
               
        // Check for exact match
        if (array_key_exists($uri, $this->routes)) {
            return $this->routes[$uri];
        }

        // Future: Add support for parameterized routes
        // Example: /admin/user/{id}
        
        return null;
    }

    private function executeRoute(array $route): void
    {
        try {
            // Load helpers for current route (optional optimization)
            $helperManager = HelperManager::getInstance();
            $currentRoute = $this->getCurrentUri();
            $helperManager->loadHelpersForRoute($currentRoute);

            // Execute global middleware first
            foreach ($this->globalMiddleware as $middleware) {
                $result = $middleware();
                if ($result === false) {
                    return; // Middleware stopped execution
                }
            }

            // Execute route-specific middleware
            foreach ($route['middleware'] as $middlewareName) {
                if (isset($this->middleware[$middlewareName])) {
                    $result = $this->middleware[$middlewareName]();
                    if ($result === false) {
                        return; // Middleware stopped execution
                    }
                }
            }

            // Execute controller
            $this->callController($route['controller']);
            
        } catch (\Exception $e) {
            // Re-throw exception to be handled by Application
            throw $e;
        }
    }

    private function callController(string $controllerAction): void
    {
        if (strpos($controllerAction, '@') === false) {
            throw new \InvalidArgumentException("Invalid controller action format: $controllerAction");
        }

        list($controller, $action) = explode('@', $controllerAction);
        
        // Try namespaced controller first
        $namespacedController = "App\\Controllers\\$controller";
        if (class_exists($namespacedController)) {
            // Use dependency injection if Application is available
            $controllerInstance = $this->app ? 
                new $namespacedController($this->app) : 
                new $namespacedController();
            
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                throw new \BadMethodCallException("Method $action not found in controller $controller");
            }
            return;
        }
        
        // Fallback to non-namespaced (backward compatibility)
        $controllerFile = APP_PATH . '/controllers/' . $controller . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            
            if (!class_exists($controller)) {
                throw new \RuntimeException("Controller class $controller not found in file");
            }
            
            // Use dependency injection if Application is available (fallback controllers)
            $controllerInstance = $this->app ? 
                new $controller($this->app) : 
                new $controller();
            
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            } else {
                throw new \BadMethodCallException("Method $action not found in controller $controller");
            }
        } else {
            throw new \RuntimeException("Controller file not found: $controllerFile");
        }
    }

    // Getter methods
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getGlobalMiddleware(): array
    {
        return $this->globalMiddleware;
    }

    // Utility methods
    public function hasRoute(string $route): bool
    {
        return array_key_exists($route, $this->routes);
    }

    public function hasMiddleware(string $name): bool
    {
        return array_key_exists($name, $this->middleware);
    }
}