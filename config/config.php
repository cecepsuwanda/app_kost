<?php

return [
    // Database configuration
    'database' => [
        'host' => 'localhost',
        'name' => 'db_kost',
        'user' => 'cecep',
        'pass' => 'Cecep@1982',
        'charset' => 'utf8mb4'
    ],
    
    // Application configuration
    'app' => [
        'name' => 'Sistem Manajemen Kos',
        'version' => '2.3.0',
        'url' => 'http://localhost/app_kost'
    ],
    
    // Session configuration
    'session' => [
        'timeout' => 1800 // 30 minutes
    ],
    
    // Security configuration
    'security' => [
        'password_min_length' => 6
    ],
    
    // Timezone
    'timezone' => 'Asia/Jakarta',
    
    // Upload configuration
    'upload' => [
        'max_file_size' => 2048, // KB
        'upload_path' => ROOT_PATH . '/uploads'
    ],
    
    // Error reporting
    'error_reporting' => E_ALL,
    'display_errors' => 1
];