<?php

// Define constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Include autoloader
require_once APP_PATH . '/core/Autoloader.php';

// Create autoloader instance
$autoloader = new App\Core\Autoloader();
$autoloader->register();

// Initialize configuration
$config = App\Core\Config::getInstance(); 