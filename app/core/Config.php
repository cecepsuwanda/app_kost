<?php

namespace App\Core;

class Config
{
    private static $instance = null;
    private static $config = null;

    private function __construct()
    {
        if (self::$config === null) {
            $configFile = ROOT_PATH . '/config/config.php';
            if (file_exists($configFile)) {
                self::$config = require $configFile;
                
                // Apply timezone
                date_default_timezone_set(self::$config['timezone']);
                
                // Apply error reporting
                error_reporting(self::$config['error_reporting']);
                ini_set('display_errors', self::$config['display_errors']);
            } else {
                throw new \Exception('Configuration file not found');
            }
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
    public static function get($key, $default = null)
    {
        self::getInstance();
        $keys = explode('.', $key);
        
        $value = self::$config;
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }

    public static function database($key = null)
    {
        if ($key === null) {
            return self::get('database');
        }
        return self::get("database.$key");
    }

    public static function app($key = null)
    {
        if ($key === null) {
            return self::get('app');
        }
        return self::get("app.$key");
    }

    // Instance methods (new approach)
    public function config($key, $default = null)
    {
        $keys = explode('.', $key);
        
        $value = self::$config;
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }

    public function db($key = null)
    {
        if ($key === null) {
            return $this->config('database');
        }
        return $this->config("database.$key");
    }

    public function appConfig($key = null)
    {
        if ($key === null) {
            return $this->config('app');
        }
        return $this->config("app.$key");
    }
} 