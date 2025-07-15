<?php

namespace App\Core;

class Autoloader
{
    private $namespaces = [];

    public function __construct()
    {
        // Register PSR-4 namespaces
        $this->namespaces = [
            'App\\Core\\' => APP_PATH . '/core/',
            'App\\Controllers\\' => APP_PATH . '/controllers/',
            'App\\Models\\' => APP_PATH . '/models/',
        ];
    }

    public function register()
    {
        spl_autoload_register([$this, 'load']);
    }

    private function load($className)
    {
        // Check if class has namespace
        foreach ($this->namespaces as $namespace => $path) {
            if (strpos($className, $namespace) === 0) {
                $relativeClass = substr($className, strlen($namespace));
                $file = $path . str_replace('\\', '/', $relativeClass) . '.php';
                
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }

        // Fallback for non-namespaced classes (backward compatibility)
        $paths = [
            APP_PATH . '/core/',
            APP_PATH . '/controllers/',
            APP_PATH . '/models/',
        ];

        foreach ($paths as $path) {
            $file = $path . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}