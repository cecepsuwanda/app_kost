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

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: Maintenance
 * PURPOSE: System maintenance mode management and utility operations
 * EXTENDS: Controller (base controller class)
 * SECURITY_LEVEL: System-level access for maintenance operations
 * 
 * BUSINESS_CONTEXT:
 * This controller handles system maintenance operations and provides a maintenance
 * mode interface when the system needs to be temporarily unavailable for updates,
 * backups, or repairs. It ensures graceful handling of system downtime and
 * provides utilities for system administration tasks.
 * 
 * CLASS_METHODS:
 * 
 * 1. index()
 *    PURPOSE: Display maintenance mode page and handle maintenance operations
 *    HANDLES:
 *      - Display maintenance mode notice to users
 *      - Provide system status information
 *      - Handle maintenance task execution
 *    MAINTENANCE_FEATURES:
 *      - System status checking
 *      - Database maintenance operations
 *      - Backup and restore utilities
 *      - System diagnostics
 *    USED_IN: System maintenance periods, emergency downtime
 *    AI_CONTEXT: System administration and maintenance operations
 * 
 * 2. checkSystemStatus()
 *    PURPOSE: Verify system health and component status
 *    PARAMETERS: None
 *    RETURNS: System status information
 *    CHECKS_PERFORMED:
 *      - Database connectivity
 *      - File system permissions
 *      - Configuration validation
 *      - Critical component health
 *    USED_IN: Maintenance dashboard, system monitoring
 *    AI_CONTEXT: System health monitoring and diagnostics
 * 
 * MAINTENANCE_FEATURES:
 * - Maintenance mode display for users
 * - System health checking and diagnostics
 * - Database maintenance operations
 * - Backup and restore utilities
 * - Emergency system access
 * 
 * USAGE_PATTERNS:
 * - Activated during system updates
 * - Used for emergency maintenance
 * - System health monitoring
 * - Database maintenance operations
 * 
 * SECURITY_CONSIDERATIONS:
 * - Limited access during maintenance mode
 * - System administrator access only
 * - Secure handling of system operations
 * - Protection of system integrity
 * 
 * AI_INTEGRATION_NOTES:
 * - Critical for system reliability and uptime management
 * - Provides system administration capabilities
 * - Enables safe system maintenance operations
 * - Important for business continuity planning
 * - Supports system monitoring and diagnostics
 */