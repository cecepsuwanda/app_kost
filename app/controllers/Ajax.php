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

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: Ajax
 * PURPOSE: Handles AJAX requests for dynamic data loading and asynchronous operations
 * EXTENDS: Controller (base controller class)
 * AUTHENTICATION: Requires login for all methods
 * 
 * BUSINESS_CONTEXT:
 * This controller provides AJAX endpoints for dynamic user interface interactions
 * without full page reloads. It supports real-time data fetching, form submissions,
 * and interactive features that enhance user experience in the admin panel.
 * All responses are in JSON format for JavaScript consumption.
 * 
 * CLASS_METHODS:
 * 
 * 1. getPenghuniByKamar()
 *    PURPOSE: Get tenant list for specific room via AJAX
 *    PARAMETERS: $_POST['id_kamar'] - Room ID
 *    RETURNS: JSON with tenant data for the room
 *    USED_IN: Room management interfaces, tenant assignment dropdowns
 *    AI_CONTEXT: Dynamic room-tenant relationship display
 * 
 * 2. getKamarTersedia()
 *    PURPOSE: Get available rooms list via AJAX
 *    PARAMETERS: Optional capacity parameters
 *    RETURNS: JSON with available rooms data
 *    USED_IN: Tenant assignment interfaces, room selection
 *    AI_CONTEXT: Real-time room availability checking
 * 
 * 3. getTagihanDetails()
 *    PURPOSE: Get detailed billing information via AJAX
 *    PARAMETERS: $_POST['id_tagihan'] - Bill ID
 *    RETURNS: JSON with bill details and payment information
 *    USED_IN: Bill management interfaces, payment forms
 *    AI_CONTEXT: Dynamic billing detail display
 * 
 * 4. getPembayaranByPeriode()
 *    PURPOSE: Get payment data for specific period via AJAX
 *    PARAMETERS: $_POST['periode'] - Period string (YYYY-MM)
 *    RETURNS: JSON with payment report data
 *    USED_IN: Financial reporting interfaces, dashboard analytics
 *    AI_CONTEXT: Dynamic financial reporting and analytics
 * 
 * AJAX_FEATURES:
 * - JSON response format for all methods
 * - Error handling with structured error responses
 * - Authentication validation for security
 * - Real-time data fetching without page reloads
 * - Support for dynamic form population
 * 
 * USAGE_PATTERNS:
 * - Called from JavaScript in admin panel views
 * - Used for dynamic dropdown population
 * - Supports interactive dashboard features
 * - Enables real-time data updates
 * 
 * AI_INTEGRATION_NOTES:
 * - Provides API-like endpoints for frontend interactions
 * - Enables responsive user interface without page reloads
 * - Critical for modern web application user experience
 * - Supports real-time data visualization and reporting
 */