<?php

namespace App\Controllers;

use App\Core\Controller;

class Auth extends Controller
{
    public function login()
    {
        // If already logged in, redirect to admin
        if (\App\Core\Session::has('user_id')) {
            $this->redirect(\App\Core\Config::app('url').'/admin');
        }

        $error = '';

        if (\App\Core\Request::isPost()) {
            $username = $this->post('username');
            $password = $this->post('password');

            if (empty($username) || empty($password)) {
                $error = 'Username dan password harus diisi';
            } else {
                $userModel = $this->loadModel('UserModel');
                $user = $userModel->authenticate($username, $password);

                if ($user) {
                    // Set session
                    \App\Core\Session::set('user_id', $user['id']);
                    \App\Core\Session::set('username', $user['username']);
                    \App\Core\Session::set('nama', $user['nama']);
                    \App\Core\Session::set('role', $user['role']);
                    \App\Core\Session::set('login_time', time());

                    // Update last login
                    $userModel->updateLastLogin($user['id']);

                    // Redirect to admin
                    $this->redirect(\App\Core\Config::app('url').'/admin');
                } else {
                    $error = 'Username atau password salah';
                }
            }
        }

        $data = [
            'title' => 'Login Admin - ' . \App\Core\Config::app('name'),
            'error' => $error
        ];

        $this->loadView('auth/login', $data);
    }

    public function logout()
    {
        // Destroy session
        \App\Core\Session::destroy();
        
        // Redirect to login
        $this->redirect(\App\Core\Config::app('url').'/login');
    }

    public static function isLoggedIn()
    {
        return \App\Core\Session::has('user_id') && !empty(\App\Core\Session::get('user_id'));
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . \App\Core\Config::app('url') . '/login');
            exit;
        }
    }

    public static function getUser()
    {
        if (self::isLoggedIn()) {
            return [
                'id' => \App\Core\Session::get('user_id'),
                'username' => \App\Core\Session::get('username'),
                'nama' => \App\Core\Session::get('nama'),
                'role' => \App\Core\Session::get('role')
            ];
        }
        return null;
    }
}