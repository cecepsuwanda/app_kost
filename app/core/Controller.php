<?php

namespace App\Core;

class Controller
{
    protected $db;
    protected $config;
    protected $session;
    protected $request;
    protected $app;

    public function __construct(?Application $app = null)
    {
        // If Application instance is provided, use dependency injection
        if ($app !== null) {
            $this->app = $app;
            $this->db = $app->getDatabase();
            $this->config = $app->getConfig();
            $this->session = $app->getSession();
            $this->request = $app->getRequest();
        } else {
            // Fallback to singleton pattern for backward compatibility
            $this->db = Database::getInstance();
            $this->config = Config::getInstance();
            $this->session = Session::getInstance();
            $this->request = Request::getInstance();
        }
    }

    protected function loadView($view, $data = [])
    {
        // Make core instances available to views
        $data['config'] = $this->config;
        $data['session'] = $this->session;
        $data['request'] = $this->request;
        
        extract($data);
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View $view not found");
        }
    }

    protected function loadModel($model)
    {
        // Try namespaced model first
        $namespacedModel = "App\\Models\\$model";
        if (class_exists($namespacedModel)) {
            // Use dependency injection if Application is available
            return $this->app ? 
                new $namespacedModel($this->db, $this->app) : 
                new $namespacedModel();
        }
        
        // Fallback to non-namespaced (backward compatibility)
        $modelFile = APP_PATH . '/models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            // Use dependency injection for fallback models too
            return $this->app ? 
                new $model($this->db, $this->app) : 
                new $model();
        } else {
            throw new \RuntimeException("Model $model not found");
        }
    }

    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Legacy methods (for backward compatibility)
    protected function post($key, $default = null)
    {
        return \App\Core\Request::post($key, $default);
    }

    protected function get($key, $default = null)
    {
        return \App\Core\Request::get($key, $default);
    }
}