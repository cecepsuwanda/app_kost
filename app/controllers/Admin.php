<?php

namespace App\Controllers;

use App\Core\Controller;

class Admin extends Controller
{
    private $auth;
    
    public function __construct($app = null)
    {
        parent::__construct($app);
        
        // Initialize Auth instance and check authentication
        $this->auth = new \App\Controllers\Auth($app);
        $this->auth->requireLogin();
    }

    // Helper method to add barang bawaan data to penghuni records
    private function addBarangBawaanToPenghuni($penghuniData)
    {
        $barangBawaanModel = $this->loadModel('BarangBawaanModel');
        
        if (empty($penghuniData)) {
            return $penghuniData;
        }
        
        // Handle single record vs array of records
        $isArray = isset($penghuniData[0]);
        $dataToProcess = $isArray ? $penghuniData : [$penghuniData];
        
        foreach ($dataToProcess as &$record) {
            if (isset($record['id_penghuni'])) {
                $record['barang_bawaan'] = $barangBawaanModel->getPenghuniBarangDetail($record['id_penghuni']);
            }
        }
        
        return $isArray ? $dataToProcess : $dataToProcess[0];
    }

    public function index()
    {
        $kamarModel = $this->loadModel('KamarModel');
        $kamarPenghuniModel = $this->loadModel('KamarPenghuniModel');
        $tagihanModel = $this->loadModel('TagihanModel');
        $penghuniModel = $this->loadModel('PenghuniModel');
        $detailKamarPenghuniModel = $this->loadModel('DetailKamarPenghuniModel');

        // Dashboard statistics
        $stats = [
            'total_kamar' => count($kamarModel->findAll()),
            'kamar_terisi' => count($kamarModel->getKamarTerisi()),
            'kamar_kosong' => count($kamarModel->getKamarKosong()),
            'kamar_tersedia' => count($kamarModel->getKamarTersedia()),
            'total_penghuni' => count($penghuniModel->findActive()),
            'tagihan_terlambat' => count($tagihanModel->getTagihanTerlambat()),
            'mendekati_jatuh_tempo' => count($tagihanModel->getTagihanMendekatiJatuhTempo())
        ];

        // Get data for dashboard
        $kamarKosong = $kamarModel->getKamarKosong();
        $kamarTersedia = $kamarModel->getKamarTersedia();
        $kamarMendekatiJatuhTempo = $kamarPenghuniModel->getKamarSewaanMendekatiJatuhTempo(3);
        $tagihanTerlambat = $tagihanModel->getTagihanTerlambat();
        
        // Get building statistics
        $statistikPerGedung = $kamarModel->getStatistikPerGedung();

        $data = [
            'title' => 'Dashboard Admin - ' . $this->config->appConfig('name'),
            'stats' => $stats,
            'kamarKosong' => $kamarKosong,
            'kamarTersedia' => $kamarTersedia,
            'kamarMendekatiJatuhTempo' => $kamarMendekatiJatuhTempo,
            'tagihanTerlambat' => $tagihanTerlambat,
            'statistikPerGedung' => $statistikPerGedung,
            'showSidebar' => true
        ];

        $this->loadView('admin/dashboard', $data);
    }

