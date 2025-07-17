<?php

namespace App\Controllers;

use App\Core\Controller;

class Admin extends Controller
{
    private $auth;
    
    public function __construct()
    {
        parent::__construct();
        
        // Initialize Auth instance and check authentication
        $this->auth = new \App\Controllers\Auth();
        $this->auth->requireLogin();
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
            'title' => 'Dashboard Admin - ' . $this->config->appConfig('name'),
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

        if ($this->request->isPostRequest()) {
            $action = $this->request->postParam('action');

            switch ($action) {
                case 'create':
                    $data = [
                        'nama' => $this->request->postParam('nama'),
                        'no_ktp' => $this->request->postParam('no_ktp'),
                        'no_hp' => $this->request->postParam('no_hp'),
                        'tgl_masuk' => $this->request->postParam('tgl_masuk')
                    ];
                    
                    $id_penghuni = $penghuniModel->create($data);
                    
                    // Assign to room if selected
                    if ($this->request->postParam('id_kamar')) {
                        $id_kamar = $this->request->postParam('id_kamar');
                        
                        // Check if room already has active occupancy
                        $activeKamarPenghuni = $kamarPenghuniModel->findActiveByKamar($id_kamar);
                        
                        if ($activeKamarPenghuni) {
                            // Check room capacity
                            if ($kamarPenghuniModel->checkKamarCapacity($id_kamar)) {
                                $kamarPenghuniModel->addPenghuniToKamar($activeKamarPenghuni['id'], $id_penghuni, $this->request->postParam('tgl_masuk'));
                            }
                        } else {
                            // Create new kamar penghuni record
                            $kamarPenghuniModel->createKamarPenghuni($id_kamar, $this->request->postParam('tgl_masuk'), [$id_penghuni]);
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
                        'no_ktp' => $this->request->postParam('no_ktp'),
                        'no_hp' => $this->request->postParam('no_hp'),
                        'tgl_masuk' => $this->request->postParam('tgl_masuk')
                    ];
                    
                    if ($this->request->postParam('tgl_keluar')) {
                        $data['tgl_keluar'] = $this->request->postParam('tgl_keluar');
                    }
                    
                    $penghuniModel->update($id, $data);
                    break;

                case 'delete':
                    $penghuniModel->delete($this->request->postParam('id'));
                    break;

                case 'checkout':
                    $id = $this->request->postParam('id');
                    $tgl_keluar = $this->request->postParam('tgl_keluar');
                    
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
                    $id_penghuni = $this->request->postParam('id_penghuni');
                    $id_kamar_baru = $this->request->postParam('id_kamar_baru');
                    $tgl_pindah = $this->request->postParam('tgl_pindah');
                    
                    $kamarPenghuniModel->pindahKamar($id_penghuni, $id_kamar_baru, $tgl_pindah);
                    break;
            }
            
            $this->redirect('/admin/penghuni');
        }

        $penghuni = $penghuniModel->getPenghuniWithKamar();
        $kamarTersedia = $kamarModel->getKamarTersedia();
        $barang = $barangModel->findAll();

        $data = [
            'title' => 'Kelola Penghuni - ' . $this->config->appConfig('name'),
            'penghuni' => $penghuni,
            'kamarTersedia' => $kamarTersedia,
            'barang' => $barang
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
                        'harga' => $this->request->postParam('harga')
                    ]);
                    break;

                case 'update':
                    $kamarModel->update($this->request->postParam('id'), [
                        'nomor' => $this->request->postParam('nomor'),
                        'harga' => $this->request->postParam('harga')
                    ]);
                    break;

                case 'delete':
                    $kamarModel->delete($this->request->postParam('id'));
                    break;
            }
            
            $this->redirect('/admin/kamar');
        }

        $kamar = $kamarModel->getKamarWithStatus();

        $data = [
            'title' => 'Kelola Kamar - ' . $this->config->appConfig('name'),
            'kamar' => $kamar
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
            
            $this->redirect('/admin/barang');
        }

        $barang = $barangModel->findAll();

        $data = [
            'title' => 'Kelola Barang - ' . $this->config->appConfig('name'),
            'barang' => $barang
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
                    $bulan = $this->request->postParam('bulan');
                    $generated = $tagihanModel->generateTagihan($bulan);
                    $this->session->sessionFlash('message', "Berhasil generate $generated tagihan untuk bulan $bulan");
                    break;
            }
            
            $this->redirect($this->config->appConfig('url') . '/admin/tagihan');
        }

        $bulan = $this->request->getParam('bulan', date('Y-m'));
        $tagihan = $tagihanModel->getTagihanDetail($bulan);

        $data = [
            'title' => 'Kelola Tagihan - ' . $this->config->appConfig('name'),
            'tagihan' => $tagihan,
            'bulan' => $bulan,
            'message' => $this->session->sessionFlash('message')
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
                    
                    $result = $bayarModel->bayar($id_tagihan, $jml_bayar);
                    if ($result) {
                        $this->session->sessionFlash('message', "Pembayaran berhasil dicatat");
                    } else {
                        $this->session->sessionFlash('error', "Pembayaran gagal");
                    }
                    break;
            }
            
            $this->redirect($this->config->appConfig('url') . '/admin/pembayaran');
        }

        $bulan = $this->request->getParam('bulan', date('Y-m'));
        $laporan = $bayarModel->getLaporanPembayaran($bulan);
        $tagihan = $tagihanModel->getTagihanDetail($bulan);
        
        $data = [
            'title' => 'Kelola Pembayaran - ' . $this->config->appConfig('name'),
            'laporan' => $laporan,
            'tagihan' => $tagihan,
            'bulan' => $bulan,
            'message' => $this->session->sessionFlash('message'),
            'error' => $this->session->sessionFlash('error')
        ];

        $this->loadView('admin/pembayaran', $data);
    }
}