<?php
/**
 * Maintenance Mode Toggle Utility
 * 
 * Usage:
 * php maintenance.php on    - Enable maintenance mode
 * php maintenance.php off   - Disable maintenance mode
 * php maintenance.php       - Show current status
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Config;

function toggleMaintenanceMode($mode = null) {
    $configFile = __DIR__ . '/config/config.php';
    
    if (!file_exists($configFile)) {
        echo "❌ Configuration file not found!\n";
        return false;
    }
    
    // Read current config
    $config = require $configFile;
    $currentStatus = $config['app']['maintenance'] ?? false;
    
    // Show current status if no mode specified
    if ($mode === null) {
        echo "🔧 Maintenance Mode Status\n";
        echo "========================\n";
        echo "Application: " . $config['app']['name'] . "\n";
        echo "Current Status: " . ($currentStatus ? "🔴 ENABLED" : "🟢 DISABLED") . "\n";
        echo "Version: " . $config['app']['version'] . "\n";
        echo "\nUsage:\n";
        echo "  php maintenance.php on   - Enable maintenance mode\n";
        echo "  php maintenance.php off  - Disable maintenance mode\n";
        return true;
    }
    
    // Determine new status
    $newStatus = ($mode === 'on' || $mode === 'enable' || $mode === 'true');
    
    if ($currentStatus === $newStatus) {
        echo "ℹ️  Maintenance mode is already " . ($newStatus ? "ENABLED" : "DISABLED") . "\n";
        return true;
    }
    
    // Update config
    $config['app']['maintenance'] = $newStatus;
    
    // Generate new config file content
    $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
    
    // Write to file
    if (file_put_contents($configFile, $configContent)) {
        echo "✅ Maintenance mode " . ($newStatus ? "ENABLED" : "DISABLED") . " successfully!\n";
        echo "📝 Configuration updated: " . $configFile . "\n";
        
        if ($newStatus) {
            echo "\n🔴 MAINTENANCE MODE ACTIVE\n";
            echo "All users will see the maintenance page.\n";
            echo "To disable: php maintenance.php off\n";
        } else {
            echo "\n🟢 APPLICATION RESTORED\n";
            echo "Users can now access the application normally.\n";
        }
        
        return true;
    } else {
        echo "❌ Failed to update configuration file!\n";
        echo "Please check file permissions for: " . $configFile . "\n";
        return false;
    }
}

// Main execution
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from command line.\n";
    exit(1);
}

$mode = isset($argv[1]) ? strtolower(trim($argv[1])) : null;

// Validate mode parameter
if ($mode !== null && !in_array($mode, ['on', 'off', 'enable', 'disable', 'true', 'false'])) {
    echo "❌ Invalid parameter: $mode\n";
    echo "Valid options: on, off, enable, disable\n";
    exit(1);
}

try {
    toggleMaintenanceMode($mode);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}