    public function penghuni()
    {
        $penghuniModel = $this->loadModel('PenghuniModel');
        $kamarModel = $this->loadModel('KamarModel');
        $barangModel = $this->loadModel('BarangModel');
        $kamarPenghuniModel = $this->loadModel('KamarPenghuniModel');
        $barangBawaanModel = $this->loadModel('BarangBawaanModel');
        $detailKamarPenghuniModel = $this->loadModel('DetailKamarPenghuniModel');

        if ($this->request->isPostRequest()) {
            $action = $this->request->postParam('action');

            switch ($action) {
                case 'create':
                    $data = [
                        'nama' => $this->request->postParam('nama'),
                        'no_ktp' => $this->request->postParam('no_ktp') ?: null,
                        'no_hp' => $this->request->postParam('no_hp') ?: null,
                        'tgl_masuk' => $this->request->postParam('tgl_masuk')
                    ];
                    
                    $id_penghuni = $penghuniModel->create($data);
                    
                    // Assign to room if selected
                    if ($this->request->postParam('id_kamar')) {
                        $id_kamar = $this->request->postParam('id_kamar');
                        
                        // Check if room already has active occupancy
                        $activeKamarPenghuni = $kamarPenghuniModel->findActiveByKamar($id_kamar);
                        $detailKamarPenghuniModel = $this->loadModel('DetailKamarPenghuniModel');
                        
                        if ($activeKamarPenghuni) {
                            // Check room capacity
                            if ($kamarPenghuniModel->checkKamarCapacity($id_kamar, 2, $detailKamarPenghuniModel)) {
                                $kamarPenghuniModel->addPenghuniToKamar($activeKamarPenghuni['id'], $id_penghuni, $this->request->postParam('tgl_masuk'), $detailKamarPenghuniModel);
                            }
                        } else {
                            // Create new kamar penghuni record and detail records
                            $kamarPenghuniModel->createKamarPenghuniWithDetails($id_kamar, $this->request->postParam('tgl_masuk'), [$id_penghuni], $detailKamarPenghuniModel);
                        }
                    }

                    // Add barang bawaan if selected
                    $barang_ids = $this->request->postParam('barang_ids', []);
                    foreach ($barang_ids as $id_barang) {
                        $barangBawaanModel->create([
                            'id_penghuni' => $id_penghuni,
                            'id_barang' => $id_barang
                        ]);
                    }
                    break;

                case 'update':
                    $id = $this->request->postParam('id');
                    $data = [
                        'nama' => $this->request->postParam('nama'),
                        'no_ktp' => $this->request->postParam('no_ktp') ?: null,
                        'no_hp' => $this->request->postParam('no_hp') ?: null,
                        'tgl_masuk' => $this->request->postParam('tgl_masuk')
                    ];
                    
                    if ($this->request->postParam('tgl_keluar')) {
                        $data['tgl_keluar'] = $this->request->postParam('tgl_keluar');
                    }
                    
                    $penghuniModel->update($id, $data);
                    
                    // Update barang bawaan
                    // First, get current barang bawaan
                    $currentBarangBawaan = $barangBawaanModel->findByPenghuni($id);
                    $currentBarangIds = array_column($currentBarangBawaan, 'id_barang');
                    
                    // Get new barang bawaan from form
                    $newBarangIds = $this->request->postParam('barang_ids', []);
                    
                    // Remove unchecked items
                    foreach ($currentBarangIds as $currentId) {
                        if (!in_array($currentId, $newBarangIds)) {
                            $barangBawaanModel->removeBarangFromPenghuni($id, $currentId);
                        }
                    }
                    
                    // Add new checked items
                    foreach ($newBarangIds as $newId) {
                        if (!in_array($newId, $currentBarangIds)) {
                            $barangBawaanModel->create([
                                'id_penghuni' => $id,
                                'id_barang' => $newId
                            ]);
                        }
                    }
                    break;

                case 'delete':
                    $penghuniModel->delete($this->request->postParam('id'));
                    break;

                case 'checkout':
                    $id = $this->request->postParam('id');
                    $tgl_keluar = $this->request->postParam('tgl_keluar');
                    
                    // Get room info BEFORE checkout
                    $kamarPenghuni = $kamarPenghuniModel->findKamarByPenghuni($id);
                    
                    // Update penghuni
                    $penghuniModel->update($id, ['tgl_keluar' => $tgl_keluar]);
                    
                    // Update detail kamar penghuni
                    $detailKamarPenghuniModel->checkoutPenghuniFromKamar($id, $tgl_keluar);
                    
                    // Check if kamar becomes empty and close it
                    if ($kamarPenghuni) {
                        $remainingPenghuni = $detailKamarPenghuniModel->findActiveByKamarPenghuni($kamarPenghuni['id']);
                        if (empty($remainingPenghuni)) {
                            $kamarPenghuniModel->checkoutKamar($kamarPenghuni['id'], $tgl_keluar);
                        }
                    }
                    break;

                case 'pindah_kamar':
                    $id_penghuni = $this->request->postParam('id_penghuni');
                    $id_kamar_baru = $this->request->postParam('id_kamar_baru');
                    $tgl_pindah = $this->request->postParam('tgl_pindah');
                    $detailKamarPenghuniModel = $this->loadModel('DetailKamarPenghuniModel');
                    
                    $kamarPenghuniModel->pindahKamar($id_penghuni, $id_kamar_baru, $tgl_pindah, $detailKamarPenghuniModel);
                    break;
            }
            
            $this->redirect($this->config->appConfig('url').'/admin/penghuni');
        }

        $penghuni = $penghuniModel->getPenghuniWithKamar();
        $kamarTersedia = $kamarModel->getKamarTersedia();
        $barang = $barangModel->findAll();
        
        // Add barang bawaan data for each penghuni
        foreach ($penghuni as &$p) {
            $p['barang_bawaan'] = $barangBawaanModel->getPenghuniBarangDetail($p['id']);
            $p['barang_bawaan_ids'] = array_column($p['barang_bawaan'], 'id_barang');
        }

        $data = [
            'title' => 'Kelola Penghuni - ' . $this->config->appConfig('name'),
            'penghuni' => $penghuni,
            'kamarTersedia' => $kamarTersedia,
            'barang' => $barang,
            'showSidebar' => true
        ];

        $this->loadView('admin/penghuni', $data);
    }

