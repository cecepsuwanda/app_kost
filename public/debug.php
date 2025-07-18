<?php
// Debug file to help identify 403 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Information</h1>";

// Check if we can access the main application
echo "<h2>1. Basic PHP Test</h2>";
echo "PHP is working: ✅<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Parent directory: " . dirname(__DIR__) . "<br>";

// Check if we can access the config
echo "<h2>2. Configuration Test</h2>";
$configFile = dirname(__DIR__) . '/config/config.php';
if (file_exists($configFile)) {
    echo "Config file exists: ✅<br>";
    $config = require $configFile;
    echo "Config loaded: ✅<br>";
    echo "App name: " . $config['app']['name'] . "<br>";
    echo "App URL: " . $config['app']['url'] . "<br>";
    echo "Maintenance mode: " . ($config['app']['maintenance'] ? 'ON' : 'OFF') . "<br>";
} else {
    echo "Config file missing: ❌<br>";
}

// Check if we can access the autoloader
echo "<h2>3. Autoloader Test</h2>";
$autoloaderFile = dirname(__DIR__) . '/app/core/Autoloader.php';
if (file_exists($autoloaderFile)) {
    echo "Autoloader file exists: ✅<br>";
    require_once $autoloaderFile;
    echo "Autoloader included: ✅<br>";
} else {
    echo "Autoloader file missing: ❌<br>";
}

// Check if we can access the main index.php
echo "<h2>4. Main Application Test</h2>";
$indexFile = __DIR__ . '/index.php';
if (file_exists($indexFile)) {
    echo "Index file exists: ✅<br>";
    echo "Index file size: " . filesize($indexFile) . " bytes<br>";
} else {
    echo "Index file missing: ❌<br>";
}

// Check Apache modules
echo "<h2>5. Apache Modules</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? '✅' : '❌') . "<br>";
    echo "mod_php: " . (in_array('mod_php', $modules) ? '✅' : '❌') . "<br>";
} else {
    echo "Cannot check Apache modules (function not available)<br>";
}

// Check file permissions
echo "<h2>6. File Permissions</h2>";
$files = [
    'index.php' => __DIR__ . '/index.php',
    'config.php' => dirname(__DIR__) . '/config/config.php',
    'autoloader.php' => dirname(__DIR__) . '/app/core/Autoloader.php'
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        $perms = fileperms($path);
        $perms_octal = substr(sprintf('%o', $perms), -4);
        echo "$name: $perms_octal<br>";
    } else {
        echo "$name: File not found<br>";
    }
}

// Check .htaccess
echo "<h2>7. .htaccess Test</h2>";
$htaccessFile = dirname(__DIR__) . '/.htaccess';
if (file_exists($htaccessFile)) {
    echo ".htaccess exists: ✅<br>";
    echo ".htaccess size: " . filesize($htaccessFile) . " bytes<br>";
} else {
    echo ".htaccess missing: ❌<br>";
}

echo "<h2>8. Server Information</h2>";
echo "Server software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "PHP SAPI: " . php_sapi_name() . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "<br>";
?> 