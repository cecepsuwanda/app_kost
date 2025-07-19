<?php

namespace App\Models;

use App\Core\Model;

class KamarModel extends Model
{
    protected $table = 'tb_kamar';

    public function findByNomor($nomor)
    {
        // SQL: Mencari kamar berdasarkan nomor kamar
        // SELECT * FROM tb_kamar WHERE nomor = ?
        // 
        // Penjelasan:
        // - WHERE nomor = ?: filter berdasarkan nomor kamar yang unik
        // - Digunakan untuk validasi kamar baru (mencegah duplikasi nomor)
        // - Parameter binding (:nomor) mencegah SQL injection
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE nomor = :nomor", ['nomor' => $nomor]);
    }

    public function getKamarKosong()
    {
        // SQL LEFT JOIN: Mencari kamar yang benar-benar kosong (tidak ada penghuni)
        // SELECT k.* FROM tb_kamar k
        // LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
        // WHERE kp.id IS NULL ORDER BY k.gedung, k.nomor
        //
        // Penjelasan:
        // - LEFT JOIN: ambil semua kamar, termasuk yang tidak punya relasi penghuni
        // - AND kp.tgl_keluar IS NULL: hanya periode penghunian yang masih aktif
        // - WHERE kp.id IS NULL: kamar yang tidak memiliki relasi aktif = kamar kosong
        // - ORDER BY gedung, nomor: urutkan berdasarkan gedung lalu nomor kamar
        $sql = "SELECT k.* FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                WHERE kp.id IS NULL
                ORDER BY k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarTersedia($max_occupants = 2)
    {
        // SQL COMPLEX WITH COUNT & CALCULATIONS: Mencari kamar yang masih tersedia (belum penuh)
        // SELECT k.*, COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
        //        (? - COALESCE(COUNT(dkp.id), 0)) as slot_tersedia
        // FROM tb_kamar k
        // LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
        // LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
        // GROUP BY k.id HAVING slot_tersedia > 0 ORDER BY k.gedung, k.nomor
        //
        // Penjelasan:
        // - COUNT(dkp.id): hitung jumlah penghuni aktif per kamar
        // - COALESCE(..., 0): jika tidak ada penghuni, set ke 0
        // - (? - COUNT(...)): hitung slot tersedia = max_occupants - jumlah_penghuni
        // - GROUP BY k.id: kelompokkan per kamar untuk menghitung COUNT
        // - HAVING slot_tersedia > 0: hanya tampilkan kamar yang masih ada slot
        // - Parameter [$max_occupants]: maksimal penghuni per kamar (default 2)
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
                       (? - COALESCE(COUNT(dkp.id), 0)) as slot_tersedia
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.id
                HAVING slot_tersedia > 0
                ORDER BY k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql, [$max_occupants]);
    }

    public function getKamarTerisi()
    {
        $sql = "SELECT k.*, 
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni,
                       COUNT(dkp.id) as jumlah_penghuni,
                       kp.tgl_masuk, kp.tgl_keluar
                FROM tb_kamar k
                INNER JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                INNER JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                GROUP BY k.id, kp.id
                ORDER BY k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarWithStatus($max_occupants = 2)
    {
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
                       CASE 
                           WHEN COUNT(dkp.id) = 0 THEN 'kosong'
                           WHEN COUNT(dkp.id) < ? THEN 'tersedia'
                           ELSE 'penuh'
                       END as status,
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni,
                       kp.tgl_masuk, kp.id as id_kmr_penghuni
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                GROUP BY k.id,kp.tgl_masuk,kp.id
                ORDER BY k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql, [$max_occupants]);
    }

