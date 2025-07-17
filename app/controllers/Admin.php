<?php

namespace App\Controllers;

use App\Core\Controller;

class Admin extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Check authentication for all admin methods
        \App\Controllers\Auth::requireLogin();
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
            'mendekati_jatuh_tempo' => count($kamarPenghuniModel->getKamarSewaanMendekatiJatuhTempo(5))
        ];

        // Get data for dashboard
        $kamarKosong = $kamarModel->getKamarKosong();
        $kamarTersedia = $kamarModel->getKamarTersedia();
        $kamarMendekatiJatuhTempo = $kamarPenghuniModel->getKamarSewaanMendekatiJatuhTempo(5);
        $tagihanTerlambat = $tagihanModel->getTagihanTerlambat();

        $data = [
            'title' => 'Dashboard Admin - ' . APP_NAME,
            'stats' => $stats,
            'kamarKosong' => $kamarKosong,
            'kamarTersedia' => $kamarTersedia,
            'kamarMendekatiJatuhTempo' => $kamarMendekatiJatuhTempo,
            'tagihanTerlambat' => $tagihanTerlambat
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');

            switch ($action) {
                case 'create':
                    $data = [
                        'nama' => $this->post('nama'),
                        'no_ktp' => $this->post('no_ktp'),
                        'no_hp' => $this->post('no_hp'),
                        'tgl_masuk' => $this->post('tgl_masuk')
                    ];
                    
                    $id_penghuni = $penghuniModel->create($data);
                    
                    // Assign to room if selected
                    if ($this->post('id_kamar')) {
                        $id_kamar = $this->post('id_kamar');
                        
                        // Check if room already has active occupancy
                        $activeKamarPenghuni = $kamarPenghuniModel->findActiveByKamar($id_kamar);
                        
                        if ($activeKamarPenghuni) {
                            // Check room capacity
                            if ($kamarPenghuniModel->checkKamarCapacity($id_kamar)) {
                                $kamarPenghuniModel->addPenghuniToKamar($activeKamarPenghuni['id'], $id_penghuni, $this->post('tgl_masuk'));
                            }
                        } else {
                            // Create new kamar penghuni record
                            $kamarPenghuniModel->createKamarPenghuni($id_kamar, $this->post('tgl_masuk'), [$id_penghuni]);
                        }
                    }

                    // Add barang bawaan if selected
                    $barang_ids = $this->post('barang_ids', []);
                    foreach ($barang_ids as $id_barang) {
                        $barangBawaanModel->create([
                            'id_penghuni' => $id_penghuni,
                            'id_barang' => $id_barang
                        ]);
                    }
                    break;

                case 'update':
                    $id = $this->post('id');
                    $data = [
                        'nama' => $this->post('nama'),
                        'no_ktp' => $this->post('no_ktp'),
                        'no_hp' => $this->post('no_hp'),
                        'tgl_masuk' => $this->post('tgl_masuk')
                    ];
                    
                    if ($this->post('tgl_keluar')) {
                        $data['tgl_keluar'] = $this->post('tgl_keluar');
                    }
                    
                    $penghuniModel->update($id, $data);
                    break;

                case 'delete':
                    $penghuniModel->delete($this->post('id'));
                    break;

                case 'checkout':
                    $id = $this->post('id');
                    $tgl_keluar = $this->post('tgl_keluar');
                    
                    // Update penghuni
                    $penghuniModel->update($id, ['tgl_keluar' => $tgl_keluar]);
                    
                    // Update detail kamar penghuni
                    $detailKamarPenghuniModel->checkoutPenghuniFromKamar($id, $tgl_keluar);
                    
                    // Check if kamar becomes empty and close it
                    $kamarPenghuni = $kamarPenghuniModel->findKamarByPenghuni($id);
                    if ($kamarPenghuni) {
                        $remainingPenghuni = $detailKamarPenghuniModel->findActiveByKamarPenghuni($kamarPenghuni['id']);
                        if (empty($remainingPenghuni)) {
                            $kamarPenghuniModel->checkoutKamar($kamarPenghuni['id'], $tgl_keluar);
                        }
                    }
                    break;

                case 'pindah_kamar':
                    $id_penghuni = $this->post('id_penghuni');
                    $id_kamar_baru = $this->post('id_kamar_baru');
                    $tgl_pindah = $this->post('tgl_pindah');
                    
                    $kamarPenghuniModel->pindahKamar($id_penghuni, $id_kamar_baru, $tgl_pindah);
                    break;
            }
            
            $this->redirect('/admin/penghuni');
        }

        $penghuni = $penghuniModel->getPenghuniWithKamar();
        $kamarTersedia = $kamarModel->getKamarTersedia();
        $barang = $barangModel->findAll();

        $data = [
            'title' => 'Kelola Penghuni - ' . APP_NAME,
            'penghuni' => $penghuni,
            'kamarTersedia' => $kamarTersedia,
            'barang' => $barang
        ];

        $this->loadView('admin/penghuni', $data);
    }

    public function kamar()
    {
        $kamarModel = $this->loadModel('KamarModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');

            switch ($action) {
                case 'create':
                    $kamarModel->create([
                        'nomor' => $this->post('nomor'),
                        'harga' => $this->post('harga')
                    ]);
                    break;

                case 'update':
                    $kamarModel->update($this->post('id'), [
                        'nomor' => $this->post('nomor'),
                        'harga' => $this->post('harga')
                    ]);
                    break;

                case 'delete':
                    $kamarModel->delete($this->post('id'));
                    break;
            }
            
            $this->redirect('/admin/kamar');
        }

        $kamar = $kamarModel->getKamarWithStatus();

        $data = [
            'title' => 'Kelola Kamar - ' . APP_NAME,
            'kamar' => $kamar
        ];

        $this->loadView('admin/kamar', $data);
    }

    public function barang()
    {
        $barangModel = $this->loadModel('BarangModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');

            switch ($action) {
                case 'create':
                    $barangModel->create([
                        'nama' => $this->post('nama'),
                        'harga' => $this->post('harga')
                    ]);
                    break;

                case 'update':
                    $barangModel->update($this->post('id'), [
                        'nama' => $this->post('nama'),
                        'harga' => $this->post('harga')
                    ]);
                    break;

                case 'delete':
                    $barangModel->delete($this->post('id'));
                    break;
            }
            
            $this->redirect('/admin/barang');
        }

        $barang = $barangModel->findAll();

        $data = [
            'title' => 'Kelola Barang - ' . APP_NAME,
            'barang' => $barang
        ];

        $this->loadView('admin/barang', $data);
    }

    public function tagihan()
    {
        $tagihanModel = $this->loadModel('TagihanModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');

            switch ($action) {
                case 'generate':
                    $bulan = $this->post('bulan');
                    $generated = $tagihanModel->generateTagihan($bulan);
                    $_SESSION['message'] = "Berhasil generate $generated tagihan untuk bulan $bulan";
                    break;
            }
            
            $this->redirect('/admin/tagihan');
        }

        $bulan = $this->get('bulan', date('Y-m'));
        $tagihan = $tagihanModel->getTagihanDetail($bulan);

        $data = [
            'title' => 'Kelola Tagihan - ' . APP_NAME,
            'tagihan' => $tagihan,
            'bulan' => $bulan,
            'message' => $_SESSION['message'] ?? null
        ];

        unset($_SESSION['message']);

        $this->loadView('admin/tagihan', $data);
    }

    public function pembayaran()
    {
        $bayarModel = $this->loadModel('BayarModel');
        $tagihanModel = $this->loadModel('TagihanModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');

            switch ($action) {
                case 'bayar':
                    $id_tagihan = $this->post('id_tagihan');
                    $jml_bayar = $this->post('jml_bayar');
                    
                    $result = $bayarModel->bayar($id_tagihan, $jml_bayar);
                    if ($result) {
                        $_SESSION['message'] = "Pembayaran berhasil dicatat";
                    } else {
                        $_SESSION['error'] = "Pembayaran gagal";
                    }
                    break;
            }
            
            $this->redirect('/admin/pembayaran');
        }

        $bulan = $this->get('bulan', date('Y-m'));
        $laporan = $bayarModel->getLaporanPembayaran($bulan);
        $tagihan = $tagihanModel->getTagihanDetail($bulan);

        $data = [
            'title' => 'Kelola Pembayaran - ' . APP_NAME,
            'laporan' => $laporan,
            'tagihan' => $tagihan,
            'bulan' => $bulan,
            'message' => $_SESSION['message'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ];

        unset($_SESSION['message'], $_SESSION['error']);

        $this->loadView('admin/pembayaran', $data);
    }
}