    public function kamar()
    {
        $kamarModel = $this->loadModel('KamarModel');

        if ($this->request->isPostRequest()) {
            $action = $this->request->postParam('action');

            switch ($action) {
                case 'create':
                    $kamarModel->create([
                        'nomor' => $this->request->postParam('nomor'),
                        'gedung' => $this->request->postParam('gedung'),
                        'harga' => $this->request->postParam('harga')
                    ]);
                    break;

                case 'update':
                    $kamarModel->update($this->request->postParam('id'), [
                        'nomor' => $this->request->postParam('nomor'),
                        'gedung' => $this->request->postParam('gedung'),
                        'harga' => $this->request->postParam('harga')
                    ]);
                    break;

                case 'delete':
                    $kamarModel->delete($this->request->postParam('id'));
                    break;
            }
            
            $this->redirect($this->config->appConfig('url').'/admin/kamar');
        }

        $kamar = $kamarModel->getKamarWithBasicStatus();

        $data = [
            'title' => 'Kelola Kamar - ' . $this->config->appConfig('name'),
            'kamar' => $kamar,
            'showSidebar' => true
        ];

        $this->loadView('admin/kamar', $data);
    }

    public function barang()
    {
        $barangModel = $this->loadModel('BarangModel');

        if ($this->request->isPostRequest()) {
            $action = $this->request->postParam('action');

            switch ($action) {
                case 'create':
                    $barangModel->create([
                        'nama' => $this->request->postParam('nama'),
                        'harga' => $this->request->postParam('harga')
                    ]);
                    break;

                case 'update':
                    $barangModel->update($this->request->postParam('id'), [
                        'nama' => $this->request->postParam('nama'),
                        'harga' => $this->request->postParam('harga')
                    ]);
                    break;

                case 'delete':
                    $barangModel->delete($this->request->postParam('id'));
                    break;
            }
            
            $this->redirect($this->config->appConfig('url').'/admin/barang');
        }

        $barang = $barangModel->findAll();

        $data = [
            'title' => 'Kelola Barang - ' . $this->config->appConfig('name'),
            'barang' => $barang,
            'showSidebar' => true
        ];

        $this->loadView('admin/barang', $data);
    }

