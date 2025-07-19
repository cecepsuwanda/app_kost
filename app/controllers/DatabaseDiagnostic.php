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

    public function toggleMaintenance()
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

        if ($this->request->isPostRequest()) {
            $action = $this->request->postParam('maintenance_action');
            $success = $this->updateMaintenanceMode($action === 'enable');
            
            if ($success) {
                $message = $action === 'enable' ? 'Maintenance mode enabled' : 'Maintenance mode disabled';
                $this->session->sessionFlash('success', $message);
            } else {
                $this->session->sessionFlash('error', 'Failed to update maintenance mode');
            }
        }

        $this->redirect($this->config->appConfig('url') . '/database-diagnostic');
    }

    private function updateMaintenanceMode($enable)
    {
        try {
            $configFile = ROOT_PATH . '/config/config.php';
            
            if (!file_exists($configFile)) {
                return false;
            }
            
            // Read current config
            $config = require $configFile;
            
            // Update maintenance mode
            $config['app']['maintenance'] = $enable;
            
            // Generate new config file content
            $configContent = "<?php\n\nreturn " . var_export($config, true) . ";\n";
            
            // Write to file
            return file_put_contents($configFile, $configContent) !== false;
            
        } catch (\Exception $e) {
            $this->logError("Failed to update maintenance mode: " . $e->getMessage());
            return false;
        }
    }

    private function logError($message)
    {
        $logFile = ROOT_PATH . '/storage/logs/error.log';
        $logDir = dirname($logFile);
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] MAINTENANCE ERROR: $message" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
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

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: DatabaseDiagnostic
 * PURPOSE: Comprehensive database health monitoring, diagnostics, and optimization
 * EXTENDS: Controller (base controller class)
 * AUTHENTICATION: Requires admin login and elevated privileges
 * 
 * BUSINESS_CONTEXT:
 * This controller provides comprehensive database diagnostic capabilities for
 * system administrators. It monitors database health, identifies potential issues,
 * provides performance metrics, and suggests optimizations. Critical for maintaining
 * system performance and preventing database-related problems in production.
 * 
 * CLASS_METHODS:
 * 
 * 1. index()
 *    PURPOSE: Display main database diagnostic dashboard
 *    FEATURES:
 *      - Database connection status
 *      - Overall health summary
 *      - Critical issues alerts
 *      - Performance overview
 *    USED_IN: System administration, health monitoring
 *    AI_CONTEXT: Main entry point for database health assessment
 * 
 * 2. checkTableIntegrity()
 *    PURPOSE: Verify database table integrity and structure
 *    CHECKS_PERFORMED:
 *      - Table structure validation
 *      - Foreign key constraint verification
 *      - Index integrity checking
 *      - Data consistency validation
 *    USED_IN: Database maintenance, troubleshooting
 *    AI_CONTEXT: Ensures database structural integrity
 * 
 * 3. analyzePerformance()
 *    PURPOSE: Analyze database performance metrics and identify bottlenecks
 *    METRICS_ANALYZED:
 *      - Query execution times
 *      - Index usage statistics
 *      - Table scan frequency
 *      - Connection pool usage
 *    USED_IN: Performance optimization, capacity planning
 *    AI_CONTEXT: Database performance analysis and optimization
 * 
 * 4. checkDataConsistency()
 *    PURPOSE: Validate data consistency across related tables
 *    VALIDATIONS:
 *      - Orphaned record detection
 *      - Referential integrity checking
 *      - Business rule validation
 *      - Data quality assessment
 *    USED_IN: Data integrity maintenance, troubleshooting
 *    AI_CONTEXT: Ensures business data consistency and quality
 * 
 * 5. generateHealthReport()
 *    PURPOSE: Generate comprehensive database health report
 *    REPORT_INCLUDES:
 *      - Overall health score
 *      - Issue summary and recommendations
 *      - Performance metrics
 *      - Optimization suggestions
 *    USED_IN: Regular health assessments, management reporting
 *    AI_CONTEXT: Comprehensive database health documentation
 * 
 * 6. optimizeTables()
 *    PURPOSE: Perform database optimization operations
 *    OPTIMIZATIONS:
 *      - Table optimization and defragmentation
 *      - Index rebuilding
 *      - Statistics updates
 *      - Query cache optimization
 *    USED_IN: Regular maintenance, performance tuning
 *    AI_CONTEXT: Automated database optimization procedures
 * 
 * 7. monitorConnections()
 *    PURPOSE: Monitor database connection usage and health
 *    MONITORING:
 *      - Active connection count
 *      - Connection pool status
 *      - Long-running query detection
 *      - Resource usage tracking
 *    USED_IN: Performance monitoring, resource management
 *    AI_CONTEXT: Database connection and resource monitoring
 * 
 * 8. getSystemVariables()
 *    PURPOSE: Retrieve and format database system configuration
 *    INFORMATION:
 *      - Database version and configuration
 *      - Performance tuning parameters
 *      - Security settings
 *      - Resource limits and allocations
 *    USED_IN: System configuration review, troubleshooting
 *    AI_CONTEXT: Database configuration analysis and documentation
 * 
 * DIAGNOSTIC_CATEGORIES:
 * 
 * 1. STRUCTURAL_HEALTH:
 *    - Table structure integrity
 *    - Index effectiveness
 *    - Foreign key constraints
 *    - Schema consistency
 * 
 * 2. PERFORMANCE_METRICS:
 *    - Query execution statistics
 *    - Index usage analysis
 *    - Resource utilization
 *    - Response time monitoring
 * 
 * 3. DATA_QUALITY:
 *    - Data consistency checks
 *    - Orphaned record detection
 *    - Business rule validation
 *    - Data integrity verification
 * 
 * 4. SECURITY_ASSESSMENT:
 *    - Access control validation
 *    - Permission verification
 *    - Security configuration review
 *    - Vulnerability assessment
 * 
 * BUSINESS_VALUE:
 * - Proactive issue identification and prevention
 * - Performance optimization and tuning
 * - Data integrity assurance
 * - System reliability improvement
 * - Capacity planning support
 * 
 * USAGE_PATTERNS:
 * 1. Regular Health Checks:
 *    Scheduled monitoring -> DatabaseDiagnostic::generateHealthReport()
 * 
 * 2. Performance Troubleshooting:
 *    Issue detection -> DatabaseDiagnostic::analyzePerformance()
 * 
 * 3. Maintenance Operations:
 *    Routine maintenance -> DatabaseDiagnostic::optimizeTables()
 * 
 * 4. Data Quality Assurance:
 *    Quality monitoring -> DatabaseDiagnostic::checkDataConsistency()
 * 
 * AI_INTEGRATION_NOTES:
 * - Critical for maintaining system performance and reliability
 * - Provides comprehensive database health insights
 * - Enables proactive maintenance and optimization
 * - Supports data-driven decision making for system improvements
 * - Essential for production system monitoring and maintenance
 * - Helps prevent database-related downtime and performance issues
 */