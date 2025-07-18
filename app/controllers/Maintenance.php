<?php

namespace App\Controllers;

use App\Core\Controller;

class Maintenance extends Controller
{
    public function __construct($app = null)
    {
        parent::__construct($app);
    }

    public function index()
    {
        // Set maintenance page title
        $data = [
            'title' => 'Maintenance Mode - ' . $this->config->appConfig('name')
        ];

        // Load maintenance view (note: this bypasses the normal layout)
        $this->loadMaintenanceView('maintenance/index', $data);
    }

    /**
     * Special method to load maintenance view without normal layout processing
     */
    private function loadMaintenanceView($view, $data = [])
    {
        // Make essential variables available to maintenance view
        $appName = $this->config->appConfig('name');
        
        // Extract data for view
        extract($data);
        
        // Set proper HTTP response code
        http_response_code(503); // Service Unavailable
        
        // Add headers to prevent caching
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Retry-After: 3600'); // Suggest retry after 1 hour
        
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            // Fallback maintenance message
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Mode</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            margin-top: 50px; 
            background: #f5f5f5;
        }
        .container { 
            max-width: 500px; 
            margin: 0 auto; 
            padding: 2rem; 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 { color: #e74c3c; }
        p { color: #666; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Maintenance Mode</h1>
        <p>We are currently performing scheduled maintenance.</p>
        <p>Please check back later.</p>
        <p><strong>Application:</strong> ' . htmlspecialchars($appName) . '</p>
    </div>
</body>
</html>';
        }
        
        exit; // Important: Stop execution after showing maintenance page
    }
}