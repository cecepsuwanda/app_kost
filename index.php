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

// Application-Centric Architecture Implementation
// As recommended in README.md "Rekomendasi Perbaikan Arsitektur"
if (class_exists('App\Core\Application')) {
    try {
        // New application-centric approach
        $app = new App\Core\Application();
        $app->initialize();
        $app->boot();
        $app->run();
    } catch (\Exception $e) {
        // Fallback to router-centric approach if Application fails
        error_log("Application failed, falling back to router-centric approach: " . $e->getMessage());
        $fallback = true;
    }
} else {
    $fallback = true;
}

// Fallback to current router-centric approach for backward compatibility
if (isset($fallback) && $fallback) {
    // Initialize Config (this will also start session and apply settings)
    \App\Core\Config::getInstance();

    // Create router instance
    $router = new App\Core\Router();

    // Define routes
    $router->add('/', 'Home@index');
    $router->add('/login', 'Auth@login');
    $router->add('/logout', 'Auth@logout');
    $router->add('/admin', 'Admin@index');
    $router->add('/admin/penghuni', 'Admin@penghuni');
    $router->add('/admin/kamar', 'Admin@kamar');
    $router->add('/admin/barang', 'Admin@barang');
    $router->add('/admin/tagihan', 'Admin@tagihan');
    $router->add('/admin/pembayaran', 'Admin@pembayaran');
    $router->add('/install', 'Install@index');
    $router->add('/install/run', 'Install@run');

    // Handle AJAX requests
    $request = \App\Core\Request::getInstance();
    if ($request->hasParam('action')) {
        $router->add('/ajax', 'Ajax@handle');
    }

    // Run the router
    $router->run();
}