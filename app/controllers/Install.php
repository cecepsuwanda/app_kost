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
        $isLoggedIn = $this->isLoggedIn();
        $user = $this->getUser();
        $baseUrl = $this->getBaseUrl();

        $data = [
            'isLoggedIn' => $isLoggedIn,
            'user' => $user,
            'baseUrl' => $baseUrl
        ];
        
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

        $isLoggedIn = $this->isLoggedIn();  
        $user = $this->getUser();
        $baseUrl = $this->getBaseUrl();

        $data = [
            'message' => $message,
            'success' => $success,
            'isLoggedIn' => $isLoggedIn,
            'user' => $user,
            'baseUrl' => $baseUrl
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
        -- Table: users (for authentication)
        DROP TABLE IF EXISTS users;
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nama VARCHAR(100) NOT NULL,
            role ENUM('admin', 'superadmin') DEFAULT 'admin',
            is_active TINYINT(1) DEFAULT 1,
            last_login DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
            ['nomor' => '101', 'harga' => 500000],
            ['nomor' => '102', 'harga' => 500000],
            ['nomor' => '103', 'harga' => 500000],
            ['nomor' => '104', 'harga' => 500000],
            ['nomor' => '105', 'harga' => 500000],
            ['nomor' => '106', 'harga' => 500000],
            ['nomor' => '107', 'harga' => 500000],
            ['nomor' => '108', 'harga' => 500000],
            ['nomor' => '109', 'harga' => 500000],
            ['nomor' => '110', 'harga' => 500000],
            ['nomor' => '111', 'harga' => 500000],
            ['nomor' => '112', 'harga' => 500000],
            ['nomor' => '113', 'harga' => 500000],
            ['nomor' => '114', 'harga' => 500000],
            ['nomor' => '115', 'harga' => 500000],
            ['nomor' => '116', 'harga' => 500000],
            ['nomor' => '217', 'harga' => 500000],
            ['nomor' => '218', 'harga' => 500000],
            ['nomor' => '219', 'harga' => 500000],
            ['nomor' => '220', 'harga' => 500000],
            ['nomor' => '221', 'harga' => 500000],
            ['nomor' => '222', 'harga' => 500000],
            ['nomor' => '223', 'harga' => 500000],
            ['nomor' => '224', 'harga' => 500000],
            ['nomor' => '225', 'harga' => 500000],
            ['nomor' => '226', 'harga' => 500000],
            ['nomor' => '227', 'harga' => 500000],
            ['nomor' => '228', 'harga' => 500000],
            ['nomor' => '229', 'harga' => 500000],
            ['nomor' => '230', 'harga' => 500000],
            ['nomor' => '231', 'harga' => 500000],
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
        $penghuniData = [
            [
                'nama' => 'Ahmad Santoso',
                'no_ktp' => '1234567890123456',
                'no_hp' => '081234567890',
                'tgl_masuk' => '2025-07-15'
            ],
            [
                'nama' => 'Siti Aminah',
                'no_ktp' => '2345678901234567',
                'no_hp' => '081234567891',
                'tgl_masuk' => '2025-07-01'
            ],
            [
                'nama' => 'Budi Prakoso',
                'no_ktp' => null,
                'no_hp' => null,
                'tgl_masuk' => '2025-07-10'
            ]
        ];

        foreach ($penghuniData as $penghuni) {
            $this->db->insert('tb_penghuni', $penghuni);
        }

        // Insert sample kamar penghuni
        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 1,
            'tgl_masuk' => '2025-07-15'
        ]);

        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 2,
            'tgl_masuk' => '2025-07-01'
        ]);

        // Insert sample detail kamar penghuni
        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 1,
            'id_penghuni' => 1,
            'tgl_masuk' => '2025-07-15'
        ]);

        $this->db->insert('tb_detail_kmr_penghuni', [
            'id_kmr_penghuni' => 2,
            'id_penghuni' => 2,
            'tgl_masuk' => '2025-07-01'
        ]);

        // Insert sample barang bawaan
        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 1,
            'id_barang' => 1 // Kulkas
        ]);

        $this->db->insert('tb_brng_bawaan', [
            'id_penghuni' => 2,
            'id_barang' => 2 // AC
        ]);
    }
}