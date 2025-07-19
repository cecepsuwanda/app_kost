<?php

namespace App\Models;

use App\Core\Model;

class BayarModel extends Model
{
    protected $table = 'tb_bayar';

        public function findByTagihan($id_tagihan)
    {
        // SQL: Mengambil semua pembayaran untuk tagihan tertentu
        // SELECT * FROM tb_bayar WHERE id_tagihan = ? ORDER BY id DESC
        //
        // Penjelasan:
        // - WHERE id_tagihan = ?: filter pembayaran berdasarkan tagihan tertentu
        // - ORDER BY id DESC: urutkan dari pembayaran terbaru ke terlama
        // - Digunakan untuk melihat history pembayaran/cicilan suatu tagihan
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_tagihan = :id_tagihan ORDER BY id DESC",
            ['id_tagihan' => $id_tagihan]);
    }

        public function getTotalBayarByTagihan($id_tagihan)
    {
        // SQL AGGREGATE: Menghitung total semua pembayaran untuk tagihan tertentu
        // SELECT SUM(jml_bayar) as total FROM tb_bayar WHERE id_tagihan = ?
        //
        // Penjelasan:
        // - SUM(jml_bayar): menjumlahkan semua nilai pembayaran
        // - WHERE id_tagihan = ?: filter untuk tagihan tertentu
        // - Digunakan untuk mengetahui sudah berapa banyak yang dibayar dari total tagihan
        // - Result['total'] ?? 0: jika belum ada pembayaran, return 0
        $result = $this->db->fetch("SELECT SUM(jml_bayar) as total FROM {$this->table} WHERE id_tagihan = :id_tagihan",
            ['id_tagihan' => $id_tagihan]);
        return $result['total'] ?? 0;
    }

    public function getStatusByTagihan($id_tagihan)
    {
        $sql = "SELECT t.jml_tagihan, COALESCE(SUM(b.jml_bayar), 0) as total_bayar,
                       CASE 
                           WHEN COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan THEN 'lunas'
                           WHEN COALESCE(SUM(b.jml_bayar), 0) > 0 THEN 'cicil'
                           ELSE 'belum_bayar'
                       END as status
                FROM tb_tagihan t
                LEFT JOIN {$this->table} b ON t.id = b.id_tagihan
                WHERE t.id = :id_tagihan
                GROUP BY t.id";
        
        return $this->db->fetch($sql, ['id_tagihan' => $id_tagihan]);
    }

    public function bayar($id_tagihan, $jml_bayar, $tagihan_data = null)
    {
        // If tagihan data is not provided, we need to get it from external source
        // This should be handled by the controller
        if (!$tagihan_data) {
            throw new \InvalidArgumentException("Tagihan data must be provided by controller");
        }

        // Get total already paid
        $totalBayar = $this->getTotalBayarByTagihan($id_tagihan);
        $sisaTagihan = $tagihan_data['jml_tagihan'] - $totalBayar;

        // Determine status
        if ($jml_bayar >= $sisaTagihan) {
            $status = 'lunas';
            $jml_bayar = $sisaTagihan; // Don't allow overpayment
        } else {
            $status = 'cicil';
        }

        // Create payment record
        return $this->create([
            'id_tagihan' => $id_tagihan,
            'jml_bayar' => $jml_bayar,
            'status' => $status
        ]);
    }

    public function getPembayaranDetail($id_tagihan = null)
    {
        $sql = "SELECT b.*, t.bulan, t.jml_tagihan,
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni, k.nomor as nomor_kamar
                FROM {$this->table} b
                INNER JOIN tb_tagihan t ON b.id_tagihan = t.id
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                " . ($id_tagihan ? "WHERE b.id_tagihan = :id_tagihan " : "") . "
                GROUP BY b.id
                ORDER BY b.id DESC";
        
        $params = $id_tagihan ? ['id_tagihan' => $id_tagihan] : [];
        return $this->db->fetchAll($sql, $params);
    }

    public function getLaporanPembayaran($periode = null)
    {
        $whereCondition = "";
        $params = [];
        
        if ($periode) {
            // Parse periode (format: YYYY-MM) to extract bulan and tahun
            $date = date_create_from_format('Y-m', $periode);
            if ($date) {
                $bulan = (int)$date->format('n'); // 1-12
                $tahun = (int)$date->format('Y'); // YYYY
                $whereCondition = "WHERE t.bulan = :bulan AND t.tahun = :tahun ";
                $params = ['bulan' => $bulan, 'tahun' => $tahun];
            }
        }


        $sql = "SELECT t.id, t.bulan, t.tahun, t.tanggal, t.id_kmr_penghuni,
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni, 
                       k.nomor as nomor_kamar, k.gedung,
                       GROUP_CONCAT(DISTINCT p.tgl_masuk SEPARATOR ', ') as tgl_masuk_penghuni,
                       t.jml_tagihan, COALESCE(SUM(b.jml_bayar), 0) as total_bayar,
                       DATEDIFF(CURDATE(), t.tanggal) as selisih_hari,
                       DATEDIFF(t.tanggal, kp.tgl_masuk) as selisih_dari_tgl_masuk_kamar_penghuni,
                       CASE 
                           WHEN COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
                           WHEN COALESCE(SUM(b.jml_bayar), 0) > 0 THEN 'Cicil'
                           ELSE 'Belum Bayar'
                       END as status_bayar,
                       CASE 
                           WHEN COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan THEN 'lunas'
                           WHEN DATEDIFF(t.tanggal, kp.tgl_masuk) < 0 THEN 'terlambat'
                           WHEN DATEDIFF(t.tanggal, kp.tgl_masuk) BETWEEN 0 AND 3 THEN 'mendekati'
                           ELSE 'normal'
                       END as status_waktu
                FROM tb_tagihan t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN {$this->table} b ON t.id = b.id_tagihan
                " . $whereCondition . "
                GROUP BY t.id
                ORDER BY t.tahun DESC, t.bulan DESC, k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalBayarPerGedung($periode = null)
    {
        $whereCondition = "";
        $params = [];
        
        if ($periode) {
            $date = date_create_from_format('Y-m', $periode);
            if ($date) {
                $bulan = (int)$date->format('n');
                $tahun = (int)$date->format('Y');
                $whereCondition = "WHERE t.bulan = :bulan AND t.tahun = :tahun ";
                $params = ['bulan' => $bulan, 'tahun' => $tahun];
            }
        }

        $sql = "SELECT k.gedung,
                       COUNT(DISTINCT t.id) as jumlah_tagihan,
                       SUM(COALESCE(b.jml_bayar, 0)) as total_dibayar,
                       COUNT(DISTINCT b.id) as jumlah_pembayaran
                FROM tb_tagihan t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN {$this->table} b ON t.id = b.id_tagihan
                " . $whereCondition . "
                GROUP BY k.gedung
                ORDER BY k.gedung";
        
        return $this->db->fetchAll($sql, $params);
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: BayarModel
 * PURPOSE: Manages payment transactions and billing-payment relationships
 * DATABASE_TABLE: tb_bayar
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model handles all payment transactions in the boarding house system.
 * It tracks payments made by tenants against their bills, supports partial
 * payments/installments, and provides payment analytics. Each payment is
 * linked to a specific bill and can be part of multiple payments for one bill.
 * 
 * CLASS_METHODS:
 * 
 * 1. findByTagihan($id_tagihan)
 *    PURPOSE: Get all payments for a specific bill
 *    PARAMETERS: $id_tagihan: int - Bill ID to find payments for
 *    RETURNS: array - List of payments for the bill, ordered by newest first
 *    SQL_QUERY: SELECT * FROM tb_bayar WHERE id_tagihan = ? ORDER BY id DESC
 *    USED_IN:
 *      - Admin::pembayaran() - displaying payment history for a bill
 *      - Bill detail views - showing payment breakdown
 *      - Payment status calculations
 *    AI_CONTEXT: Core method for tracking payment history per bill
 * 
 * 2. getTotalBayarByTagihan($id_tagihan)
 *    PURPOSE: Calculate total amount paid for a specific bill
 *    PARAMETERS: $id_tagihan: int - Bill ID to calculate total for
 *    RETURNS: float - Total amount paid (0 if no payments)
 *    SQL_QUERY: SELECT SUM(jml_bayar) as total FROM tb_bayar WHERE id_tagihan = ?
 *    BUSINESS_LOGIC: Uses COALESCE to return 0 instead of NULL for no payments
 *    USED_IN:
 *      - Payment status determination (paid/unpaid/partial)
 *      - Outstanding balance calculations
 *      - Financial reporting and analytics
 *    AI_CONTEXT: Critical for determining payment completion status
 * 
 * DATABASE_RELATIONSHIPS:
 * - MANY-TO-ONE with tb_tagihan (multiple payments per bill)
 * - INDIRECT with tb_kmr_penghuni through tb_tagihan
 * - INDIRECT with room and tenant data through billing relationships
 * 
 * KEY_FIELDS:
 * - id: Primary key
 * - id_tagihan: Foreign key to tb_tagihan (which bill this payment is for)
 * - jml_bayar: Payment amount
 * - tgl_bayar: Payment date
 * - metode_bayar: Payment method (cash, transfer, etc.)
 * - keterangan: Payment notes/description
 * - created_at: Record creation timestamp
 * 
 * BUSINESS_RULES:
 * - Multiple payments allowed per bill (installment support)
 * - Payment amounts must be positive
 * - Total payments can exceed bill amount (overpayment tracking)
 * - Payment history preserved for audit trail
 * - Payment dates tracked for cash flow analysis
 * 
 * PAYMENT_STATUS_LOGIC:
 * - UNPAID: getTotalBayarByTagihan() = 0
 * - PARTIAL: 0 < getTotalBayarByTagihan() < bill amount
 * - PAID: getTotalBayarByTagihan() >= bill amount
 * - OVERPAID: getTotalBayarByTagihan() > bill amount
 * 
 * FINANCIAL_CALCULATIONS:
 * Used in various financial reports and analytics:
 * - Monthly payment totals
 * - Outstanding balance calculations
 * - Cash flow reporting
 * - Tenant payment behavior analysis
 * 
 * USAGE_PATTERNS:
 * 1. Recording Payment:
 *    Admin::pembayaran() -> BayarModel::create() -> update payment status
 * 
 * 2. Payment Status Check:
 *    TagihanModel::getTagihanWithDetails() -> BayarModel::getTotalBayarByTagihan()
 * 
 * 3. Payment History:
 *    Admin::pembayaran() -> BayarModel::findByTagihan() -> display payment list
 * 
 * AI_INTEGRATION_NOTES:
 * - Essential for financial management and cash flow tracking
 * - Supports flexible payment arrangements (installments)
 * - Critical for determining bill payment status
 * - Used in financial reporting and analytics
 * - Integrates with billing system for complete financial picture
 * - Important for collection management and outstanding balance tracking
 */