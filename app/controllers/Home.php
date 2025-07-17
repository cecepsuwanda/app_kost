<?php

namespace App\Controllers;

use App\Core\Controller;

class Home extends Controller
{
    public function index()
    {
        $kamarModel = $this->loadModel('KamarModel');
        $kamarPenghuniModel = $this->loadModel('KamarPenghuniModel');
        $tagihanModel = $this->loadModel('TagihanModel');

        // Get available rooms
        $kamarKosong = $kamarModel->getKamarKosong();

        // Get rooms approaching payment due date (next 5 days)
        $kamarMendekatiJatuhTempo = $kamarPenghuniModel->getKamarSewaanMendekatiJatuhTempo(5);

        // Get overdue payments
        $tagihanTerlambat = $tagihanModel->getTagihanTerlambat();
       
        $data = [
            'title' => 'Selamat Datang - ' . $this->config->appConfig('name'),
            'kamarKosong' => $kamarKosong,
            'kamarMendekatiJatuhTempo' => $kamarMendekatiJatuhTempo,
            'tagihanTerlambat' => $tagihanTerlambat
        ];

        $this->loadView('home/index', $data);
    }
}