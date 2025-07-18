<?php

namespace App\Core;

use PDO;
use PDOException;
use App\Core\Config;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $config = Config::getInstance();
            $dsn = "mysql:host=" . $config->db('host') . ";dbname=" . $config->db('name') . ";charset=" . $config->db('charset');
            $this->connection = new PDO($dsn, $config->db('user'), $config->db('pass'));
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleConnectionError($e, $config);
        }
    }

    private function handleConnectionError(PDOException $e, $config)
    {
        // Log the error
        $this->logDatabaseError($e->getMessage());
        
        // Check if we're in web context
        if (isset($_SERVER['HTTP_HOST'])) {
            // Prepare variables for the error page
            $dbHost = $config->db('host');
            $dbName = $config->db('name');
            $dbUser = $config->db('user');
            $errorMessage = $e->getMessage();
            $baseUrl = $config->appConfig('url');
            
            // Include the database error page
            include APP_PATH . '/views/errors/database.php';
            exit;
        } else {
            // CLI context - just die with message
            die("Database connection failed: " . $e->getMessage());
        }
    }

    private function logDatabaseError($message)
    {
        $logFile = ROOT_PATH . '/storage/logs/error.log';
        $logDir = dirname($logFile);
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] DATABASE ERROR: $message" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->handleQueryError($e, $sql, $params);
        }
    }

    private function handleQueryError(PDOException $e, $sql, $params)
    {
        // Log the error with more details
        $errorDetails = [
            'message' => $e->getMessage(),
            'sql' => $sql,
            'params' => $params,
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
        
        $this->logDatabaseError("Query Error: " . json_encode($errorDetails));
        
        // In development, show detailed error
        if (defined('APP_DEBUG') && APP_DEBUG) {
            $errorPage = $this->createDetailedErrorPage($e, $sql, $params);
            echo $errorPage;
            exit;
        } else {
            // In production, show generic error
            die("Database query failed. Please contact administrator.");
        }
    }

    private function createDetailedErrorPage($e, $sql, $params)
    {
        $errorDetails = [
            'message' => $e->getMessage(),
            'sql' => $sql,
            'params' => $params,
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ];
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Query Error</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background: #f8f9fa; padding: 2rem; }
                .error-container { max-width: 1000px; margin: 0 auto; }
                pre { background: #f1f3f4; padding: 1rem; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="alert alert-danger">
                    <h4><i class="bi bi-exclamation-triangle"></i> Database Query Error</h4>
                    <p><strong>Message:</strong> <?= htmlspecialchars($errorDetails['message']) ?></p>
                    <p><strong>Error Code:</strong> <?= htmlspecialchars($errorDetails['code']) ?></p>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header"><strong>SQL Query</strong></div>
                    <div class="card-body">
                        <pre><?= htmlspecialchars($errorDetails['sql']) ?></pre>
                    </div>
                </div>
                
                <div class="card mb-3">
                    <div class="card-header"><strong>Parameters</strong></div>
                    <div class="card-body">
                        <pre><?= htmlspecialchars(json_encode($errorDetails['params'], JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header"><strong>Stack Trace</strong></div>
                    <div class="card-body">
                        <pre><?= htmlspecialchars($errorDetails['trace']) ?></pre>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
                    <a href="/" class="btn btn-primary">Home</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(', ', $set);
        
        $sql = "UPDATE $table SET $set WHERE $where";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}