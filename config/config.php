<?php

// Define legacy constants for backward compatibility
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_kost');
define('DB_USER', 'cecep');
define('DB_PASS', 'Cecep@1982');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Sistem Manajemen Kos');
define('APP_VERSION', '2.3.0');
define('APP_URL', 'http://localhost/app_kost');

define('SESSION_TIMEOUT', 1800); // 30 minutes
define('PASSWORD_MIN_LENGTH', 6);
define('DEBUG_MODE', false);
define('TIMEZONE', 'Asia/Jakarta');
define('MAX_FILE_SIZE', 2048); // KB
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

return [
    // Database configuration
    'database' => [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'charset' => DB_CHARSET
    ],
    
    // Application configuration
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'url' => APP_URL
    ],
    
    // Session configuration
    'session' => [
        'timeout' => SESSION_TIMEOUT
    ],
    
    // Security configuration
    'security' => [
        'password_min_length' => PASSWORD_MIN_LENGTH
    ],
    
    // Timezone
    'timezone' => TIMEZONE,
    
    // Upload configuration
    'upload' => [
        'max_file_size' => MAX_FILE_SIZE,
        'upload_path' => UPLOAD_PATH
    ],
    
    // Error reporting
    'error_reporting' => DEBUG_MODE ? E_ALL : 0,
    'display_errors' => DEBUG_MODE ? 1 : 0
];