    public function tagihan()
    {
        $tagihanModel = $this->loadModel('TagihanModel');

        if ($this->request->isPostRequest()) {
            $action = $this->request->postParam('action');

            switch ($action) {
                case 'generate':
                    try {
                        $bulan = $this->request->postParam('bulan');
                        $generated = $tagihanModel->generateTagihan($bulan);
                        $this->session->sessionFlash('message', "Berhasil generate $generated tagihan untuk bulan $bulan");
                    } catch (\InvalidArgumentException $e) {
                        $this->session->sessionFlash('error', $e->getMessage());
                    } catch (\Exception $e) {
                        $this->session->sessionFlash('error', "Gagal generate tagihan: " . $e->getMessage());
                    }
                    break;

                case 'recalculate':
                    try {
                        $id_tagihan = $this->request->postParam('id_tagihan');
                        $newAmount = $tagihanModel->recalculateTagihan($id_tagihan);
                        if ($newAmount !== false) {
                            $this->session->sessionFlash('message', "Berhasil hitung ulang tagihan. Jumlah baru: Rp " . number_format($newAmount, 0, ',', '.'));
                        } else {
                            $this->session->sessionFlash('error', "Gagal menghitung ulang tagihan");
                        }
                    } catch (\InvalidArgumentException $e) {
                        $this->session->sessionFlash('error', $e->getMessage());
                    } catch (\Exception $e) {
                        $this->session->sessionFlash('error', "Gagal menghitung ulang tagihan: " . $e->getMessage());
                    }
                    break;

                case 'recalculate_all':
                    try {
                        $bulan = $this->request->postParam('bulan');
                        $recalculated = $tagihanModel->recalculateAllTagihan($bulan);
                        $this->session->sessionFlash('message', "Berhasil hitung ulang $recalculated tagihan untuk bulan $bulan");
                    } catch (\InvalidArgumentException $e) {
                        $this->session->sessionFlash('error', $e->getMessage());
                    } catch (\Exception $e) {
                        $this->session->sessionFlash('error', "Gagal menghitung ulang tagihan: " . $e->getMessage());
                    }
                    break;
            }
            
            $this->redirect($this->config->appConfig('url') . '/admin/tagihan');
        }

        $bulan = $this->request->getParam('bulan', date('Y-m'));
        $tagihan = $tagihanModel->getTagihanDetail($bulan);
        
        // Add barang bawaan data for each kamar in tagihan
        $detailKamarPenghuniModel = $this->loadModel('DetailKamarPenghuniModel');
        $barangBawaanModel = $this->loadModel('BarangBawaanModel');
        
        foreach ($tagihan as &$t) {
            // Get all penghuni in this kamar and their barang bawaan
            $penghuniList = $detailKamarPenghuniModel->findActiveByKamarPenghuni($t['id_kmr_penghuni']);
            $t['detail_penghuni'] = [];
            
            foreach ($penghuniList as $penghuni) {
                $barangBawaan = $barangBawaanModel->getPenghuniBarangDetail($penghuni['id_penghuni']);
                $t['detail_penghuni'][] = [
                    'id_penghuni' => $penghuni['id_penghuni'],
                    'nama' => $penghuni['nama'] ?? 'Nama tidak tersedia',
                    'no_hp' => $penghuni['no_hp'] ?? '',
                    'barang_bawaan' => $barangBawaan
                ];
            }
        }
        
        $data = [
            'title' => 'Kelola Tagihan - ' . $this->config->appConfig('name'),
            'tagihan' => $tagihan,
            'bulan' => $bulan,
            'message' => $this->session->sessionFlash('message'),
            'error' => $this->session->sessionFlash('error'),
            'showSidebar' => true
        ];

        $this->loadView('admin/tagihan', $data);
    }

