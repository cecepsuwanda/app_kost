<?php

class Auth extends Controller
{
    public function login()
    {
        // If already logged in, redirect to admin
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/admin');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->post('username');
            $password = $this->post('password');

            if (empty($username) || empty($password)) {
                $error = 'Username dan password harus diisi';
            } else {
                $userModel = $this->loadModel('UserModel');
                $user = $userModel->authenticate($username, $password);

                if ($user) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();

                    // Update last login
                    $userModel->updateLastLogin($user['id']);

                    // Redirect to admin
                    $this->redirect('/admin');
                } else {
                    $error = 'Username atau password salah';
                }
            }
        }

        $data = [
            'title' => 'Login Admin - ' . APP_NAME,
            'error' => $error
        ];

        $this->loadView('auth/login', $data);
    }

    public function logout()
    {
        // Destroy session
        session_destroy();
        
        // Redirect to login
        $this->redirect('/login');
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public static function getUser()
    {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'nama' => $_SESSION['nama'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }
}