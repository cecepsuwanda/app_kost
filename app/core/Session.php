<?php

namespace App\Core;

class Session
{
    private static $instance = null;

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Static methods (for backward compatibility)
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy()
    {
        self::start();
        session_destroy();
    }

    public static function regenerate()
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function flash($key, $value = null)
    {
        self::start();
        if ($value !== null) {
            $_SESSION['flash'][$key] = $value;
        } else {
            $value = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $value;
        }
    }

    public static function hasFlash($key)
    {
        self::start();
        return isset($_SESSION['flash'][$key]);
    }

    // Instance methods (new approach)
    public function session($key, $value = null)
    {
        if ($value !== null) {
            $_SESSION[$key] = $value;
        } else {
            return $_SESSION[$key] ?? null;
        }
    }

    public function sessionGet($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function sessionSet($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function sessionHas($key)
    {
        return isset($_SESSION[$key]);
    }

    public function sessionRemove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function sessionDestroy()
    {
        session_destroy();
    }

    public function sessionRegenerate()
    {
        session_regenerate_id(true);
    }

    public function sessionFlash($key, $value = null)
    {
        if ($value !== null) {
            $_SESSION['flash'][$key] = $value;
        } else {
            $value = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $value;
        }
    }

    public function sessionHasFlash($key)
    {
        return isset($_SESSION['flash'][$key]);
    }
} 