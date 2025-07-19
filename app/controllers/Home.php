<?php

namespace App\Controllers;

use App\Core\Controller;

class Home extends Controller
{
    public function __construct($app = null)
    {
        parent::__construct($app);
    }

    public function index()
    {
        $kamarModel = $this->loadModel('KamarModel');
        $kamarPenghuniModel = $this->loadModel('KamarPenghuniModel');
        $tagihanModel = $this->loadModel('TagihanModel');
        $barangBawaanModel = $this->loadModel('BarangBawaanModel');

        // Get available rooms
        $kamarKosong = $kamarModel->getKamarKosong();

        // Get rooms approaching payment due date (next 5 days)
        $kamarMendekatiJatuhTempo = $kamarPenghuniModel->getKamarSewaanMendekatiJatuhTempo(3);
        
        // Add barang bawaan data for each penghuni
        foreach ($kamarMendekatiJatuhTempo as &$kamar) {
            if (isset($kamar['id_penghuni']) && $kamar['id_penghuni']) {
                $kamar['barang_bawaan'] = $barangBawaanModel->getPenghuniBarangDetail($kamar['id_penghuni']);
            }
        }

        // Get overdue payments
        $tagihanTerlambat = $tagihanModel->getTagihanTerlambat();
                
        // Add barang bawaan data for each penghuni in tagihan
        foreach ($tagihanTerlambat as &$tagihan) {
            if (isset($tagihan['id_penghuni']) && $tagihan['id_penghuni']) {
                $tagihan['barang_bawaan'] = $barangBawaanModel->getPenghuniBarangDetail($tagihan['id_penghuni']);
            }
        }

        $data = [
            'title' => 'Selamat Datang - ' . $this->config->appConfig('name'),
            'kamarKosong' => $kamarKosong,
            'kamarMendekatiJatuhTempo' => $kamarMendekatiJatuhTempo,
            'tagihanTerlambat' => $tagihanTerlambat
        ];
            
        $this->loadView('home/index', $data);
    }
}