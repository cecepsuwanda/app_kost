<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Core\Session;

class Auth extends Controller
{
    protected $config;
    protected $session;

    public function __construct($app = null)
    {
        parent::__construct($app);
        // Note: config and session are already set by parent constructor
        // This ensures compatibility with both DI and singleton patterns
    }
    
    public function login()
    {
        // If already logged in, redirect to admin
        if ($this->session->sessionHas('user_id')) {
            $this->redirect($this->config->appConfig('url').'/admin');
        }

        $error = '';

        if ($this->request->isPostRequest()) {
            $username = $this->request->postParam('username');
            $password = $this->request->postParam('password');

            if (empty($username) || empty($password)) {
                $error = 'Username dan password harus diisi';
            } else {
                $userModel = $this->loadModel('UserModel');
                $user = $userModel->authenticate($username, $password);

                if ($user) {
                    // Set session
                    $this->session->sessionSet('user_id', $user['id']);
                    $this->session->sessionSet('username', $user['username']);
                    $this->session->sessionSet('nama', $user['nama']);
                    $this->session->sessionSet('role', $user['role']);
                    $this->session->sessionSet('login_time', time());

                    // Update last login
                    $userModel->updateLastLogin($user['id']);

                    // Redirect to admin
                    $this->redirect($this->config->appConfig('url').'/admin');
                } else {
                    $error = 'Username atau password salah';
                }
            }
        }
        $baseUrl = $this->getBaseUrl();
        $appName = $this->getAppName();

        $data = [
            'title' => 'Login Admin - ' . $appName,
            'error' => $error,
            'baseUrl' => $baseUrl,
            'appName' => $appName
        ];

        $this->loadView('auth/login', $data);
    }

    public function logout()
    {
        // Destroy session
        $this->session->sessionDestroy();
        
        // Redirect to login
        $this->redirect($this->config->appConfig('url').'/login');
    }

    
}