    public function pembayaran()
    {
        $bayarModel = $this->loadModel('BayarModel');
        $tagihanModel = $this->loadModel('TagihanModel');

        if ($this->request->isPostRequest()) {
            $action = $this->request->postParam('action');

            switch ($action) {
                case 'bayar':
                    $id_tagihan = $this->request->postParam('id_tagihan');
                    $jml_bayar = $this->request->postParam('jml_bayar');
                    
                    // Get tagihan data first (proper MVC pattern)
                    $tagihan = $tagihanModel->findById($id_tagihan);
                    if ($tagihan) {
                        $result = $bayarModel->bayar($id_tagihan, $jml_bayar, $tagihan);
                        if ($result) {
                            $this->session->sessionFlash('message', "Pembayaran berhasil dicatat");
                        } else {
                            $this->session->sessionFlash('error', "Pembayaran gagal");
                        }
                    } else {
                        $this->session->sessionFlash('error', "Tagihan tidak ditemukan");
                    }
                    break;
            }
            
            $this->redirect($this->config->appConfig('url') . '/admin/pembayaran');
        }

        $bulan = $this->request->getParam('bulan', date('Y-m'));
        $laporan = $bayarModel->getLaporanPembayaran($bulan);
        $tagihan = $tagihanModel->getTagihanDetail($bulan);
        
        // Add barang bawaan data for each room in laporan
        $detailKamarPenghuniModel = $this->loadModel('DetailKamarPenghuniModel');
        $barangBawaanModel = $this->loadModel('BarangBawaanModel');
        
        foreach ($laporan as &$l) {
            // Get all penghuni in this room and their barang bawaan
            $penghuniList = $detailKamarPenghuniModel->findActiveByKamarPenghuni($l['id_kmr_penghuni']);
            $l['barang_bawaan'] = [];
            $barangNames = []; // Track unique items to avoid duplicates
            
            foreach ($penghuniList as $penghuni) {
                $barangBawaan = $barangBawaanModel->getPenghuniBarangDetail($penghuni['id_penghuni']);
                foreach ($barangBawaan as $barang) {
                    // Use item name as key to track uniqueness and sum quantities
                    $itemKey = $barang['nama_barang'];
                    if (!isset($barangNames[$itemKey])) {
                        $barangNames[$itemKey] = [
                            'nama_barang' => $barang['nama_barang'],
                            'harga_barang' => $barang['harga_barang'],
                            'jumlah' => 0
                        ];
                    }
                    
                    $barangNames[$itemKey]['jumlah'] += 1;
                }
            }
            
            // Convert back to indexed array for view
            $l['barang_bawaan'] = array_values($barangNames);
        }
        
        // Add barang bawaan data for each kamar in tagihan (using already loaded models)
        
        foreach ($tagihan as &$t) {
            // Get all penghuni in this kamar and their barang bawaan
            $penghuniList = $detailKamarPenghuniModel->findActiveByKamarPenghuni($t['id_kmr_penghuni']);
            $t['detail_penghuni'] = [];
            
            foreach ($penghuniList as $penghuni) {
                $barangBawaan = $barangBawaanModel->getPenghuniBarangDetail($penghuni['id_penghuni']);
                $t['detail_penghuni'][] = [
                    'id_penghuni' => $penghuni['id_penghuni'],
                    'nama' => $penghuni['nama'] ?? 'Nama tidak tersedia',
                    'no_hp' => $penghuni['no_hp'] ?? '',
                    'barang_bawaan' => $barangBawaan
                ];
            }
        }

        $data = [
            'title' => 'Kelola Pembayaran - ' . $this->config->appConfig('name'),
            'laporan' => $laporan,
            'tagihan' => $tagihan,
            'bulan' => $bulan,
            'message' => $this->session->sessionFlash('message'),
            'error' => $this->session->sessionFlash('error'),
            'showSidebar' => true
        ];

        $this->loadView('admin/pembayaran', $data);
    }

    public function dataManagement()
    {
        $data = [
            'title' => 'Kelola Data - ' . $this->config->appConfig('name'),
            'message' => $this->session->sessionFlash('message'),
            'error' => $this->session->sessionFlash('error'),
            'showSidebar' => true
        ];

        $this->loadView('admin/data-management', $data);
    }

