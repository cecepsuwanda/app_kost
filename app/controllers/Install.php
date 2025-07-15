<?php

class Install extends Controller
{
    public function index()
    {
        $this->loadView('install/index');
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
            
        } catch (Exception $e) {
            $message = "Installation failed: " . $e->getMessage();
            $success = false;
        }
        
        $this->loadView('install/result', compact('message', 'success'));
    }

    private function createDatabase()
    {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    }

    private function createTables()
    {
        $sql = "
        -- Table: tb_penghuni
        DROP TABLE IF EXISTS tb_brng_bawaan;
        DROP TABLE IF EXISTS tb_bayar;
        DROP TABLE IF EXISTS tb_tagihan;
        DROP TABLE IF EXISTS tb_kmr_penghuni;        
        DROP TABLE IF EXISTS tb_penghuni;
        CREATE TABLE tb_penghuni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(255) NOT NULL,
            no_ktp VARCHAR(20) UNIQUE NOT NULL,
            no_hp VARCHAR(15) NOT NULL,
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
            id_penghuni INT NOT NULL,
            tgl_masuk DATE NOT NULL,
            tgl_keluar DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_kamar) REFERENCES tb_kamar(id) ON DELETE CASCADE,
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
        // Insert sample kamar
        $kamarData = [
            ['nomor' => '101', 'harga' => 500000],
            ['nomor' => '102', 'harga' => 550000],
            ['nomor' => '103', 'harga' => 600000],
            ['nomor' => '201', 'harga' => 650000],
            ['nomor' => '202', 'harga' => 700000],
        ];

        foreach ($kamarData as $kamar) {
            $this->db->insert('tb_kamar', $kamar);
        }

        // Insert sample barang
        $barangData = [
            ['nama' => 'Kulkas', 'harga' => 50000],
            ['nama' => 'AC', 'harga' => 100000],
            ['nama' => 'TV', 'harga' => 75000],
            ['nama' => 'Mesin Cuci', 'harga' => 60000],
            ['nama' => 'Kompor Gas', 'harga' => 25000],
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
                'tgl_masuk' => '2024-01-15'
            ],
            [
                'nama' => 'Siti Aminah',
                'no_ktp' => '2345678901234567',
                'no_hp' => '081234567891',
                'tgl_masuk' => '2024-02-01'
            ]
        ];

        foreach ($penghuniData as $penghuni) {
            $this->db->insert('tb_penghuni', $penghuni);
        }

        // Insert sample kamar penghuni
        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 1,
            'id_penghuni' => 1,
            'tgl_masuk' => '2024-01-15'
        ]);

        $this->db->insert('tb_kmr_penghuni', [
            'id_kamar' => 2,
            'id_penghuni' => 2,
            'tgl_masuk' => '2024-02-01'
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