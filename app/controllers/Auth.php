<?php

namespace App\Controllers;

use App\Core\Controller;

class Auth extends Controller
{
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

        $data = [
            'title' => 'Login Admin - ' . $this->config->appConfig('name'),
            'error' => $error
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

    public static function isLoggedIn()
    {
        $session = Session::getInstance();
        return $session->sessionHas('user_id') && !empty($session->sessionGet('user_id'));
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            $config = Config::getInstance();
            header('Location: ' . $config->appConfig('url') . '/login');
            exit;
        }
    }

    public static function getUser()
    {
        if (self::isLoggedIn()) {
            $session = Session::getInstance();
            return [
                'id' => $session->sessionGet('user_id'),
                'username' => $session->sessionGet('username'),
                'nama' => $session->sessionGet('nama'),
                'role' => $session->sessionGet('role')
            ];
        }
        return null;
    }
}