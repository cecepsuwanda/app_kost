<?php

namespace App\Models;

use App\Core\Model;

class TagihanModel extends Model
{
    protected $table = 'tb_tagihan';

        public function findByBulanTahun($bulan, $tahun)
    {
        // SQL: Mengambil semua tagihan berdasarkan bulan dan tahun tertentu
        // SELECT * FROM tb_tagihan WHERE bulan = ? AND tahun = ?
        // Contoh: bulan=12, tahun=2024 -> ambil semua tagihan Desember 2024
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE bulan = :bulan AND tahun = :tahun",
            ['bulan' => $bulan, 'tahun' => $tahun]);
    }

        public function findByKamarPenghuni($id_kmr_penghuni)
    {
        // SQL: Mengambil semua tagihan untuk satu kamar penghuni tertentu
        // SELECT * FROM tb_tagihan WHERE id_kmr_penghuni = ?
        // Digunakan untuk melihat riwayat tagihan suatu kamar dari waktu ke waktu
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_kmr_penghuni = :id_kmr_penghuni",
            ['id_kmr_penghuni' => $id_kmr_penghuni]);
    }

        public function findByBulanTahunKamarPenghuni($bulan, $tahun, $id_kmr_penghuni)
    {
        // SQL: Mencari tagihan spesifik untuk kamar tertentu pada bulan/tahun tertentu
        // SELECT * FROM tb_tagihan WHERE bulan = ? AND tahun = ? AND id_kmr_penghuni = ?
        // Digunakan untuk mengecek apakah tagihan sudah ada sebelum membuat yang baru
        // Mencegah duplikasi tagihan untuk kamar dan periode yang sama
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE bulan = :bulan AND tahun = :tahun AND id_kmr_penghuni = :id_kmr_penghuni",
            ['bulan' => $bulan, 'tahun' => $tahun, 'id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function generateTagihan($periode)
    {
        // Parse periode (format: YYYY-MM) to extract bulan and tahun
        $date = date_create_from_format('Y-m', $periode);
        if (!$date) {
            throw new \InvalidArgumentException("Invalid periode format. Expected YYYY-MM");
        }
        
        $bulan = (int)$date->format('n'); // 1-12
        $tahun = (int)$date->format('Y'); // YYYY

        // Validasi periode - hanya bisa generate bulan sekarang dan bulan berikutnya
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        // Hitung selisih bulan dari bulan sekarang
        $monthDiff = ($tahun - $currentYear) * 12 + ($bulan - $currentMonth);
        
        // Hanya boleh generate untuk bulan sekarang (0) atau bulan berikutnya (1)
        if ($monthDiff < 0) {
            throw new \InvalidArgumentException("Tidak bisa generate tagihan untuk bulan yang sudah lewat");
        }
        
        if ($monthDiff > 1) {
            throw new \InvalidArgumentException("Tidak bisa generate tagihan untuk bulan yang terlalu jauh ke depan");
        }

        // SQL JOIN: Mengambil data kamar yang aktif beserta harga kamarnya
        // SELECT kp.id, kp.id_kamar, kp.tgl_masuk, k.harga as harga_kamar
        // FROM tb_kmr_penghuni kp INNER JOIN tb_kamar k ON kp.id_kamar = k.id
        // WHERE kp.tgl_keluar IS NULL GROUP BY ...
        // 
        // Penjelasan:
        // - tb_kmr_penghuni: tabel hubungan kamar dengan periode penghunian
        // - INNER JOIN tb_kamar: gabungkan dengan data kamar untuk mendapat harga
        // - WHERE tgl_keluar IS NULL: hanya kamar yang masih aktif (belum checkout)
        // - GROUP BY: hindari duplikasi data kamar yang sama
        $sql = "SELECT kp.id, kp.id_kamar, kp.tgl_masuk, k.harga as harga_kamar
                FROM tb_kmr_penghuni kp
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE kp.tgl_keluar IS NULL
                GROUP BY kp.id,kp.id_kamar, kp.tgl_masuk, k.harga";
        
        $activeKamarList = $this->db->fetchAll($sql);

        $generated = 0;
        foreach ($activeKamarList as $kamar) {
            // Check if tagihan already exists for this month, year, and kamar_penghuni
            $existing = $this->findByBulanTahunKamarPenghuni($bulan, $tahun, $kamar['id']);
            if ($existing) {
                continue; // Skip if already generated
            }

            // SQL: Mengambil semua penghuni yang masih aktif di kamar ini
            // SELECT id_penghuni FROM tb_detail_kmr_penghuni WHERE id_kmr_penghuni = ? AND tgl_keluar IS NULL
            // 
            // Penjelasan:
            // - tb_detail_kmr_penghuni: tabel detail yang menyimpan penghuni per kamar
            // - WHERE id_kmr_penghuni: filter untuk kamar tertentu
            // - AND tgl_keluar IS NULL: hanya penghuni yang masih tinggal (belum pindah/keluar)
            $penghuniSql = "SELECT id_penghuni FROM tb_detail_kmr_penghuni 
                           WHERE id_kmr_penghuni = :id_kmr_penghuni AND tgl_keluar IS NULL";
            $penghuniList = $this->db->fetchAll($penghuniSql, ['id_kmr_penghuni' => $kamar['id']]);
            
            if (empty($penghuniList)) {
                continue; // Skip if no active penghuni
            }
            
            // Use room occupancy entry date (tgl_masuk from tb_kmr_penghuni)
            $tglMasukKamarPenghuni = $kamar['tgl_masuk'];
            
            // SQL COALESCE & SUM: Menghitung total harga barang bawaan semua penghuni di kamar ini
            $totalHargaBarang = 0;
            foreach ($penghuniList as $penghuni) {
                // SELECT COALESCE(SUM(b.harga), 0) as total_harga
                // FROM tb_brng_bawaan bb INNER JOIN tb_barang b ON bb.id_barang = b.id
                // WHERE bb.id_penghuni = ?
                //
                // Penjelasan:
                // - tb_brng_bawaan: tabel relasi penghuni dengan barang yang dibawa
                // - INNER JOIN tb_barang: gabung untuk mendapat harga barang
                // - SUM(b.harga): jumlahkan semua harga barang
                // - COALESCE(..., 0): jika tidak ada barang, return 0 (bukan NULL)
                // - WHERE bb.id_penghuni: filter untuk penghuni tertentu
                $barangSql = "SELECT COALESCE(SUM(b.harga), 0) as total_harga
                             FROM tb_brng_bawaan bb
                             INNER JOIN tb_barang b ON bb.id_barang = b.id
                             WHERE bb.id_penghuni = :id_penghuni";
                $barangResult = $this->db->fetch($barangSql, ['id_penghuni' => $penghuni['id_penghuni']]);
                $totalHargaBarang += $barangResult['total_harga'] ?? 0;
            }
            
            $totalTagihan = $kamar['harga_kamar'] + $totalHargaBarang;

            // Calculate tanggal tagihan using room occupancy entry date
            $tanggalMasukKamarPenghuniDay = date('d', strtotime($tglMasukKamarPenghuni));
            
            // Validate if the date is valid for the target month/year
            // Keep subtracting one day until we get a valid date
            while (!checkdate($bulan, $tanggalMasukKamarPenghuniDay, $tahun)) {
                $tanggalMasukKamarPenghuniDay = $tanggalMasukKamarPenghuniDay - 1;
                // Safety check to prevent infinite loop (should never reach 0)
                if ($tanggalMasukKamarPenghuniDay < 1) {
                    $tanggalMasukKamarPenghuniDay = 1;
                    break;
                }
            }
            
            $tanggalTagihan = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tanggalMasukKamarPenghuniDay);
            
            // Create tagihan (satu tagihan per kamar)
            $this->create([
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggal' => $tanggalTagihan,
                'id_kmr_penghuni' => $kamar['id'],
                'jml_tagihan' => $totalTagihan
            ]);

            $generated++;
        }

        return $generated;
    }

    public function recalculateTagihan($id_tagihan)
    {
        // Get tagihan details
        $tagihan = $this->findAll($id_tagihan);
        if (!$tagihan) {
            return false;
        }

        // Validasi periode - hanya bisa recalculate bulan sekarang dan bulan berikutnya
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        // Hitung selisih bulan dari bulan sekarang
        $monthDiff = ($tagihan['tahun'] - $currentYear) * 12 + ($tagihan['bulan'] - $currentMonth);
        
        // Hanya boleh recalculate untuk bulan sekarang (0) atau bulan berikutnya (1)
        if ($monthDiff < 0) {
            throw new \InvalidArgumentException("Tidak bisa recalculate tagihan untuk bulan yang sudah lewat");
        }
        
        if ($monthDiff > 1) {
            throw new \InvalidArgumentException("Tidak bisa recalculate tagihan untuk bulan yang terlalu jauh ke depan");
        }

        // Get kamar penghuni and kamar details via direct SQL join
        $kamarSql = "SELECT kp.id, kp.id_kamar, k.harga 
                     FROM tb_kmr_penghuni kp 
                     INNER JOIN tb_kamar k ON kp.id_kamar = k.id 
                     WHERE kp.id = :id_kmr_penghuni";
        $kamarData = $this->db->fetch($kamarSql, ['id_kmr_penghuni' => $tagihan['id_kmr_penghuni']]);
        
        if (!$kamarData) {
            return false;
        }

        // Get all active penghuni in this kamar
        $penghuniSql = "SELECT id_penghuni FROM tb_detail_kmr_penghuni 
                       WHERE id_kmr_penghuni = :id_kmr_penghuni AND tgl_keluar IS NULL";
        $penghuniList = $this->db->fetchAll($penghuniSql, ['id_kmr_penghuni' => $tagihan['id_kmr_penghuni']]);
        
        // Calculate total barang bawaan for all penghuni in this kamar via direct SQL
        $totalHargaBarang = 0;
        foreach ($penghuniList as $penghuni) {
            $barangSql = "SELECT COALESCE(SUM(b.harga), 0) as total_harga
                         FROM tb_brng_bawaan bb
                         INNER JOIN tb_barang b ON bb.id_barang = b.id
                         WHERE bb.id_penghuni = :id_penghuni";
            $barangResult = $this->db->fetch($barangSql, ['id_penghuni' => $penghuni['id_penghuni']]);
            $totalHargaBarang += $barangResult['total_harga'] ?? 0;
        }
        
        $newTotalTagihan = $kamarData['harga'] + $totalHargaBarang;

        // Update tagihan with new calculated amount
        $result = $this->update($id_tagihan, [
            'jml_tagihan' => $newTotalTagihan
        ]);

        return $result ? $newTotalTagihan : false;
    }

    public function recalculateAllTagihan($periode)
    {
        // Parse periode (format: YYYY-MM) to extract bulan and tahun
        $date = date_create_from_format('Y-m', $periode);
        if (!$date) {
            throw new \InvalidArgumentException("Invalid periode format. Expected YYYY-MM");
        }
        
        $bulan = (int)$date->format('n'); // 1-12
        $tahun = (int)$date->format('Y'); // YYYY

        // Validasi periode - hanya bisa recalculate bulan sekarang dan bulan berikutnya
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        // Hitung selisih bulan dari bulan sekarang
        $monthDiff = ($tahun - $currentYear) * 12 + ($bulan - $currentMonth);
        
        // Hanya boleh recalculate untuk bulan sekarang (0) atau bulan berikutnya (1)
        if ($monthDiff < 0) {
            throw new \InvalidArgumentException("Tidak bisa recalculate tagihan untuk bulan yang sudah lewat");
        }
        
        if ($monthDiff > 1) {
            throw new \InvalidArgumentException("Tidak bisa recalculate tagihan untuk bulan yang terlalu jauh ke depan");
        }

        // Get all tagihan for the specified periode
        $allTagihan = $this->findByBulanTahun($bulan, $tahun);
        
        $recalculated = 0;
        foreach ($allTagihan as $tagihan) {
            $result = $this->recalculateTagihan($tagihan['id']);
            if ($result !== false) {
                $recalculated++;
            }
        }

        return $recalculated;
    }

    public function getTagihanDetail($periode = null)
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

        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar,
                       GROUP_CONCAT(DISTINCT p.nama SEPARATOR ', ') as nama_penghuni,
                       GROUP_CONCAT(DISTINCT p.no_hp SEPARATOR ', ') as no_hp,
                       GROUP_CONCAT(DISTINCT p.tgl_masuk SEPARATOR ', ') as tgl_masuk_penghuni,
                       k.nomor as nomor_kamar, k.gedung, k.harga as harga_kamar,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar,
                       DATEDIFF(CURDATE(), t.tanggal) as selisih_hari,
                       DATEDIFF(t.tanggal, kp.tgl_masuk) as selisih_dari_tgl_masuk_kamar_penghuni,
                       CASE 
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) > 0 THEN 'Cicil'
                           ELSE 'Belum Bayar'
                       END as status_bayar,
                       CASE 
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'lunas'
                           WHEN DATEDIFF(CURDATE(), t.tanggal) < 0 THEN 'terlambat'
                           WHEN DATEDIFF(CURDATE(), t.tanggal) BETWEEN 0 AND 3 THEN 'mendekati'
                           ELSE 'normal'
                       END as status_waktu
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan
                " . $whereCondition . "
                GROUP BY t.id
                ORDER BY t.tahun DESC, t.bulan DESC, k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getTagihanTerlambat()
    {
        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar, 
                       GROUP_CONCAT(DISTINCT p.nama SEPARATOR ', ') as nama_penghuni, 
                       GROUP_CONCAT(DISTINCT p.no_hp SEPARATOR ', ') as no_hp,
                       GROUP_CONCAT(DISTINCT p.tgl_masuk SEPARATOR ', ') as tgl_masuk_penghuni,
                       k.nomor as nomor_kamar, k.gedung,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar,
                       DATEDIFF(t.tanggal,CURDATE()) as selisih_hari,
                       DATEDIFF(t.tanggal, kp.tgl_masuk) as selisih_dari_tgl_masuk_kamar_penghuni
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan                
                WHERE DATEDIFF(t.tanggal,CURDATE()) < 0
                GROUP BY t.id
                HAVING COALESCE(SUM(byr.jml_bayar), 0) < t.jml_tagihan
                ORDER BY t.tanggal DESC, k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getTagihanMendekatiJatuhTempo()
    {
        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar, 
                       GROUP_CONCAT(DISTINCT p.nama SEPARATOR ', ') as nama_penghuni, 
                       GROUP_CONCAT(DISTINCT p.no_hp SEPARATOR ', ') as no_hp,
                       GROUP_CONCAT(DISTINCT p.tgl_masuk SEPARATOR ', ') as tgl_masuk_penghuni,
                       k.nomor as nomor_kamar, k.gedung,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar,
                       DATEDIFF(t.tanggal,CURDATE()) as selisih_hari,
                       DATEDIFF(t.tanggal, kp.tgl_masuk) as selisih_dari_tgl_masuk_kamar_penghuni
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan                
                WHERE DATEDIFF(t.tanggal,CURDATE()) BETWEEN 0 AND 3
                GROUP BY t.id
                HAVING COALESCE(SUM(byr.jml_bayar), 0) < t.jml_tagihan
                ORDER BY t.tanggal DESC, k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getTotalTagihanPerGedung($periode = null)
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
                       COUNT(t.id) as jumlah_tagihan,
                       SUM(t.jml_tagihan) as total_tagihan,
                       SUM(COALESCE(byr.total_bayar, 0)) as total_dibayar,
                       SUM(t.jml_tagihan) - SUM(COALESCE(byr.total_bayar, 0)) as sisa_tagihan
                FROM tb_tagihan t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN (
                    SELECT id_tagihan, SUM(jml_bayar) as total_bayar
                    FROM tb_bayar 
                    GROUP BY id_tagihan
                ) byr ON t.id = byr.id_tagihan
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
 * CLASS: TagihanModel
 * PURPOSE: Manages billing/invoice system for boarding house monthly charges
 * DATABASE_TABLE: tb_tagihan
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model handles the complex billing system for the boarding house. It generates
 * monthly bills for each room based on room rent plus tenant belongings costs.
 * The billing system supports multiple tenants per room and tracks payment status.
 * Bills are generated monthly and include both room charges and additional costs.
 * 
 * CLASS_METHODS:
 * 
 * 1. findByBulanTahun($bulan, $tahun)
 *    PURPOSE: Get all bills for a specific month and year
 *    PARAMETERS: $bulan: int (1-12), $tahun: int (YYYY)
 *    RETURNS: array - List of bills for the period
 *    SQL_QUERY: SELECT * FROM tb_tagihan WHERE bulan = ? AND tahun = ?
 *    USED_IN:
 *      - Admin::tagihan() - monthly bill display
 *      - Billing reports and analytics
 *    AI_CONTEXT: Primary method for monthly billing management
 * 
 * 2. findByKamarPenghuni($id_kmr_penghuni)
 *    PURPOSE: Get billing history for a specific room occupancy
 *    PARAMETERS: $id_kmr_penghuni: int - Room occupancy ID
 *    RETURNS: array - All bills for this room occupancy
 *    SQL_QUERY: SELECT * FROM tb_tagihan WHERE id_kmr_penghuni = ?
 *    USED_IN:
 *      - Room billing history
 *      - Tenant billing analysis
 *    AI_CONTEXT: Tracks billing history per room occupancy period
 * 
 * 3. findByBulanTahunKamarPenghuni($bulan, $tahun, $id_kmr_penghuni)
 *    PURPOSE: Check if bill exists for specific room and period
 *    PARAMETERS: $bulan, $tahun, $id_kmr_penghuni
 *    RETURNS: array|null - Specific bill or null
 *    SQL_QUERY: SELECT * WHERE bulan = ? AND tahun = ? AND id_kmr_penghuni = ?
 *    USED_IN:
 *      - Bill generation validation (prevent duplicates)
 *      - Billing verification processes
 *    AI_CONTEXT: Prevents duplicate bill generation for same period
 * 
 * 4. generateTagihan($periode)
 *    PURPOSE: Generate bills for all active rooms for a given period
 *    PARAMETERS: $periode: string - Format "YYYY-MM"
 *    RETURNS: int - Number of bills generated
 *    BUSINESS_LOGIC:
 *      - Gets all active room occupancies
 *      - Calculates room rent + tenant belongings costs
 *      - Creates bills based on room entry date
 *      - Validates period constraints (current/next month only)
 *    USED_IN:
 *      - Admin::tagihan() - monthly bill generation
 *    AI_CONTEXT: Core business logic for automated billing system
 * 
 * 5. getTagihanWithDetails($bulan = null, $tahun = null)
 *    PURPOSE: Get bills with room and tenant information
 *    PARAMETERS: Optional month/year filters
 *    RETURNS: array - Bills with detailed information
 *    SQL_QUERY: Complex JOIN across multiple tables for complete bill details
 *    USED_IN:
 *      - Admin::tagihan() - detailed bill display
 *      - Billing reports with full context
 *    AI_CONTEXT: Comprehensive billing view with all related data
 * 
 * 6. getTagihanTerlambat($batasHari = 5)
 *    PURPOSE: Get overdue bills based on days threshold
 *    PARAMETERS: $batasHari: int - Days threshold for overdue status
 *    RETURNS: array - Overdue bills with tenant details
 *    BUSINESS_LOGIC: Calculates days between bill date and current date
 *    USED_IN:
 *      - Admin dashboard - overdue bill alerts
 *      - Collection management
 *    AI_CONTEXT: Identifies bills requiring immediate attention
 * 
 * 7. getStatistikTagihan($bulan = null, $tahun = null)
 *    PURPOSE: Generate billing statistics and analytics
 *    PARAMETERS: Optional month/year for specific period
 *    RETURNS: array - Statistical data about bills
 *    CALCULATIONS: Total bills, amounts, payment rates, etc.
 *    USED_IN:
 *      - Admin dashboard statistics
 *      - Financial reporting
 *    AI_CONTEXT: Business intelligence for billing performance
 * 
 * 8. getStatistikPerGedung($bulan = null, $tahun = null)
 *    PURPOSE: Get billing statistics grouped by building
 *    PARAMETERS: Optional month/year filters
 *    RETURNS: array - Statistics per building
 *    USED_IN:
 *      - Building-level financial analysis
 *      - Performance comparison between buildings
 *    AI_CONTEXT: Geographic/building-based billing analytics
 * 
 * COMPLEX_BUSINESS_LOGIC:
 * 
 * Bill Generation Process:
 * 1. Validate period (only current/next month allowed)
 * 2. Get all active room occupancies with room prices
 * 3. For each room:
 *    - Get all active tenants in the room
 *    - Calculate total belongings cost for all tenants
 *    - Set bill date based on room entry date
 *    - Create single bill per room (not per tenant)
 * 4. Prevent duplicate bills for same period
 * 
 * DATABASE_RELATIONSHIPS:
 * - MANY-TO-ONE with tb_kmr_penghuni (room occupancy periods)
 * - ONE-TO-MANY with tb_bayar (payments for this bill)
 * - INDIRECT with tb_kamar through tb_kmr_penghuni
 * - INDIRECT with tb_penghuni through tb_detail_kmr_penghuni
 * 
 * KEY_FIELDS:
 * - id: Primary key
 * - bulan: Bill month (1-12)
 * - tahun: Bill year (YYYY)
 * - tanggal: Bill due date
 * - id_kmr_penghuni: Room occupancy period reference
 * - jml_tagihan: Total bill amount (room + belongings)
 * 
 * BUSINESS_RULES:
 * - One bill per room per month (not per tenant)
 * - Bill amount = room price + sum of all tenants' belongings
 * - Bill date follows room entry date pattern
 * - Only current and next month bills can be generated
 * - Historical bills are preserved for audit trail
 * 
 * PAYMENT_INTEGRATION:
 * - Bills can have multiple payments (installments)
 * - Payment status calculated from tb_bayar table
 * - Supports partial payments and payment tracking
 * 
 * AI_INTEGRATION_NOTES:
 * - This model contains the most complex business logic in the system
 * - Bill generation involves multiple table calculations
 * - Critical for financial operations and cash flow management
 * - Integrates room management, tenant management, and payment systems
 * - Supports various reporting and analytics requirements
 * - Handles edge cases like month-end dates and leap years
 */