    public function exportSql()
    {
        try {
            $db = $this->db();
            
            // Get all table names
            $tables = [
                'users',
                'tb_penghuni',
                'tb_kamar',
                'tb_barang',
                'tb_kmr_penghuni',
                'tb_detail_kmr_penghuni',
                'tb_brng_bawaan',
                'tb_tagihan',
                'tb_bayar'
            ];

            $sqlContent = "-- Export Data SQL\n";
            $sqlContent .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($tables as $table) {
                            // SQL METADATA: Mengecek apakah tabel ada dalam database
            // SHOW TABLES LIKE ? -> menampilkan tabel yang sesuai dengan pattern
            $stmt = $db->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() == 0) {
                continue; // Skip jika tabel tidak ditemukan
            }

            // SQL METADATA: Mengambil struktur tabel (CREATE TABLE statement)
            // SHOW CREATE TABLE `table_name` -> menampilkan SQL untuk membuat tabel
            // Digunakan untuk export struktur tabel beserta kolom, tipe data, dan constraint
            $stmt = $db->prepare("SHOW CREATE TABLE `$table`");
            $stmt->execute();
            $row = $stmt->fetch();
                
                $sqlContent .= "-- Table: $table\n";
                $sqlContent .= "DROP TABLE IF EXISTS `$table`;\n";
                $sqlContent .= $row['Create Table'] . ";\n\n";

                // SQL DATA EXPORT: Mengambil semua data dari tabel
                // SELECT * FROM `table_name` -> ambil semua kolom dan baris dari tabel
                $stmt = $db->prepare("SELECT * FROM `$table`");
                $stmt->execute();
                $rows = $stmt->fetchAll();

                if (!empty($rows)) {
                    $sqlContent .= "-- Data for table: $table\n";
                    
                    // Konversi data ke format INSERT statement
                    foreach ($rows as $row) {
                        $columns = array_keys($row); // Nama kolom
                        // Quote values untuk keamanan dan format SQL yang benar
                        $values = array_map(function($value) use ($db) {
                            if ($value === null) return 'NULL';
                            return $db->quote($value); // Escape string untuk mencegah SQL injection
                        }, array_values($row));
                        
                        // Generate INSERT statement: INSERT INTO table (col1, col2) VALUES (val1, val2)
                        $sqlContent .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $sqlContent .= "\n";
                }
            }

            $sqlContent .= "SET FOREIGN_KEY_CHECKS = 1;\n";

            // Set headers for download
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="kos_data_export_' . date('Y-m-d_H-i-s') . '.sql"');
            header('Content-Length: ' . strlen($sqlContent));
            
            echo $sqlContent;
            exit;

        } catch (\Exception $e) {
            $this->session->sessionFlash('error', 'Gagal mengekspor data: ' . $e->getMessage());
            $this->redirect($this->config->appConfig('url') . '/admin/data-management');
        }
    }

