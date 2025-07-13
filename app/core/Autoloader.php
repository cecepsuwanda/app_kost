<?php

class Autoloader
{
    private $paths = [];

    public function __construct()
    {
        $this->paths = [
            APP_PATH . '/core/',
            APP_PATH . '/controllers/',
            APP_PATH . '/models/',
        ];
    }

    public function register()
    {
        spl_autoload_register([$this, 'load']);
    }

    private function load($className)
    {
        foreach ($this->paths as $path) {
            $file = $path . $className . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}