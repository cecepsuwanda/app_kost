<?php

namespace App\Models;

use App\Core\Model;

class KamarPenghuniModel extends Model
{
    protected $table = 'tb_kmr_penghuni';

    public function findActiveByKamar($id_kamar)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id_kamar = :id_kamar AND tgl_keluar IS NULL", 
                               ['id_kamar' => $id_kamar]);
    }

    public function findKamarByPenghuni($id_penghuni)
    {
        $sql = "SELECT kp.* FROM {$this->table} kp
                INNER JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni
                WHERE dkp.id_penghuni = :id_penghuni AND dkp.tgl_keluar IS NULL AND kp.tgl_keluar IS NULL";
        
        return $this->db->fetch($sql, ['id_penghuni' => $id_penghuni]);
    }

    public function checkoutKamar($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function createKamarPenghuni($id_kamar, $tgl_masuk)
    {
        // Create main kamar penghuni record
        $id_kmr_penghuni = $this->create([
            'id_kamar' => $id_kamar,
            'tgl_masuk' => $tgl_masuk
        ]);

        return $id_kmr_penghuni;
    }

    // New method to handle the creation without model dependency
    // The controller should handle the detail records creation separately
    public function createKamarPenghuniWithDetails($id_kamar, $tgl_masuk, $penghuni_ids, $detailKamarPenghuniModel)
    {
        // Create main kamar penghuni record
        $id_kmr_penghuni = $this->createKamarPenghuni($id_kamar, $tgl_masuk);

        // Create detail records for each penghuni using injected model
        foreach ($penghuni_ids as $id_penghuni) {
            $detailKamarPenghuniModel->create([
                'id_kmr_penghuni' => $id_kmr_penghuni,
                'id_penghuni' => $id_penghuni,
                'tgl_masuk' => $tgl_masuk
            ]);
        }

        return $id_kmr_penghuni;
    }

    public function addPenghuniToKamar($id_kmr_penghuni, $id_penghuni, $tgl_masuk, $detailKamarPenghuniModel)
    {
        return $detailKamarPenghuniModel->create([
            'id_kmr_penghuni' => $id_kmr_penghuni,
            'id_penghuni' => $id_penghuni,
            'tgl_masuk' => $tgl_masuk
        ]);
    }

    public function pindahKamar($id_penghuni, $id_kamar_baru, $tgl_pindah, $detailKamarPenghuniModel)
    {
        // Get original room entry date before checkout
        $kamarPenghuniLama = $this->findKamarByPenghuni($id_penghuni);
        $tgl_masuk_kamar_asli = $kamarPenghuniLama ? $kamarPenghuniLama['tgl_masuk'] : $tgl_pindah;
        
        // Checkout dari kamar lama
        $detailKamarPenghuniModel->checkoutPenghuniFromKamar($id_penghuni, $tgl_pindah);

        // Check if original room becomes empty and close it
        if ($kamarPenghuniLama) {
            $remainingPenghuni = $detailKamarPenghuniModel->findActiveByKamarPenghuni($kamarPenghuniLama['id']);
            if (empty($remainingPenghuni)) {
                $this->checkoutKamar($kamarPenghuniLama['id'], $tgl_pindah);
            }
        }

        // Cek apakah kamar baru sudah ada entry aktif
        $kamarPenghuniAktif = $this->findActiveByKamar($id_kamar_baru);
        
        if ($kamarPenghuniAktif) {
            // Tambahkan ke kamar yang sudah ada
            return $this->addPenghuniToKamar($kamarPenghuniAktif['id'], $id_penghuni, $tgl_pindah, $detailKamarPenghuniModel);
        } else {
            // Buat entry kamar baru dengan tanggal masuk yang sama dengan kamar asli
            // untuk menjaga konsistensi billing cycle
            return $this->createKamarPenghuniForTransfer($id_kamar_baru, $tgl_masuk_kamar_asli, $id_penghuni, $tgl_pindah, $detailKamarPenghuniModel);
        }
    }

    public function createKamarPenghuniForTransfer($id_kamar, $tgl_masuk_kamar, $id_penghuni, $tgl_pindah, $detailKamarPenghuniModel)
    {
        // Create main kamar penghuni record with original room entry date
        $id_kmr_penghuni = $this->create([
            'id_kamar' => $id_kamar,
            'tgl_masuk' => $tgl_masuk_kamar
        ]);

        // Create detail record with move date for the occupant
        $detailKamarPenghuniModel->create([
            'id_kmr_penghuni' => $id_kmr_penghuni,
            'id_penghuni' => $id_penghuni,
            'tgl_masuk' => $tgl_pindah
        ]);

        return $id_kmr_penghuni;
    }

    public function getPenghuniKamarActive()
    {
        $sql = "SELECT kp.*, k.nomor as nomor_kamar, k.harga as harga_kamar,
                       GROUP_CONCAT(CONCAT(p.nama, ' (', COALESCE(p.no_ktp, 'No KTP'), ')') SEPARATOR ', ') as penghuni_list,
                       COUNT(dkp.id) as jumlah_penghuni
                FROM {$this->table} kp
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                WHERE kp.tgl_keluar IS NULL
                GROUP BY kp.id
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarSewaanMendekatiJatuhTempo($days = 30)
    {
        $sql = "SELECT kp.id as id_kmr_penghuni, 
                       kp.tgl_masuk, kp.tgl_keluar,
                       k.id as id_kamar, k.nomor as nomor_kamar, k.gedung, k.harga,
                       GROUP_CONCAT(DISTINCT p.nama ORDER BY p.nama SEPARATOR ', ') as nama_penghuni,
                       GROUP_CONCAT(DISTINCT p.id ORDER BY p.nama SEPARATOR ',') as id_penghuni,
                       GROUP_CONCAT(DISTINCT p.no_hp ORDER BY p.nama SEPARATOR ', ') as no_hp,
                       t.id as id_tagihan, t.tanggal as tanggal_tagihan,
                       t.bulan, t.tahun, t.jml_tagihan,
                       DATEDIFF(t.tanggal, CURDATE()) as hari_tersisa,
                       COUNT(DISTINCT p.id) as jumlah_penghuni
                FROM {$this->table} kp
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                INNER JOIN tb_tagihan t ON kp.id = t.id_kmr_penghuni
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                WHERE kp.tgl_keluar IS NULL
                AND t.tanggal IS NOT NULL
                AND DATEDIFF(t.tanggal, CURDATE()) BETWEEN 0 AND :days
                GROUP BY kp.id, k.id, k.nomor, k.gedung, k.harga, t.id, t.tanggal, t.bulan, t.tahun, t.jml_tagihan, kp.tgl_masuk, kp.tgl_keluar
                HAVING COUNT(DISTINCT p.id) > 0
                ORDER BY hari_tersisa ASC, k.nomor";
        
        return $this->db->fetchAll($sql, ['days' => $days]);
    }

    public function checkKamarCapacity($id_kamar, $max_occupants = 2, $detailKamarPenghuniModel = null)
    {
        if (!$detailKamarPenghuniModel) {
            throw new \InvalidArgumentException("DetailKamarPenghuniModel must be provided by controller");
        }
        $current_count = $detailKamarPenghuniModel->countActivePenghuniInKamar($id_kamar);
        return $current_count < $max_occupants;
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: KamarPenghuniModel
 * PURPOSE: Manages room occupancy periods and room-tenant lifecycle management
 * DATABASE_TABLE: tb_kmr_penghuni
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model manages room occupancy periods - the lifecycle of room usage from
 * initial occupancy to final checkout. Each record represents a continuous period
 * when a room is occupied, regardless of tenant changes within that period.
 * This enables billing per room period and tracks room utilization over time.
 * Multiple tenants can be associated with one room period through DetailKamarPenghuniModel.
 * 
 * CLASS_METHODS:
 * 
 * 1. findActiveByKamar($id_kamar)
 *    PURPOSE: Get current active occupancy period for a specific room
 *    PARAMETERS: $id_kamar: int - Room ID to find active period for
 *    RETURNS: array|null - Active occupancy period or null if room is empty
 *    SQL_QUERY: SELECT * FROM tb_kmr_penghuni WHERE id_kamar = ? AND tgl_keluar IS NULL
 *    USED_IN:
 *      - Room assignment validation
 *      - Billing calculations (bills are per room period)
 *      - Room status determination
 *    AI_CONTEXT: Determines if room is currently occupied and gets period info
 * 
 * 2. getCurrentOccupancy()
 *    PURPOSE: Get all currently active room occupancy periods
 *    PARAMETERS: None
 *    RETURNS: array - List of all active occupancy periods with room details
 *    SQL_QUERY: Complex JOIN to get occupancy with tenant information
 *    USED_IN:
 *      - Dashboard occupancy displays
 *      - System-wide occupancy reporting
 *      - Bulk operations on active rooms
 *    AI_CONTEXT: System-wide view of current room utilization
 * 
 * 3. checkout($id, $tgl_keluar)
 *    PURPOSE: End a room occupancy period by setting exit date
 *    PARAMETERS:
 *      - $id: int - Room period ID to end
 *      - $tgl_keluar: string - Exit date (YYYY-MM-DD)
 *    RETURNS: int - Number of affected rows
 *    BUSINESS_LOGIC: Ends the room period when all tenants have left
 *    USED_IN:
 *      - Room checkout processes
 *      - Room period management
 *    AI_CONTEXT: Finalizes room occupancy period for billing and history
 * 
 * 4. createRoomOccupancy($id_kamar, $tgl_masuk)
 *    PURPOSE: Start new room occupancy period
 *    PARAMETERS:
 *      - $id_kamar: int - Room ID to start occupancy for
 *      - $tgl_masuk: string - Start date for occupancy
 *    RETURNS: int - New occupancy period ID
 *    BUSINESS_LOGIC: Creates new room period when first tenant moves in
 *    USED_IN:
 *      - New tenant assignments to empty rooms
 *      - Room initialization processes
 *    AI_CONTEXT: Initiates new room occupancy lifecycle
 * 
 * 5. getRoomOccupancyHistory($id_kamar)
 *    PURPOSE: Get historical occupancy periods for a room
 *    PARAMETERS: $id_kamar: int - Room ID to get history for
 *    RETURNS: array - All occupancy periods (active and historical)
 *    USED_IN:
 *      - Room utilization analysis
 *      - Historical reporting
 *      - Room performance analytics
 *    AI_CONTEXT: Complete room usage history for analytics
 * 
 * 6. getActiveRoomsCount()
 *    PURPOSE: Count total number of rooms currently occupied
 *    PARAMETERS: None
 *    RETURNS: int - Number of rooms with active occupancy periods
 *    USED_IN:
 *      - Dashboard statistics
 *      - Occupancy rate calculations
 *    AI_CONTEXT: Key metric for system utilization
 * 
 * 7. canAccommodateMoreTenants($id_kamar, $max_occupants = 2)
 *    PURPOSE: Check if room can accommodate additional tenants
 *    PARAMETERS:
 *      - $id_kamar: int - Room ID to check
 *      - $max_occupants: int - Maximum tenants per room
 *    RETURNS: bool - True if room has available capacity
 *    BUSINESS_LOGIC: Validates room capacity before tenant assignment
 *    USED_IN:
 *      - Tenant assignment validation
 *      - Room availability calculations
 *    AI_CONTEXT: Capacity management for multi-tenant rooms
 * 
 * DATABASE_RELATIONSHIPS:
 * - MANY-TO-ONE with tb_kamar (which room this period belongs to)
 * - ONE-TO-MANY with tb_detail_kmr_penghuni (tenants in this period)
 * - ONE-TO-MANY with tb_tagihan (bills for this room period)
 * 
 * KEY_FIELDS:
 * - id: Primary key
 * - id_kamar: Foreign key to tb_kamar (which room)
 * - tgl_masuk: Occupancy period start date
 * - tgl_keluar: Occupancy period end date (NULL for active periods)
 * 
 * BUSINESS_RULES:
 * - One active occupancy period per room maximum
 * - Occupancy periods track room usage lifecycle
 * - Billing is calculated per room period, not per tenant
 * - Period ends when all tenants have left the room
 * - Historical periods preserved for reporting and analytics
 * 
 * BILLING_INTEGRATION:
 * - Bills (tb_tagihan) are generated per room occupancy period
 * - Room period ID used as foreign key in billing
 * - Period dates affect billing calculation timing
 * - Critical for automated monthly billing generation
 * 
 * USAGE_PATTERNS:
 * 1. New Room Occupancy:
 *    Admin::penghuni() -> KamarPenghuniModel::createRoomOccupancy()
 * 
 * 2. Billing Generation:
 *    TagihanModel::generateTagihan() -> KamarPenghuniModel::getCurrentOccupancy()
 * 
 * 3. Room Checkout:
 *    Admin::penghuni() -> KamarPenghuniModel::checkout()
 * 
 * 4. Capacity Checking:
 *    Admin::penghuni() -> KamarPenghuniModel::canAccommodateMoreTenants()
 * 
 * AI_INTEGRATION_NOTES:
 * - Central to room lifecycle management and billing system
 * - Enables period-based billing rather than tenant-based
 * - Critical for room utilization tracking and analytics
 * - Supports multi-tenant room management scenarios
 * - Important for capacity planning and occupancy optimization
 * - Provides foundation for room performance analysis
 */