<?php

namespace App\Core;

class HelperManager
{
    private Config $config;
    private array $loadedHelpers = [];
    private array $aliases = [];
    private static ?HelperManager $instance = null;

    public function __construct(Config $config = null)
    {
        $this->config = $config ?? Config::getInstance();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): HelperManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load helpers based on configuration
     */
    public function loadHelpers(?string $context = null): void
    {
        $helpersConfig = $this->config->get('helpers', []);
        
        // Load helpers based on context (controller/route)
        if ($context && isset($helpersConfig['conditional'][$context])) {
            $this->loadSpecificHelpers($helpersConfig['conditional'][$context]);
            return;
        }

        // Load all helpers if configured
        if ($helpersConfig['load_all'] ?? false) {
            $this->loadAllHelpers();
            return;
        }

        // Load specific helpers from autoload list
        $autoloadHelpers = $helpersConfig['autoload'] ?? [];
        $this->loadSpecificHelpers($autoloadHelpers);

        // Load global functions if enabled
        if ($helpersConfig['load_functions'] ?? false) {
            $this->loadGlobalFunctions();
        }

        // Setup aliases
        $this->setupAliases($helpersConfig['aliases'] ?? []);
    }

    /**
     * Load specific helpers by name
     */
    public function loadSpecificHelpers(array $helperNames): void
    {
        $helpersConfig = $this->config->get('helpers', []);
        $helpersPath = APP_PATH . ($helpersConfig['path'] ?? '/helpers/');

        foreach ($helperNames as $helperName) {
            $this->loadHelper($helperName, $helpersPath);
        }
    }

    /**
     * Load all helpers in the helpers directory
     */
    public function loadAllHelpers(): void
    {
        $helpersConfig = $this->config->get('helpers', []);
        $helpersPath = APP_PATH . ($helpersConfig['path'] ?? '/helpers/');

        if (!is_dir($helpersPath)) {
            return;
        }

        $helpers = glob($helpersPath . '*.php');
        foreach ($helpers as $helperFile) {
            $helperName = basename($helperFile, '.php');
            $this->loadHelper($helperName, $helpersPath);
        }
    }

    /**
     * Load a single helper
     */
    private function loadHelper(string $helperName, string $helpersPath): void
    {
        // Skip if already loaded
        if (in_array($helperName, $this->loadedHelpers)) {
            return;
        }

        $helperFile = $helpersPath . $helperName . '.php';
        
        if (file_exists($helperFile)) {
            require_once $helperFile;
            $this->loadedHelpers[] = $helperName;
            
            // Log for debugging
            if ($this->config->get('debug', false)) {
                error_log("Helper loaded: $helperName");
            }
        } else {
            // Log warning if helper not found
            error_log("Helper not found: $helperName at $helperFile");
        }
    }

    /**
     * Setup global aliases for easier access
     */
    private function setupAliases(array $aliases): void
    {
        foreach ($aliases as $alias => $className) {
            if (class_exists($className)) {
                $this->aliases[$alias] = $className;
                
                // Create global function for easier access (optional)
                if (!function_exists($alias)) {
                    eval("function $alias() { return \\$className; }");
                }
            }
        }
    }

    /**
     * Get helper by alias
     */
    public function getHelper(string $alias): ?string
    {
        return $this->aliases[$alias] ?? null;
    }

    /**
     * Check if helper is loaded
     */
    public function isHelperLoaded(string $helperName): bool
    {
        return in_array($helperName, $this->loadedHelpers);
    }

    /**
     * Get all loaded helpers
     */
    public function getLoadedHelpers(): array
    {
        return $this->loadedHelpers;
    }

    /**
     * Get all aliases
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Load helpers conditionally based on current route/controller
     */
    public function loadHelpersForRoute(string $route): void
    {
        // Extract context from route (e.g., 'admin' from '/admin/dashboard')
        $context = $this->extractContextFromRoute($route);
        $this->loadHelpers($context);
    }

    /**
     * Extract context from route for conditional loading
     */
    private function extractContextFromRoute(string $route): ?string
    {
        $route = trim($route, '/');
        $segments = explode('/', $route);
        
        // Return first segment as context
        return !empty($segments) ? $segments[0] : null;
    }

    /**
     * Magic method to call helper methods directly
     */
    public function __call(string $method, array $args)
    {
        // Try to find the method in loaded helper classes
        foreach ($this->aliases as $alias => $className) {
            if (method_exists($className, $method)) {
                return call_user_func_array([$className, $method], $args);
            }
        }

        throw new \BadMethodCallException("Helper method '$method' not found");
    }

    /**
     * Load global helper functions
     */
    private function loadGlobalFunctions(): void
    {
        $functionsFile = APP_PATH . '/helpers/functions.php';
        if (file_exists($functionsFile)) {
            require_once $functionsFile;
            
            if ($this->config->get('debug', false)) {
                error_log("Global helper functions loaded");
            }
        }
    }

    /**
     * Clear all loaded helpers (useful for testing)
     */
    public function clearHelpers(): void
    {
        $this->loadedHelpers = [];
        $this->aliases = [];
    }
}