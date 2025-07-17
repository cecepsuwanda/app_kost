<?php

namespace App\Core;

class Config
{
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
        if (self::$config === null) {
            new self();
        }
        return self::$config;
    }

    public static function get($key, $default = null)
    {
        $config = self::getInstance();
        $keys = explode('.', $key);
        
        $value = $config;
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
} 