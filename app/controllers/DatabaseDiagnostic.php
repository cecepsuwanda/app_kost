<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;
use PDOException;

class DatabaseDiagnostic extends Controller
{
    public function __construct($app = null)
    {
        parent::__construct($app);
    }

    public function index()
    {
        // Check if user is logged in and is admin
        if (!$this->isLoggedIn()) {
            $this->redirect($this->config->appConfig('url') . '/auth/login');
            return;
        }

        $user = $this->getUser();
        if ($user['role'] !== 'superadmin') {
            $this->redirect($this->config->appConfig('url') . '/admin');
            return;
        }

        $diagnostics = $this->runDatabaseDiagnostics();

        $data = [
            'title' => 'Database Diagnostics - ' . $this->config->appConfig('name'),
            'diagnostics' => $diagnostics,
            'showSidebar' => true
        ];

        $this->loadView('admin/database-diagnostic', $data);
    }

    public function logs()
    {
        // Check if user is logged in and is admin
        if (!$this->isLoggedIn()) {
            http_response_code(401);
            echo "Unauthorized";
            return;
        }

        $user = $this->getUser();
        if ($user['role'] !== 'superadmin') {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $logFile = ROOT_PATH . '/storage/logs/error.log';
        
        if (!file_exists($logFile)) {
            echo "No error log file found.";
            return;
        }

        // Get last 100 lines of log file
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $recentLines = array_slice($lines, -100);
        
        // Filter for database-related errors
        $dbErrors = array_filter($recentLines, function($line) {
            return stripos($line, 'database') !== false || 
                   stripos($line, 'mysql') !== false || 
                   stripos($line, 'pdo') !== false;
        });

        if (empty($dbErrors)) {
            echo "No recent database errors found in log file.";
        } else {
            echo implode("\n", $dbErrors);
        }
    }

    public function clearLogs()
    {
        // Check if user is logged in and is admin
        if (!$this->isLoggedIn()) {
            $this->redirect($this->config->appConfig('url') . '/auth/login');
            return;
        }

        $user = $this->getUser();
        if ($user['role'] !== 'superadmin') {
            $this->redirect($this->config->appConfig('url') . '/admin');
            return;
        }

        $logFile = ROOT_PATH . '/storage/logs/error.log';
        
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
            $this->session->sessionFlash('success', 'Error logs cleared successfully');
        } else {
            $this->session->sessionFlash('warning', 'No log file found to clear');
        }

        $this->redirect($this->config->appConfig('url') . '/database-diagnostic');
    }

    public function ping()
    {
        // Simple endpoint to check if the application is responding
        try {
            $db = Database::getInstance();
            $result = $db->fetch("SELECT 1 as status");
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok', 'database' => 'connected']);
        } catch (\Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        }
    }

    private function runDatabaseDiagnostics()
    {
        $diagnostics = [
            'connection' => $this->testConnection(),
            'database_info' => $this->getDatabaseInfo(),
            'tables' => $this->checkTables(),
            'table_structure' => $this->checkTableStructures(),
            'data_integrity' => $this->checkDataIntegrity(),
            'performance' => $this->checkPerformance(),
            'storage' => $this->checkStorage(),
            'logs' => $this->getRecentErrors()
        ];

        return $diagnostics;
    }

    private function testConnection()
    {
        try {
            $db = Database::getInstance();
            $connection = $db->getConnection();
            
            // Test basic query
            $stmt = $connection->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            return [
                'status' => 'success',
                'message' => 'Database connection successful',
                'details' => [
                    'host' => $this->config->db('host'),
                    'database' => $this->config->db('name'),
                    'charset' => $this->config->db('charset'),
                    'connection_id' => $connection->query("SELECT CONNECTION_ID() as id")->fetch()['id']
                ]
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
                'details' => [
                    'host' => $this->config->db('host'),
                    'database' => $this->config->db('name'),
                    'charset' => $this->config->db('charset')
                ]
            ];
        }
    }

