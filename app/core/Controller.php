<?php

namespace App\Core;

class Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function loadView($view, $data = [])
    {
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
            return new $namespacedModel();
        }
        
        // Fallback to non-namespaced (backward compatibility)
        $modelFile = APP_PATH . '/models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("Model $model not found");
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

    protected function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    protected function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}