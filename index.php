<?php

// Define constants
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Include autoloader
require_once APP_PATH . '/core/Autoloader.php';

// Create autoloader instance
$autoloader = new App\Core\Autoloader();
$autoloader->register();

    try {
        // New application-centric approach
        $app = new App\Core\Application();
        $app->initialize();
        $app->boot();
        $app->run();
    } catch (\Exception $e) {
        // Fallback to router-centric approach if Application fails
        error_log("Application failed, falling back to router-centric approach: " . $e->getMessage());
       
    }

