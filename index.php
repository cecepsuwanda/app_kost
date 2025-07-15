<?php
session_start();

// Define constants
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Include autoloader
require_once APP_PATH . '/core/Autoloader.php';

// Create autoloader instance
$autoloader = new Autoloader();
$autoloader->register();

// Include config
require_once CONFIG_PATH . '/config.php';

// Create router instance
$router = new Router();

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
if (isset($_POST['action'])) {
    $router->add('/ajax', 'Ajax@handle');
}

// Run the router
$router->run();