    public function getKamarWithBasicStatus($max_occupants = 2)
    {
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
                       CASE 
                           WHEN COUNT(dkp.id) = 0 THEN 'kosong'
                           WHEN COUNT(dkp.id) < ? THEN 'tersedia'
                           ELSE 'penuh'
                       END as status
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.id
                ORDER BY k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql, [$max_occupants]);
    }

    public function getDetailKamar($id_kamar)
    {
        $sql = "SELECT k.*, 
                       kp.id as id_kmr_penghuni, kp.tgl_masuk as tgl_masuk_kamar,
                       dkp.id as id_detail, dkp.tgl_masuk as tgl_masuk_penghuni,
                       p.id as id_penghuni, p.nama, p.no_ktp, p.no_hp
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                WHERE k.id = :id_kamar
                ORDER BY dkp.tgl_masuk";
        
        return $this->db->fetchAll($sql, ['id_kamar' => $id_kamar]);
    }

    public function getStatistikPerGedung()
    {
        $sql = "SELECT k.gedung,
                       COUNT(k.id) as total_kamar,
                       COALESCE(COUNT(CASE WHEN dkp.id IS NOT NULL THEN 1 END), 0) as kamar_terisi,
                       COALESCE(COUNT(CASE WHEN dkp.id IS NULL THEN 1 END), 0) as kamar_kosong,
                       MIN(k.harga) as harga_terendah,
                       MAX(k.harga) as harga_tertinggi,
                       AVG(k.harga) as harga_rata_rata
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.gedung
                ORDER BY k.gedung";
        
        return $this->db->fetchAll($sql);
    }

    public function getStatistikKamarKosongPerGedung()
    {
        $sql = "SELECT k.gedung,
                       COUNT(k.id) as jumlah_tersedia,
                       MIN(k.harga) as harga_terendah,
                       MAX(k.harga) as harga_tertinggi,
                       AVG(k.harga) as harga_rata_rata
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                WHERE kp.id IS NULL
                GROUP BY k.gedung
                ORDER BY k.gedung";
        
        return $this->db->fetchAll($sql);
    }

    public function getGedungList()
    {
        $sql = "SELECT DISTINCT gedung FROM tb_kamar ORDER BY gedung";
        return $this->db->fetchAll($sql);
    }

    public function getKamarWithAllOccupantsAndBelongings($max_occupants = 2)
    {
        // First get all rooms with basic info
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(DISTINCT dkp.id), 0) as jumlah_penghuni,
                       CASE 
                           WHEN COUNT(DISTINCT dkp.id) = 0 THEN 'kosong'
                           WHEN COUNT(DISTINCT dkp.id) < ? THEN 'tersedia'
                           ELSE 'penuh'
                       END as status,
                       kp.tgl_masuk, kp.id as id_kmr_penghuni
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.id, kp.id, kp.tgl_masuk
                ORDER BY k.gedung, k.nomor";
        
        $rooms = $this->db->fetchAll($sql, [$max_occupants]);
        
        // Now get detailed occupant and belongings info for each room
        foreach ($rooms as &$room) {
            $room['penghuni_list'] = [];
            $room['nama_penghuni'] = '';
            
            if ($room['id_kmr_penghuni']) {
                // Get all occupants for this room
                $occupantSql = "SELECT dkp.*, p.nama, p.no_ktp, p.no_hp, p.id as id_penghuni
                               FROM tb_detail_kmr_penghuni dkp
                               INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                               WHERE dkp.id_kmr_penghuni = ? AND dkp.tgl_keluar IS NULL
                               ORDER BY dkp.tgl_masuk";
                
                $occupants = $this->db->fetchAll($occupantSql, [$room['id_kmr_penghuni']]);
                
                $occupantNames = [];
                foreach ($occupants as $occupant) {
                    // Get belongings for each occupant
                    $belongingsSql = "SELECT bb.*, b.nama as nama_barang, b.harga as harga_barang
                                     FROM tb_brng_bawaan bb
                                     INNER JOIN tb_barang b ON bb.id_barang = b.id
                                     WHERE bb.id_penghuni = ?";
                    
                    $belongings = $this->db->fetchAll($belongingsSql, [$occupant['id_penghuni']]);
                    $occupant['barang_bawaan'] = $belongings;
                    
                    $room['penghuni_list'][] = $occupant;
                    $occupantNames[] = $occupant['nama'];
                }
                
                $room['nama_penghuni'] = implode(', ', $occupantNames);
                
                // Collect all belongings for display
                $allBelongings = [];
                foreach ($room['penghuni_list'] as $occupant) {
                    foreach ($occupant['barang_bawaan'] as $belonging) {
                        $allBelongings[] = $belonging;
                    }
                }
                $room['barang_bawaan'] = $allBelongings;
            } else {
                $room['barang_bawaan'] = [];
            }
        }
        
        return $rooms;
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: KamarModel
 * PURPOSE: Manages boarding house room data, availability, and occupancy tracking
 * DATABASE_TABLE: tb_kamar
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model manages the physical rooms in the boarding house. It tracks room
 * information, availability status, occupancy levels, and handles room assignment
 * logic. Rooms can be empty, partially occupied, or full based on tenant capacity.
 * The system supports multiple tenants per room with configurable limits.
 * 
 * CLASS_METHODS:
 * 
 * 1. findByNomor($nomor)
 *    PURPOSE: Find room by room number
 *    PARAMETERS: $nomor: string - Room number identifier
 *    RETURNS: array|null - Room data or null if not found
 *    SQL_QUERY: SELECT * FROM tb_kamar WHERE nomor = ?
 *    USED_IN:
 *      - Room registration validation (prevent duplicate numbers)
 *      - Room search and identification
 *    AI_CONTEXT: Unique identifier validation for room management
 * 
 * 2. getKamarKosong()
 *    PURPOSE: Get completely empty rooms (no tenants at all)
 *    PARAMETERS: None
 *    RETURNS: array - List of empty rooms
 *    SQL_QUERY: LEFT JOIN with tb_kmr_penghuni to find rooms with no occupancy
 *    USED_IN:
 *      - Room assignment for new tenants
 *      - Availability displays
 *    AI_CONTEXT: Identifies rooms available for immediate occupancy
 * 
 * 3. getKamarTersedia($max_occupants = 2)
 *    PURPOSE: Get rooms with available slots (not at capacity)
 *    PARAMETERS: $max_occupants: int - Maximum tenants per room
 *    RETURNS: array - Rooms with available slots and current occupancy count
 *    SQL_QUERY: Complex COUNT calculation to determine available slots
 *    USED_IN:
 *      - Room assignment with occupancy limits
 *      - Capacity management
 *    AI_CONTEXT: Supports multi-tenant room assignments with capacity control
 * 
 * 4. getKamarTerisi()
 *    PURPOSE: Get occupied rooms with tenant details
 *    PARAMETERS: None
 *    RETURNS: array - Occupied rooms with tenant information
 *    SQL_QUERY: JOIN with tenant tables to get occupancy details
 *    USED_IN:
 *      - Occupancy reports
 *      - Tenant-room relationship displays
 *    AI_CONTEXT: Shows current room utilization status
 * 
 * 5. getKamarWithStatusAndTenants($gedung = null)
 *    PURPOSE: Comprehensive room data with status and tenant information
 *    PARAMETERS: $gedung: int - Optional building filter
 *    RETURNS: array - Complete room data with status and tenant details
 *    BUSINESS_LOGIC: Determines room status (kosong/terisi/penuh) based on occupancy
 *    USED_IN:
 *      - Admin dashboard comprehensive room view
 *      - Room management interfaces
 *    AI_CONTEXT: Primary method for complete room status overview
 * 
 * 6. getTotalKamar()
 *    PURPOSE: Get total count of all rooms
 *    PARAMETERS: None
 *    RETURNS: int - Total number of rooms
 *    USED_IN:
 *      - Dashboard statistics
 *      - Capacity planning
 *    AI_CONTEXT: Basic metric for system capacity
 * 
 * 7. getKamarTerisiCount()
 *    PURPOSE: Count rooms that have at least one tenant
 *    PARAMETERS: None
 *    RETURNS: int - Number of occupied rooms
 *    USED_IN:
 *      - Dashboard occupancy statistics
 *      - Utilization rate calculations
 *    AI_CONTEXT: Key performance indicator for occupancy rate
 * 
 * 8. getKamarKosongCount()
 *    PURPOSE: Count completely empty rooms
 *    PARAMETERS: None
 *    RETURNS: int - Number of empty rooms
 *    USED_IN:
 *      - Dashboard availability statistics
 *      - Vacancy tracking
 *    AI_CONTEXT: Availability metric for new tenant assignments
 * 
 * 9. getAvailableRooms($gedung = null)
 *    PURPOSE: Get rooms available for new tenant assignment
 *    PARAMETERS: $gedung: int - Optional building filter
 *    RETURNS: array - Rooms with available capacity
 *    BUSINESS_LOGIC: Includes both empty rooms and rooms with available slots
 *    USED_IN:
 *      - Tenant assignment processes
 *      - Room selection interfaces
 *    AI_CONTEXT: Combines multiple availability criteria for assignment logic
 * 
 * 10. getGedungList()
 *     PURPOSE: Get list of all building numbers
 *     PARAMETERS: None
 *     RETURNS: array - Distinct building numbers
 *     SQL_QUERY: SELECT DISTINCT gedung FROM tb_kamar ORDER BY gedung
 *     USED_IN:
 *       - Building filter dropdowns
 *       - Navigation and organization
 *     AI_CONTEXT: Provides building-based organization structure
 * 
 * 11. searchRooms($criteria)
 *     PURPOSE: Search rooms based on multiple criteria
 *     PARAMETERS: $criteria: array - Search parameters
 *     RETURNS: array - Filtered room results
 *     BUSINESS_LOGIC: Supports filtering by number, building, price range, status
 *     USED_IN:
 *       - Room search functionality
 *       - Advanced filtering interfaces
 *     AI_CONTEXT: Flexible search system for room management
 * 
 * ROOM_STATUS_LOGIC:
 * - KOSONG: No active tenants (tgl_keluar IS NOT NULL OR no records)
 * - TERISI: Has active tenants but below maximum capacity
 * - PENUH: At maximum tenant capacity
 * - Status calculated dynamically based on current occupancy
 * 
 * DATABASE_RELATIONSHIPS:
 * - ONE-TO-MANY with tb_kmr_penghuni (room occupancy periods)
 * - INDIRECT with tb_penghuni through tb_detail_kmr_penghuni
 * - Referenced in tb_tagihan through tb_kmr_penghuni
 * 
 * KEY_FIELDS:
 * - id: Primary key
 * - nomor: Room number (unique identifier)
 * - gedung: Building number for organization
 * - harga: Monthly rent price
 * 
 * BUSINESS_RULES:
 * - Each room has unique number within the system
 * - Rooms can accommodate multiple tenants (configurable limit)
 * - Room prices are used in billing calculations
 * - Building organization for management and navigation
 * - Historical occupancy data preserved for reporting
 * 
 * CAPACITY_MANAGEMENT:
 * - Default maximum occupancy: 2 tenants per room
 * - Configurable through method parameters
 * - Real-time availability calculation
 * - Supports partial occupancy tracking
 * 
 * AI_INTEGRATION_NOTES:
 * - This model is crucial for room assignment logic
 * - Availability calculations affect tenant placement decisions
 * - Status determination supports various UI displays
 * - Building organization enables scalable management
 * - Integrates with billing system for rent calculations
 * - Supports both simple and complex room assignment scenarios
 */