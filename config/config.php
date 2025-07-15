<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_kost');
define('DB_USER', 'cecep');
define('DB_PASS', 'Cecep@1982');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Sistem Manajemen Kos');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/app_kost');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);