    private function getDatabaseInfo()
    {
        try {
            $db = Database::getInstance();
            
            $version = $db->fetch("SELECT VERSION() as version");
            $variables = $db->fetchAll("SHOW VARIABLES WHERE Variable_name IN ('innodb_buffer_pool_size', 'max_connections', 'wait_timeout', 'interactive_timeout')");
            $status = $db->fetchAll("SHOW STATUS WHERE Variable_name IN ('Threads_connected', 'Queries', 'Uptime', 'Slow_queries')");
            
            return [
                'status' => 'success',
                'version' => $version['version'],
                'variables' => $this->formatVariables($variables),
                'status_info' => $this->formatVariables($status)
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not retrieve database information',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkTables()
    {
        try {
            $db = Database::getInstance();
            
            $expectedTables = [
                'users', 'tb_penghuni', 'tb_kamar', 'tb_barang', 'tb_kmr_penghuni', 
                'tb_detail_kmr_penghuni', 'tb_brng_bawaan', 'tb_tagihan', 'tb_bayar'
            ];
            
            $existingTables = $db->fetchAll("SHOW TABLES");
            $existingTableNames = array_column($existingTables, 'Tables_in_' . $this->config->db('name'));
            
            $missing = array_diff($expectedTables, $existingTableNames);
            $extra = array_diff($existingTableNames, $expectedTables);
            
            $tableStatus = [];
            foreach ($expectedTables as $table) {
                if (in_array($table, $existingTableNames)) {
                    $status = $db->fetch("SHOW TABLE STATUS LIKE '$table'");
                    $tableStatus[$table] = [
                        'exists' => true,
                        'engine' => $status['Engine'],
                        'rows' => $status['Rows'],
                        'data_length' => $status['Data_length'],
                        'index_length' => $status['Index_length'],
                        'collation' => $status['Collation']
                    ];
                } else {
                    $tableStatus[$table] = ['exists' => false];
                }
            }
            
            return [
                'status' => empty($missing) ? 'success' : 'warning',
                'missing_tables' => $missing,
                'extra_tables' => $extra,
                'table_status' => $tableStatus
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not check table status',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkTableStructures()
    {
        try {
            $db = Database::getInstance();
            $issues = [];
            
            // Check for essential columns
            $requiredColumns = [
                'tb_kamar' => ['id', 'nomor', 'gedung', 'harga'],
                'tb_tagihan' => ['id', 'bulan', 'tahun', 'tanggal', 'id_kmr_penghuni', 'jml_tagihan'],
                'tb_penghuni' => ['id', 'nama', 'no_ktp', 'no_hp', 'tgl_masuk'],
                'users' => ['id', 'username', 'password', 'nama', 'role']
            ];
            
            foreach ($requiredColumns as $table => $columns) {
                try {
                    $tableColumns = $db->fetchAll("DESCRIBE $table");
                    $existingColumns = array_column($tableColumns, 'Field');
                    
                    $missingColumns = array_diff($columns, $existingColumns);
                    if (!empty($missingColumns)) {
                        $issues[$table] = [
                            'type' => 'missing_columns',
                            'missing' => $missingColumns
                        ];
                    }
                } catch (PDOException $e) {
                    $issues[$table] = [
                        'type' => 'table_not_accessible',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return [
                'status' => empty($issues) ? 'success' : 'warning',
                'issues' => $issues
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not check table structures',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkDataIntegrity()
    {
        try {
            $db = Database::getInstance();
            $issues = [];
            
            // Check for orphaned records
            $orphanedChecks = [
                'tb_detail_kmr_penghuni without tb_kmr_penghuni' => 
                    "SELECT COUNT(*) as count FROM tb_detail_kmr_penghuni dkp 
                     LEFT JOIN tb_kmr_penghuni kp ON dkp.id_kmr_penghuni = kp.id 
                     WHERE kp.id IS NULL",
                     
                'tb_detail_kmr_penghuni without tb_penghuni' => 
                    "SELECT COUNT(*) as count FROM tb_detail_kmr_penghuni dkp 
                     LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id 
                     WHERE p.id IS NULL",
                     
                'tb_kmr_penghuni without tb_kamar' => 
                    "SELECT COUNT(*) as count FROM tb_kmr_penghuni kp 
                     LEFT JOIN tb_kamar k ON kp.id_kamar = k.id 
                     WHERE k.id IS NULL",
                     
                'tb_tagihan without tb_kmr_penghuni' => 
                    "SELECT COUNT(*) as count FROM tb_tagihan t 
                     LEFT JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id 
                     WHERE kp.id IS NULL",
                     
                'tb_bayar without tb_tagihan' => 
                    "SELECT COUNT(*) as count FROM tb_bayar b 
                     LEFT JOIN tb_tagihan t ON b.id_tagihan = t.id 
                     WHERE t.id IS NULL"
            ];
            
            foreach ($orphanedChecks as $check => $query) {
                try {
                    $result = $db->fetch($query);
                    if ($result['count'] > 0) {
                        $issues[] = [
                            'type' => 'orphaned_records',
                            'description' => $check,
                            'count' => $result['count']
                        ];
                    }
                } catch (PDOException $e) {
                    $issues[] = [
                        'type' => 'check_failed',
                        'description' => $check,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            return [
                'status' => empty($issues) ? 'success' : 'warning',
                'issues' => $issues
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not check data integrity',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkPerformance()
    {
        try {
            $db = Database::getInstance();
            
            // Get slow queries
            $slowQueries = $db->fetch("SHOW STATUS LIKE 'Slow_queries'");
            
            // Check for missing indexes (simplified check)
            $missingIndexes = [];
            
            // Check table sizes
            $tableSizes = $db->fetchAll("
                SELECT 
                    table_name,
                    table_rows,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = '" . $this->config->db('name') . "'
                ORDER BY size_mb DESC
            ");
            
            return [
                'status' => 'success',
                'slow_queries' => $slowQueries['Value'],
                'table_sizes' => $tableSizes,
                'missing_indexes' => $missingIndexes
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not check performance metrics',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkStorage()
    {
        try {
            $db = Database::getInstance();
            
            $storage = $db->fetch("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size_mb,
                    COUNT(*) as table_count
                FROM information_schema.tables 
                WHERE table_schema = '" . $this->config->db('name') . "'
            ");
            
            return [
                'status' => 'success',
                'database_size_mb' => $storage['db_size_mb'],
                'table_count' => $storage['table_count']
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => 'Could not check storage information',
                'error' => $e->getMessage()
            ];
        }
    }

    private function getRecentErrors()
    {
        $errors = [];
        $logFile = ROOT_PATH . '/storage/logs/error.log';
        
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            // Get last 20 lines
            $recentLines = array_slice($lines, -20);
            
            foreach ($recentLines as $line) {
                if (stripos($line, 'database') !== false || stripos($line, 'mysql') !== false || stripos($line, 'pdo') !== false) {
                    $errors[] = $line;
                }
            }
        }
        
        return [
            'status' => 'success',
            'recent_errors' => $errors,
            'log_file_exists' => file_exists($logFile),
            'log_file_size' => file_exists($logFile) ? filesize($logFile) : 0
        ];
    }

    private function formatVariables($variables)
    {
        $formatted = [];
        foreach ($variables as $var) {
            $formatted[$var['Variable_name']] = $var['Value'];
        }
        return $formatted;
    }
}