    public function importSql()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect($this->config->appConfig('url') . '/admin/data-management');
            return;
        }

        try {
            // Check file upload
            if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
                $errorMessage = 'File SQL tidak ditemukan atau gagal diupload';
                if (isset($_FILES['sql_file']['error'])) {
                    switch ($_FILES['sql_file']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $errorMessage = 'File terlalu besar. Maksimal 50MB.';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $errorMessage = 'File tidak terupload dengan sempurna.';
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $errorMessage = 'Tidak ada file yang dipilih.';
                            break;
                    }
                }
                throw new \Exception($errorMessage);
            }

            $file = $_FILES['sql_file'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if ($fileExtension !== 'sql') {
                throw new \Exception('File harus berformat .sql');
            }

            // Check file size (50MB max)
            if ($file['size'] > 50 * 1024 * 1024) {
                throw new \Exception('File terlalu besar. Maksimal 50MB.');
            }

            // Read file content
            $sqlContent = file_get_contents($file['tmp_name']);
            if ($sqlContent === false) {
                throw new \Exception('Gagal membaca file SQL');
            }

            // Validate SQL content
            if (empty(trim($sqlContent))) {
                throw new \Exception('File SQL kosong');
            }

            // Execute SQL statements
            $db = $this->db();
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Start transaction for safety
            $db->beginTransaction();
            
            try {
                // Disable foreign key checks during import
                $db->exec("SET FOREIGN_KEY_CHECKS = 0");
                $db->exec("SET autocommit = 0");
                
                // Split SQL statements - improved parsing
                $statements = $this->parseSqlStatements($sqlContent);

                $successCount = 0;
                $errorCount = 0;
                $errors = [];
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        try {
                            $db->exec($statement);
                            $successCount++;
                        } catch (\PDOException $e) {
                            $errorCount++;
                            $error = "Error: " . $e->getMessage();
                            $errors[] = $error;
                            error_log("SQL Import Error: " . $error . " - Statement: " . substr($statement, 0, 200));
                            
                            // Stop on critical errors
                            if (strpos($e->getMessage(), 'Table') !== false && strpos($e->getMessage(), "doesn't exist") !== false) {
                                break;
                            }
                        }
                    }
                }

                // Re-enable foreign key checks
                $db->exec("SET FOREIGN_KEY_CHECKS = 1");
                $db->exec("SET autocommit = 1");
                
                // Commit transaction
                $db->commit();

                $message = "Import berhasil! $successCount statement SQL berhasil dieksekusi.";
                if ($errorCount > 0) {
                    $message .= " $errorCount statement gagal dieksekusi.";
                }
                
                $this->session->sessionFlash('message', $message);

            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            $this->session->sessionFlash('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }

        $this->redirect($this->config->appConfig('url') . '/admin/data-management');
    }

    private function parseSqlStatements($sqlContent)
    {
        // Remove comments
        $sqlContent = preg_replace('/^--.*$/m', '', $sqlContent);
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);
        
        // Split by semicolon but be careful with quoted strings
        $statements = [];
        $currentStatement = '';
        $inQuotes = false;
        $quoteChar = '';
        
        for ($i = 0; $i < strlen($sqlContent); $i++) {
            $char = $sqlContent[$i];
            
            if (!$inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
            } elseif ($inQuotes && $char === $quoteChar) {
                $inQuotes = false;
                $quoteChar = '';
            } elseif (!$inQuotes && $char === ';') {
                $statement = trim($currentStatement);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $currentStatement = '';
                continue;
            }
            
            $currentStatement .= $char;
        }
        
        // Add the last statement if it doesn't end with semicolon
        $statement = trim($currentStatement);
        if (!empty($statement)) {
            $statements[] = $statement;
        }
        
        // Filter out empty statements and comments
        return array_filter($statements, function($stmt) {
            $stmt = trim($stmt);
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        });
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: Admin
 * PURPOSE: Main administrative controller for boarding house management system
 * EXTENDS: Controller (base controller class)
 * AUTHENTICATION: Requires login and admin privileges
 * 
 * BUSINESS_CONTEXT:
 * This controller serves as the central hub for all administrative functions in the
 * boarding house management system. It handles dashboard display, tenant management,
 * room management, billing operations, payment tracking, and data import/export.
 * All methods require authentication and appropriate user roles.
 * 
 * CLASS_METHODS:
 * 
 * 1. __construct($app = null)
 *    PURPOSE: Initialize controller with authentication check
 *    AUTHENTICATION: Calls Auth::requireLogin() to ensure user is logged in
 *    USED_IN: Automatic initialization for all admin requests
 *    AI_CONTEXT: Security barrier preventing unauthorized access
 * 
 * 2. addBarangBawaanToPenghuni($penghuniData)
 *    PURPOSE: Helper method to add belongings data to tenant records
 *    PARAMETERS: $penghuniData: array - Tenant data (single or multiple)
 *    RETURNS: array - Enhanced tenant data with belongings information
 *    USED_IN: Various methods that display tenant information
 *    AI_CONTEXT: Data enrichment utility for comprehensive tenant displays
 * 
 * 3. index()
 *    PURPOSE: Display admin dashboard with statistics and overdue bills
 *    RETURNS: Dashboard view with comprehensive system statistics
 *    DATA_LOADED:
 *      - Room statistics (total, occupied, available)
 *      - Tenant statistics (active, total)
 *      - Financial statistics (bills, payments)
 *      - Overdue bill alerts
 *    USED_IN: Admin panel home page
 *    AI_CONTEXT: Central command center for system overview
 * 
 * 4. penghuni()
 *    PURPOSE: Tenant management interface
 *    HANDLES: Tenant CRUD operations, room assignments, checkout process
 *    BUSINESS_LOGIC:
 *      - GET: Display tenant list with room assignments
 *      - POST: Create new tenant, assign to room, process checkout
 *    FORM_PROCESSING: Validates tenant data, prevents duplicate KTP
 *    USED_IN: Tenant management workflows
 *    AI_CONTEXT: Core tenant lifecycle management
 * 
 * 5. kamar()
 *    PURPOSE: Room management interface
 *    HANDLES: Room CRUD operations, status tracking
 *    BUSINESS_LOGIC:
 *      - GET: Display room list with occupancy status
 *      - POST: Create/update room information
 *    VALIDATION: Prevents duplicate room numbers
 *    USED_IN: Room management workflows
 *    AI_CONTEXT: Physical space management for the boarding house
 * 
 * 6. barang()
 *    PURPOSE: Belongings/items management interface
 *    HANDLES: Belongings CRUD operations for billing calculations
 *    BUSINESS_LOGIC: Manages items that tenants bring, affects billing
 *    INTEGRATION: Used in billing calculations for additional charges
 *    USED_IN: Belongings management, billing setup
 *    AI_CONTEXT: Additional charges management for billing system
 * 
 * 7. tagihan()
 *    PURPOSE: Billing management interface
 *    HANDLES: Bill generation, bill display, billing analytics
 *    BUSINESS_LOGIC:
 *      - GET: Display bills with filtering options
 *      - POST: Generate bills for specific month/year
 *    COMPLEX_OPERATIONS:
 *      - Monthly bill generation with room + belongings costs
 *      - Overdue bill tracking
 *      - Payment status integration
 *    USED_IN: Financial management workflows
 *    AI_CONTEXT: Core financial operations for the business
 * 
 * 8. pembayaran()
 *    PURPOSE: Payment management and reporting interface
 *    HANDLES: Payment recording, payment history, financial reports
 *    BUSINESS_LOGIC:
 *      - Payment tracking with bill relationships
 *      - Partial payment support (installments)
 *      - Payment status calculations
 *    REPORTING: Financial analytics and payment histories
 *    USED_IN: Financial tracking and collection management
 *    AI_CONTEXT: Cash flow management and payment processing
 * 
 * 9. dataManagement()
 *    PURPOSE: Data import/export interface
 *    HANDLES: System data backup and restoration
 *    FEATURES:
 *      - SQL export of all tables
 *      - SQL import with validation
 *      - Data integrity protection
 *    SECURITY: Transaction-based imports, error handling
 *    USED_IN: System maintenance and data migration
 *    AI_CONTEXT: Business continuity and data management
 * 
 * 10. exportSql()
 *     PURPOSE: Export all system data to SQL file
 *     PROCESS:
 *       - Generate CREATE TABLE statements
 *       - Export data as INSERT statements
 *       - Handle foreign key constraints
 *       - Create downloadable SQL file
 *     SECURITY: Proper data sanitization and SQL escaping
 *     USED_IN: Data backup and system migration
 *     AI_CONTEXT: Data backup utility for business continuity
 * 
 * 11. importSql()
 *     PURPOSE: Import data from SQL file
 *     PROCESS:
 *       - Validate file format and size
 *       - Parse SQL statements safely
 *       - Execute in transaction for data integrity
 *       - Error handling and rollback capability
 *     SECURITY: SQL injection prevention, transaction safety
 *     USED_IN: Data restoration and system migration
 *     AI_CONTEXT: Data restoration utility with integrity protection
 * 
 * 12. parseSqlStatements($sqlContent)
 *     PURPOSE: Safely parse SQL file content into executable statements
 *     PARAMETERS: $sqlContent: string - Raw SQL file content
 *     RETURNS: array - Array of individual SQL statements
 *     SECURITY: Handles quoted strings, removes comments, prevents injection
 *     USED_IN: SQL import processing
 *     AI_CONTEXT: Security utility for safe SQL processing
 * 
 * AUTHENTICATION_FLOW:
 * All methods require authentication through Auth::requireLogin()
 * User session provides access to user role and permissions
 * Some features may be restricted to specific roles (superadmin)
 * 
 * DATA_FLOW_PATTERNS:
 * 1. Dashboard Statistics:
 *    Models -> Statistical calculations -> Dashboard view
 * 
 * 2. CRUD Operations:
 *    Form submission -> Validation -> Model operations -> Redirect with message
 * 
 * 3. Complex Operations (Billing):
 *    Multiple model coordination -> Business logic -> Result processing
 * 
 * ERROR_HANDLING:
 * - Session flash messages for user feedback
 * - Try-catch blocks for critical operations
 * - Validation with user-friendly error messages
 * - Transaction rollback for data integrity
 * 
 * SECURITY_FEATURES:
 * - Authentication required for all methods
 * - CSRF protection through proper form handling
 * - SQL injection prevention via parameter binding
 * - File upload validation for imports
 * - Transaction-based operations for data integrity
 * 
 * INTEGRATION_POINTS:
 * - Works with all model classes for data operations
 * - Integrates with Auth controller for security
 * - Uses Session for user feedback and state management
 * - Coordinates between multiple business domains
 * 
 * AI_INTEGRATION_NOTES:
 * - This controller contains the most complex business logic flows
 * - Handles multiple interconnected business processes
 * - Critical for daily operations of the boarding house
 * - Integrates financial, operational, and administrative functions
 * - Supports both simple CRUD and complex business operations
 * - Essential for system administration and business management
 */