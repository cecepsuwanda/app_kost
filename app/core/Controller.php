<?php

namespace App\Core;

class Controller
{
    protected $db;
    protected $config;
    protected $session;
    protected $request;
    

    public function __construct()
    {
        
            // Fallback to singleton pattern for backward compatibility
            $this->db = Database::getInstance();
            $this->config = Config::getInstance();
            $this->session = Session::getInstance();
            $this->request = Request::getInstance();
        
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
            return new $namespacedModel();
        }
        return null;
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

    public function getBaseUrl()
    {
        return $this->config->appConfig('url');
    }

    public function getAppName()
    {
        return $this->config->appConfig('name');
    }

    public function isLoggedIn()
    {
        return $this->session->sessionHas('user_id') && !empty($this->session->sessionGet('user_id'));
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header('Location: ' . $this->config->appConfig('url') . '/login');
            exit;
        }
    }

    public function getUser()
    {
        if ($this->isLoggedIn()) {
            return [
                'id' => $this->session->sessionGet('user_id'),
                'username' => $this->session->sessionGet('username'),
                'nama' => $this->session->sessionGet('nama'),
                'role' => $this->session->sessionGet('role')
            ];
        }
        return null;
    }
}