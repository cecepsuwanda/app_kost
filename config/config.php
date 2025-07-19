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
        'url' => 'http://localhost/app_kost',
        'maintenance' => false // Set to true to enable maintenance mode
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
    
    // Helpers configuration
    'helpers' => [
        // Auto-load specific helpers (recommended for performance)
        'autoload' => [
            'HtmlHelper',
            'ViewHelper',
            'FormHelper'
        ],
        
        // Load all helpers in directory (set to true for convenience)
        'load_all' => false,
        
        // Helpers directory path (relative to APP_PATH)
        'path' => '/helpers/',
        
        // Load global helper functions for easier access
        'load_functions' => true,
        
        // Global aliases for easier access in views
        'aliases' => [
            'Html' => 'App\\Helpers\\HtmlHelper',
            'View' => 'App\\Helpers\\ViewHelper',
            'Form' => 'App\\Helpers\\FormHelper'
        ],
        
        // Conditional loading based on routes/controllers
        'conditional' => [
            'admin' => ['ViewHelper'], // Load only for admin routes
            'api' => [], // No helpers for API routes
        ]
    ],
    
    // Error reporting and debugging
    'error_reporting' => E_ALL,
    'display_errors' => 1,
    'debug' => true, // Set to false in production
];