<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kos_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Sistem Manajemen Kos');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);