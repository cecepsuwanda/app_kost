<?php

namespace App\Controllers;

use App\Core\Controller;

class Ajax extends Controller
{
    public function __construct($app = null)
    {
        parent::__construct($app);
    }

    public function handle()
    {
        // Check if request has action parameter
        $action = $this->request->getParam('action');
        
        if (!$action) {
            $this->json(['error' => 'No action specified']);
            return;
        }
        
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            switch ($action) {
                case 'get_penghuni':
                    $this->getPenghuni();
                    break;
                    
                case 'get_kamar_available':
                    $this->getKamarAvailable();
                    break;
                    
                case 'delete_penghuni':
                    $this->deletePenghuni();
                    break;
                    
                case 'delete_kamar':
                    $this->deleteKamar();
                    break;
                    
                case 'delete_barang':
                    $this->deleteBarang();
                    break;
                    
                case 'get_tagihan_by_month':
                    $this->getTagihanByMonth();
                    break;
                    
                case 'get_pembayaran_by_month':
                    $this->getPembayaranByMonth();
                    break;
                    
                default:
                    $this->json(['error' => 'Invalid action']);
            }
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()]);
        }
    }
    
    private function getPenghuni()
    {
        $penghuniModel = $this->loadModel('PenghuniModel');
        $penghuni = $penghuniModel->findAll();
        $this->json(['success' => true, 'data' => $penghuni]);
    }
    
    private function getKamarAvailable()
    {
        $kamarModel = $this->loadModel('KamarModel');
        $kamar = $kamarModel->getKamarTersedia();
        $this->json(['success' => true, 'data' => $kamar]);
    }
    
    private function deletePenghuni()
    {
        $id = $this->request->postParam('id');
        if (!$id) {
            $this->json(['error' => 'ID penghuni tidak ditemukan']);
            return;
        }
        
        $penghuniModel = $this->loadModel('PenghuniModel');
        $result = $penghuniModel->delete($id);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Penghuni berhasil dihapus']);
        } else {
            $this->json(['error' => 'Gagal menghapus penghuni']);
        }
    }
    
    private function deleteKamar()
    {
        $id = $this->request->postParam('id');
        if (!$id) {
            $this->json(['error' => 'ID kamar tidak ditemukan']);
            return;
        }
        
        $kamarModel = $this->loadModel('KamarModel');
        $result = $kamarModel->delete($id);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Kamar berhasil dihapus']);
        } else {
            $this->json(['error' => 'Gagal menghapus kamar']);
        }
    }
    
    private function deleteBarang()
    {
        $id = $this->request->postParam('id');
        if (!$id) {
            $this->json(['error' => 'ID barang tidak ditemukan']);
            return;
        }
        
        $barangModel = $this->loadModel('BarangModel');
        $result = $barangModel->delete($id);
        
        if ($result) {
            $this->json(['success' => true, 'message' => 'Barang berhasil dihapus']);
        } else {
            $this->json(['error' => 'Gagal menghapus barang']);
        }
    }
    
    private function getTagihanByMonth()
    {
        $periode = $this->request->getParam('periode');
        if (!$periode) {
            $this->json(['error' => 'Periode tidak ditemukan']);
            return;
        }
        
        $tagihanModel = $this->loadModel('TagihanModel');
        $tagihan = $tagihanModel->getTagihanDetail($periode);
        $this->json(['success' => true, 'data' => $tagihan]);
    }
    
    private function getPembayaranByMonth()
    {
        $periode = $this->request->getParam('periode');
        if (!$periode) {
            $this->json(['error' => 'Periode tidak ditemukan']);
            return;
        }
        
        $bayarModel = $this->loadModel('BayarModel');
        $pembayaran = $bayarModel->getLaporanPembayaran($periode);
        $this->json(['success' => true, 'data' => $pembayaran]);
    }
}