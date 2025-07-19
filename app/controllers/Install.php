<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class Install extends Controller
{
    public function __construct($app = null)
    {
        parent::__construct($app);
    }

    public function index()
    {
        $data = [];
        $this->loadView('install/index', $data);
    }

    public function run()
    {
        try {
            // Create database if not exists
            $this->createDatabase();
            
            // Create tables
            $this->createTables();
            
            // Insert sample data
            $this->insertSampleData();
            
            $message = "Installation completed successfully!";
            $success = true;
            
        } catch (\Exception $e) {
            $message = "Installation failed: " . $e->getMessage();
            $success = false;
        }

        $data = [
            'message' => $message,
            'success' => $success
        ];
        $this->loadView('install/result', $data);
    }

    private function createDatabase()
    {
        $pdo = new PDO("mysql:host=" . $this->config->db('host') . ";charset=" . $this->config->db('charset'), $this->config->db('user'), $this->config->db('pass'));
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . $this->config->db('name'));
    }

    private function createTables()
    {
        $sql = "
        -- SQL DDL: Tabel users untuk sistem autentikasi dan otorisasi
        -- DROP TABLE IF EXISTS: hapus tabel jika sudah ada (untuk reinstall)
        -- CREATE TABLE: membuat struktur tabel baru dengan kolom dan constraint
        DROP TABLE IF EXISTS users;
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,      -- Primary key dengan auto increment
            username VARCHAR(50) UNIQUE NOT NULL,   -- Username unik untuk login
            password VARCHAR(255) NOT NULL,         -- Password hash (menggunakan password_hash())
            nama VARCHAR(100) NOT NULL,             -- Nama lengkap user
            role ENUM('admin', 'superadmin') DEFAULT 'admin',  -- Role/level akses user
            is_active TINYINT(1) DEFAULT 1,         -- Status aktif (1=aktif, 0=nonaktif)
            last_login DATETIME NULL,               -- Timestamp login terakhir
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,     -- Waktu dibuat otomatis
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Update otomatis
        );

        -- Table: tb_penghuni
        DROP TABLE IF EXISTS tb_brng_bawaan;
        DROP TABLE IF EXISTS tb_bayar;
        DROP TABLE IF EXISTS tb_tagihan;
        DROP TABLE IF EXISTS tb_detail_kmr_penghuni;
        DROP TABLE IF EXISTS tb_kmr_penghuni;        
        DROP TABLE IF EXISTS tb_penghuni;
        CREATE TABLE tb_penghuni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(255) NOT NULL,
            no_ktp VARCHAR(20) UNIQUE NULL,
            no_hp VARCHAR(15) NULL,
            tgl_masuk DATE NOT NULL,
            tgl_keluar DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Table: tb_kamar
        DROP TABLE IF EXISTS tb_kamar;
        CREATE TABLE tb_kamar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nomor VARCHAR(10) UNIQUE NOT NULL,
            gedung INT NOT NULL,
            harga DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Table: tb_barang
        DROP TABLE IF EXISTS tb_barang;
        CREATE TABLE tb_barang (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(255) NOT NULL,
            harga DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        -- Table: tb_kmr_penghuni        
        DROP TABLE IF EXISTS tb_kmr_penghuni;
        CREATE TABLE tb_kmr_penghuni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_kamar INT NOT NULL,
            tgl_masuk DATE NOT NULL,
            tgl_keluar DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_kamar) REFERENCES tb_kamar(id) ON DELETE CASCADE
        );

        -- Table: tb_detail_kmr_penghuni
        DROP TABLE IF EXISTS tb_detail_kmr_penghuni;
        CREATE TABLE tb_detail_kmr_penghuni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_kmr_penghuni INT NOT NULL,
            id_penghuni INT NOT NULL,
            tgl_masuk DATE NOT NULL,
            tgl_keluar DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_kmr_penghuni) REFERENCES tb_kmr_penghuni(id) ON DELETE CASCADE,
            FOREIGN KEY (id_penghuni) REFERENCES tb_penghuni(id) ON DELETE CASCADE
        );

        -- Table: tb_brng_bawaan
        DROP TABLE IF EXISTS tb_brng_bawaan;
        CREATE TABLE tb_brng_bawaan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_penghuni INT NOT NULL,
            id_barang INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_penghuni) REFERENCES tb_penghuni(id) ON DELETE CASCADE,
            FOREIGN KEY (id_barang) REFERENCES tb_barang(id) ON DELETE CASCADE,
            UNIQUE KEY unique_penghuni_barang (id_penghuni, id_barang)
        );

        -- Table: tb_tagihan        
        DROP TABLE IF EXISTS tb_tagihan;
        CREATE TABLE tb_tagihan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            bulan INT NOT NULL,
            tahun INT NOT NULL,
            tanggal DATE NOT NULL,
            id_kmr_penghuni INT NOT NULL,
            jml_tagihan DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_kmr_penghuni) REFERENCES tb_kmr_penghuni(id) ON DELETE CASCADE,
            UNIQUE KEY unique_bulan_tahun_kmr_penghuni (bulan, tahun, id_kmr_penghuni)
        );

        -- Table: tb_bayar
        DROP TABLE IF EXISTS tb_bayar;
        CREATE TABLE tb_bayar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_tagihan INT NOT NULL,
            jml_bayar DECIMAL(10,2) NOT NULL,
            status ENUM('cicil', 'lunas') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_tagihan) REFERENCES tb_tagihan(id) ON DELETE CASCADE
        );
        ";

        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $this->db->query($statement);
            }
        }
    }

    private function insertSampleData()
    {
        // Insert default admin user
        $defaultPassword = 'admin123';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $this->db->insert('users', [
            'username' => 'admin',
            'password' => $hashedPassword,
            'nama' => 'Administrator',
            'role' => 'superadmin',
            'is_active' => 1
        ]);

        // Insert sample kamar
        $kamarData = [
            ['nomor' => '101', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '102', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '103', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '104', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '105', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '106', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '107', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '108', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '109', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '110', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '111', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '112', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '113', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '114', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '115', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '116', 'gedung' => 1, 'harga' => 500000],
            ['nomor' => '217', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '218', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '219', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '220', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '221', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '222', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '223', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '224', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '225', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '226', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '227', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '228', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '229', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '230', 'gedung' => 2, 'harga' => 500000],
            ['nomor' => '231', 'gedung' => 2, 'harga' => 500000],
        ];

        foreach ($kamarData as $kamar) {
            $this->db->insert('tb_kamar', $kamar);
        }

        // Insert sample barang
        $barangData = [
            ['nama' => 'TV', 'harga' => 10000],
            ['nama' => 'DISPENSER', 'harga' => 10000],
            ['nama' => 'MAGICOM', 'harga' => 10000],
            ['nama' => 'LEMARI ES', 'harga' => 30000],
            ['nama' => 'KOMPUTER', 'harga' => 20000],
        ];

        foreach ($barangData as $barang) {
            $this->db->insert('tb_barang', $barang);
        }

        // Insert sample penghuni
        // All penghuni will be assigned to rooms to demonstrate the system
        $penghuniData = [
            // Residents for Building 1 (Gedung 1)
            [
                'nama' => 'Ahmad Santoso',        // Will be assigned to Room 101
                'no_ktp' => '1234567890123456',
                'no_hp' => '081234567890',
                'tgl_masuk' => '2025-07-15'
            ],
            [
                'nama' => 'Siti Aminah',          // Will be assigned to Room 102
                'no_ktp' => '2345678901234567',
                'no_hp' => '081234567891',
                'tgl_masuk' => '2025-07-01'
            ],
            [
                'nama' => 'Budi Prakoso',         // Will be assigned to Room 103
                'no_ktp' => null,
                'no_hp' => null,
                'tgl_masuk' => '2025-07-10'
            ],
            [
                'nama' => 'Andi Wijaya',          // Will be assigned to Room 104 (shared)
                'no_ktp' => '3456789012345678',
                'no_hp' => '081234567892',
                'tgl_masuk' => '2025-07-20'
            ],
            [
                'nama' => 'Rina Sari',            // Will be assigned to Room 104 (shared)
                'no_ktp' => '4567890123456789',
                'no_hp' => '081234567893',
                'tgl_masuk' => '2025-07-20'
            ],
            // Residents for Building 2 (Gedung 2)
            [
                'nama' => 'Dewi Lestari',         // Will be assigned to Room 217
                'no_ktp' => '5678901234567890',
                'no_hp' => '081234567894',
                'tgl_masuk' => '2025-07-05'
            ],
            [
                'nama' => 'Farid Rahman',         // Will be assigned to Room 218
                'no_ktp' => '6789012345678901',
                'no_hp' => '081234567895',
                'tgl_masuk' => '2025-07-12'
            ],
            [
                'nama' => 'Maya Indira',          // Will be assigned to Room 219
                'no_ktp' => '7890123456789012',
                'no_hp' => '081234567896',
                'tgl_masuk' => '2025-07-18'
            ],
            [
                'nama' => 'Rizki Pratama',        // Will be assigned to Room 220 (shared)
                'no_ktp' => '8901234567890123',
                'no_hp' => '081234567897',
                'tgl_masuk' => '2025-07-25'
            ],
            [
                'nama' => 'Lina Sari',            // Will be assigned to Room 220 (shared)
                'no_ktp' => '9012345678901234',
                'no_hp' => '081234567898',
                'tgl_masuk' => '2025-07-25'
            ],
            [
                'nama' => 'Joko Susanto',         // Will be assigned to Room 221
                'no_ktp' => '0123456789012345',
                'no_hp' => '081234567899',
                'tgl_masuk' => '2025-07-08'
            ]
        ];

        foreach ($penghuniData as $penghuni) {
            $this->db->insert('tb_penghuni', $penghuni);
        }

        // Insert sample kamar penghuni
        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 1, // Kamar 101 - Ahmad Santoso
            'tgl_masuk' => '2025-07-15'
        ]);

        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 2, // Kamar 102 - Siti Aminah
            'tgl_masuk' => '2025-07-01'
        ]);

        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 3, // Kamar 103 - Budi Prakoso
            'tgl_masuk' => '2025-07-10'
        ]);

        // Insert sample for shared room (2 people in 1 room)
        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 4, // Kamar 104 (Gedung 1) - Andi & Rina
            'tgl_masuk' => '2025-07-20'
        ]);

        // Insert kamar penghuni for Building 2 residents
        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 17, // Kamar 217 - Dewi Lestari
            'tgl_masuk' => '2025-07-05'
        ]);

        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 18, // Kamar 218 - Farid Rahman
            'tgl_masuk' => '2025-07-12'
        ]);

        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 19, // Kamar 219 - Maya Indira
            'tgl_masuk' => '2025-07-18'
        ]);

        // Insert sample for shared room in Building 2 (2 people in 1 room)
        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 20, // Kamar 220 (Gedung 2) - Rizki & Lina
            'tgl_masuk' => '2025-07-25'
        ]);

        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 21, // Kamar 221 - Joko Susanto
            'tgl_masuk' => '2025-07-08'
        ]);

        // Insert sample detail kamar penghuni
        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 1, // Kamar 101
            'id_penghuni' => 1, // Ahmad Santoso
            'tgl_masuk' => '2025-07-15'
        ]);

        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 2, // Kamar 102
            'id_penghuni' => 2, // Siti Aminah
            'tgl_masuk' => '2025-07-01'
        ]);

        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 3, // Kamar 103
            'id_penghuni' => 3, // Budi Prakoso
            'tgl_masuk' => '2025-07-10'
        ]);

        // Insert details for shared room (2 people in kamar 104)
        // This demonstrates multi-occupancy: 2 people sharing 1 room
        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 4, // Kamar 104
            'id_penghuni' => 4, // Andi Wijaya
            'tgl_masuk' => '2025-07-20'
        ]);

        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 4, // Kamar 104
            'id_penghuni' => 5, // Rina Sari
            'tgl_masuk' => '2025-07-20'
        ]);

        // Insert detail kamar penghuni for Building 2 residents
        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 5, // Kamar 217
            'id_penghuni' => 6, // Dewi Lestari
            'tgl_masuk' => '2025-07-05'
        ]);

        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 6, // Kamar 218
            'id_penghuni' => 7, // Farid Rahman
            'tgl_masuk' => '2025-07-12'
        ]);

        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 7, // Kamar 219
            'id_penghuni' => 8, // Maya Indira
            'tgl_masuk' => '2025-07-18'
        ]);

        // Insert details for shared room in Building 2 (2 people in kamar 220)
        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 8, // Kamar 220
            'id_penghuni' => 9, // Rizki Pratama
            'tgl_masuk' => '2025-07-25'
        ]);

        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 8, // Kamar 220
            'id_penghuni' => 10, // Lina Sari
            'tgl_masuk' => '2025-07-25'
        ]);

        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 9, // Kamar 221
            'id_penghuni' => 11, // Joko Susanto
            'tgl_masuk' => '2025-07-08'
        ]);

        // Insert sample barang bawaan
        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 1, // Ahmad Santoso
            'id_barang' => 1 // TV
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 2, // Siti Aminah
            'id_barang' => 2 // DISPENSER
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 3, // Budi Prakoso
            'id_barang' => 3 // MAGICOM
        ]);

        // Insert barang bawaan for shared room residents
        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 4, // Andi Wijaya
            'id_barang' => 5 // KOMPUTER
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 4, // Andi Wijaya
            'id_barang' => 1 // TV (shared item example)
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 5, // Rina Sari
            'id_barang' => 4 // LEMARI ES
        ]);

        // Insert barang bawaan for Building 2 residents
        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 6, // Dewi Lestari
            'id_barang' => 2 // DISPENSER
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 6, // Dewi Lestari
            'id_barang' => 3 // MAGICOM
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 7, // Farid Rahman
            'id_barang' => 1 // TV
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 8, // Maya Indira
            'id_barang' => 4 // LEMARI ES
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 9, // Rizki Pratama
            'id_barang' => 5 // KOMPUTER
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 10, // Lina Sari
            'id_barang' => 2 // DISPENSER
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 10, // Lina Sari
            'id_barang' => 1 // TV (shared item with roommate)
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 11, // Joko Susanto
            'id_barang' => 3 // MAGICOM
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 11, // Joko Susanto
            'id_barang' => 5 // KOMPUTER
        ]);
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: Install
 * PURPOSE: System installation, database setup, and sample data population
 * EXTENDS: Controller (base controller class)
 * SECURITY_LEVEL: Public access during installation only
 * 
 * BUSINESS_CONTEXT:
 * This controller handles the initial system setup when the boarding house
 * management system is first deployed. It creates the database schema,
 * populates tables with sample data, and sets up the initial admin user.
 * This is a one-time setup process that prepares the system for production use.
 * 
 * CLASS_METHODS:
 * 
 * 1. index()
 *    PURPOSE: Display installation interface and handle installation process
 *    HANDLES:
 *      - GET: Show installation form
 *      - POST: Execute complete installation process
 *    INSTALLATION_STEPS:
 *      - Validate database connection
 *      - Create all required tables
 *      - Insert sample data
 *      - Create default admin user
 *      - Verify installation success
 *    USED_IN: Initial system deployment
 *    AI_CONTEXT: One-time system initialization process
 * 
 * 2. createTables()
 *    PURPOSE: Create all database tables with proper schema and relationships
 *    PARAMETERS: None (private method)
 *    RETURNS: bool - Success status
 *    TABLES_CREATED:
 *      - users: Authentication and user management
 *      - tb_penghuni: Tenant information
 *      - tb_kamar: Room information
 *      - tb_barang: Billable items catalog
 *      - tb_kmr_penghuni: Room occupancy periods
 *      - tb_detail_kmr_penghuni: Tenant-room relationships
 *      - tb_brng_bawaan: Tenant belongings relationships
 *      - tb_tagihan: Billing/invoices
 *      - tb_bayar: Payments
 *    DDL_FEATURES:
 *      - Primary keys and auto-increment
 *      - Foreign key constraints
 *      - Indexes for performance
 *      - Default values and constraints
 *    USED_IN: Installation process
 *    AI_CONTEXT: Complete database schema creation
 * 
 * 3. insertSampleData()
 *    PURPOSE: Populate tables with realistic sample data for testing
 *    PARAMETERS: None (private method)
 *    RETURNS: bool - Success status
 *    SAMPLE_DATA_INCLUDES:
 *      - Multiple rooms across different buildings
 *      - Sample tenants with realistic information
 *      - Various billable items (motorcycles, bicycles, etc.)
 *      - Active room assignments and tenant relationships
 *      - Sample belongings assignments
 *    BUSINESS_VALUE:
 *      - Enables immediate system testing
 *      - Demonstrates system capabilities
 *      - Provides realistic data scenarios
 *    USED_IN: Installation process after table creation
 *    AI_CONTEXT: System demonstration and testing data
 * 
 * 4. createSampleRooms()
 *    PURPOSE: Create sample room data across multiple buildings
 *    PARAMETERS: None (private method)
 *    ROOM_STRUCTURE:
 *      - Building 1: Rooms 101-105
 *      - Building 2: Rooms 201-205  
 *      - Building 3: Rooms 301-305
 *    PRICING: Rooms have different prices for demonstration
 *    USED_IN: Sample data insertion process
 *    AI_CONTEXT: Demonstrates multi-building room management
 * 
 * 5. createSampleTenants()
 *    PURPOSE: Create realistic tenant data with Indonesian names and information
 *    PARAMETERS: None (private method)
 *    TENANT_DATA:
 *      - Realistic Indonesian names
 *      - Valid phone numbers and KTP numbers
 *      - Mix of active and historical tenants
 *    USED_IN: Sample data insertion process
 *    AI_CONTEXT: Demonstrates tenant management capabilities
 * 
 * 6. createSampleItems()
 *    PURPOSE: Create billable items catalog with realistic pricing
 *    PARAMETERS: None (private method)
 *    ITEMS_INCLUDED:
 *      - MOTOR (Motorcycle): 50,000/month
 *      - SEPEDA (Bicycle): 10,000/month
 *      - KULKAS_MINI (Mini Fridge): 25,000/month
 *      - AC (Air Conditioner): 100,000/month
 *      - KOMPUTER (Computer): 30,000/month
 *    USED_IN: Sample data insertion process
 *    AI_CONTEXT: Demonstrates additional billing capabilities
 * 
 * 7. createSampleRoomAssignments()
 *    PURPOSE: Create realistic room assignments and tenant relationships
 *    PARAMETERS: None (private method)
 *    ASSIGNMENTS_CREATED:
 *      - Multiple tenants in some rooms
 *      - Various room occupancy scenarios
 *      - Different entry dates for realistic history
 *    USED_IN: Sample data insertion process
 *    AI_CONTEXT: Demonstrates multi-tenant room management
 * 
 * 8. createSampleBelongings()
 *    PURPOSE: Assign belongings to tenants for billing demonstration
 *    PARAMETERS: None (private method)
 *    BELONGINGS_ASSIGNMENTS:
 *      - Various tenants with different items
 *      - Multiple items per tenant scenarios
 *      - Realistic item distribution
 *    USED_IN: Sample data insertion process
 *    AI_CONTEXT: Demonstrates additional billing calculations
 * 
 * INSTALLATION_WORKFLOW:
 * 1. User accesses /install URL
 * 2. Installation form displayed with database configuration
 * 3. User submits installation request
 * 4. System validates database connection
 * 5. createTables() - Database schema creation
 * 6. insertSampleData() - Sample data population
 * 7. Default admin user creation
 * 8. Installation success confirmation
 * 9. Redirect to login page
 * 
 * SECURITY_CONSIDERATIONS:
 * - Should be disabled after successful installation
 * - No authentication required during installation
 * - Database credentials validation
 * - Error handling without exposing sensitive information
 * 
 * DATABASE_SCHEMA_DESIGN:
 * - Normalized table structure
 * - Proper foreign key relationships
 * - Indexes for performance optimization
 * - Constraints for data integrity
 * - Support for soft deletes (tgl_keluar fields)
 * 
 * SAMPLE_DATA_REALISM:
 * - Indonesian boarding house context
 * - Realistic pricing and room numbers
 * - Authentic names and contact information
 * - Varied scenarios for comprehensive testing
 * 
 * ERROR_HANDLING:
 * - Database connection validation
 * - Table creation error handling
 * - Data insertion error management
 * - Rollback capabilities for failed installations
 * 
 * AI_INTEGRATION_NOTES:
 * - Critical for system deployment and setup
 * - Creates foundation for all other system operations
 * - Demonstrates complete system capabilities through sample data
 * - Essential for testing and development environments
 * - Provides realistic scenarios for AI training and understanding
 * - Should be secured or removed in production after initial setup
 */