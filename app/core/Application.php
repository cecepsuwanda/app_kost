<?php

namespace App\Core;

class Application
{
    private Router $router;
    private Config $config;
    private Database $database;
    private Session $session;
    private Request $request;
    private bool $initialized = false;
    private bool $booted = false;

    public function __construct()
    {
        // Constructor kept minimal
    }

    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        // Initialize configuration first
        $this->config = Config::getInstance();
        
        // Initialize session
        $this->session = Session::getInstance();
        
        // Initialize request
        $this->request = Request::getInstance();
        
        // Initialize database
        $this->database = Database::getInstance();
        
        // Initialize router
        $this->router = new Router();
        $this->router->setApplication($this);

        $this->initialized = true;
    }

    public function boot(): void
    {
        if (!$this->initialized) {
            throw new \RuntimeException('Application must be initialized before booting');
        }

        if ($this->booted) {
            return;
        }

        // Register routes
        $this->registerRoutes();
        
        // Register middleware (future implementation)
        $this->registerMiddleware();
        
        // Register error handlers
        $this->registerErrorHandlers();

        $this->booted = true;
    }

    public function run(): void
    {
        if (!$this->initialized || !$this->booted) {
            throw new \RuntimeException('Application must be initialized and booted before running');
        }

        try {
            $this->router->run();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function registerRoutes(): void
    {
        // Core routes
        $this->router->add('/', 'Home@index');
        $this->router->add('/login', 'Auth@login');
        $this->router->add('/logout', 'Auth@logout');
        
        // Admin routes (with authentication middleware)
        $this->router->add('/admin', 'Admin@index', ['auth']);
        $this->router->add('/admin/penghuni', 'Admin@penghuni', ['auth']);
        $this->router->add('/admin/kamar', 'Admin@kamar', ['auth']);
        $this->router->add('/admin/barang', 'Admin@barang', ['auth']);
        $this->router->add('/admin/tagihan', 'Admin@tagihan', ['auth']);
        $this->router->add('/admin/pembayaran', 'Admin@pembayaran', ['auth']);
        
        // Installation routes
        $this->router->add('/install', 'Install@index');
        $this->router->add('/install/run', 'Install@run');
        
        // Handle AJAX requests
        if ($this->request->hasParam('action')) {
            $this->router->add('/ajax', 'Ajax@handle', ['auth']);
        }
    }

    private function registerMiddleware(): void
    {
        // Authentication middleware
        $this->router->addMiddleware('auth', function() {
            $session = Session::getInstance();
            if (!$session->get('user_id')) {
                // Redirect to login page
                $config = Config::getInstance();
                $appUrl = $config->get('app.url', '');
                header("Location: $appUrl/login");
                exit;
            }
            return true;
        });

        // CSRF protection middleware (future implementation)
        $this->router->addMiddleware('csrf', function() {
            // TODO: Implement CSRF token validation
            return true;
        });

        // Rate limiting middleware (future implementation)
        $this->router->addMiddleware('rate_limit', function() {
            // TODO: Implement rate limiting
            return true;
        });

        // Global middleware for all requests
        $this->router->addGlobalMiddleware(function() {
            // Set timezone from config
            $config = Config::getInstance();
            $timezone = $config->get('timezone', 'Asia/Jakarta');
            date_default_timezone_set($timezone);
            return true;
        });
    }

    private function registerErrorHandlers(): void
    {
        // Set custom error handler
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        
        // Configure error reporting based on config
        $config = $this->config->get('error_reporting', 0);
        error_reporting($config);
        ini_set('display_errors', $this->config->get('display_errors', 0));
    }

    public function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $this->logError("PHP Error: $message in $file on line $line");

        if ($this->config->get('debug', false)) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        }

        return true;
    }

    public function handleException(\Throwable $e): void
    {
        $this->logError("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

        if ($this->config->get('debug', false)) {
            // Show detailed error in debug mode
            echo "<pre>";
            echo "Uncaught Exception: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString();
            echo "</pre>";
        } else {
            // Show user-friendly error page
            $this->showErrorPage(500);
        }
    }

    private function logError(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        $logFile = ROOT_PATH . '/storage/logs/error.log';
        $logDir = dirname($logFile);
        
        // Create log directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    private function showErrorPage(int $code): void
    {
        http_response_code($code);
        
        $errorView = APP_PATH . "/views/errors/$code.php";
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            echo "<h1>Error $code</h1>";
            echo "<p>An error occurred while processing your request.</p>";
        }
    }

    // Getter methods for dependency injection
    public function getRouter(): Router 
    { 
        if (!$this->initialized) {
            throw new \RuntimeException('Application not initialized');
        }
        return $this->router; 
    }

    public function getConfig(): Config 
    { 
        if (!$this->initialized) {
            throw new \RuntimeException('Application not initialized');
        }
        return $this->config; 
    }

    public function getDatabase(): Database 
    { 
        if (!$this->initialized) {
            throw new \RuntimeException('Application not initialized');
        }
        return $this->database; 
    }

    public function getSession(): Session 
    { 
        if (!$this->initialized) {
            throw new \RuntimeException('Application not initialized');
        }
        return $this->session; 
    }

    public function getRequest(): Request 
    { 
        if (!$this->initialized) {
            throw new \RuntimeException('Application not initialized');
        }
        return $this->request; 
    }

    // Utility methods
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function getVersion(): string
    {
        return $this->config->get('app.version', '2.2.0');
    }

    public function getName(): string
    {
        return $this->config->get('app.name', 'Sistem Manajemen Kos');
    }

    public function isDebug(): bool
    {
        return $this->config->get('debug', false);
    }
}