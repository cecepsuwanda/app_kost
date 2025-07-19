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

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: Home
 * PURPOSE: Handles public-facing home page and general system information
 * EXTENDS: Controller (base controller class)
 * SECURITY_LEVEL: Public access (no authentication required)
 * 
 * BUSINESS_CONTEXT:
 * This controller manages the public home page of the boarding house management
 * system. It provides general information about the system, basic statistics,
 * and serves as the landing page for visitors. It may also provide public
 * information about the boarding house for potential tenants.
 * 
 * CLASS_METHODS:
 * 
 * 1. index()
 *    PURPOSE: Display public home page with system information
 *    PARAMETERS: None
 *    RETURNS: Home page view with system statistics
 *    DATA_PROVIDED:
 *      - System name and basic information
 *      - Public statistics (total rooms, availability)
 *      - General boarding house information
 *      - Contact information or links to admin
 *    USED_IN: Public website access, system landing page
 *    AI_CONTEXT: Public interface for the boarding house system
 * 
 * PUBLIC_FEATURES:
 * - No authentication required
 * - General system information display
 * - Basic statistics for public viewing
 * - Landing page for system access
 * 
 * USAGE_PATTERNS:
 * - Accessed by general public or potential tenants
 * - Entry point before admin login
 * - Information display about the boarding house
 * 
 * AI_INTEGRATION_NOTES:
 * - Provides public interface to the system
 * - May serve as information portal for potential tenants
 * - Simple controller with limited functionality
 * - Could be expanded for public tenant portal features
 */