<?php

namespace App\Models;

use App\Core\Model;

class DetailKamarPenghuniModel extends Model
{
    protected $table = 'tb_detail_kmr_penghuni';

    public function findActiveByPenghuni($id_penghuni)
    {
        // SQL: Mencari detail kamar aktif untuk penghuni tertentu
        // SELECT * FROM tb_detail_kmr_penghuni WHERE id_penghuni = ? AND tgl_keluar IS NULL
        //
        // Penjelasan:
        // - WHERE id_penghuni = ?: filter untuk penghuni tertentu
        // - AND tgl_keluar IS NULL: hanya relasi yang masih aktif (belum pindah/keluar)
        // - Digunakan untuk mengetahui kamar mana yang sedang ditempati penghuni
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id_penghuni = :id_penghuni AND tgl_keluar IS NULL", 
                               ['id_penghuni' => $id_penghuni]);
    }

    public function findActiveByKamarPenghuni($id_kmr_penghuni)
    {
        // SQL JOIN: Mengambil detail penghuni aktif di kamar tertentu beserta data personalnya
        // SELECT dkp.*, p.nama, p.no_ktp, p.no_hp 
        // FROM tb_detail_kmr_penghuni dkp INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id 
        // WHERE dkp.id_kmr_penghuni = ? AND dkp.tgl_keluar IS NULL
        //
        // Penjelasan:
        // - INNER JOIN tb_penghuni: gabungkan dengan tabel penghuni untuk mendapat data personal
        // - WHERE dkp.id_kmr_penghuni = ?: filter untuk kamar tertentu
        // - AND dkp.tgl_keluar IS NULL: hanya penghuni yang masih aktif di kamar
        // - Digunakan untuk menampilkan daftar penghuni yang sedang tinggal di suatu kamar
        return $this->db->fetchAll("SELECT dkp.*, p.nama, p.no_ktp, p.no_hp FROM {$this->table} dkp INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id WHERE dkp.id_kmr_penghuni = :id_kmr_penghuni AND dkp.tgl_keluar IS NULL", 
                                   ['id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function checkoutPenghuni($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function checkoutPenghuniFromKamar($id_penghuni, $tgl_keluar)
    {
        $detailActive = $this->findActiveByPenghuni($id_penghuni);
        if ($detailActive) {
            return $this->checkoutPenghuni($detailActive['id'], $tgl_keluar);
        }
        return false;
    }

    public function getPenghuniByKamarPenghuni($id_kmr_penghuni)
    {
        $sql = "SELECT dkp.*, p.nama, p.no_ktp, p.no_hp
                FROM {$this->table} dkp
                INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                WHERE dkp.id_kmr_penghuni = :id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                ORDER BY dkp.tgl_masuk";
        
        return $this->db->fetchAll($sql, ['id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function getAllActivePenghuniWithKamar()
    {
        $sql = "SELECT dkp.*, p.nama as nama_penghuni, p.no_ktp, p.no_hp,
                       kp.id_kamar, k.nomor as nomor_kamar, k.harga as harga_kamar,
                       kp.tgl_masuk as tgl_masuk_kamar
                FROM {$this->table} dkp
                INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                INNER JOIN tb_kmr_penghuni kp ON dkp.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE dkp.tgl_keluar IS NULL AND kp.tgl_keluar IS NULL
                ORDER BY k.nomor, dkp.tgl_masuk";
        
        return $this->db->fetchAll($sql);
    }

    public function countActivePenghuniInKamar($id_kamar)
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} dkp
                INNER JOIN tb_kmr_penghuni kp ON dkp.id_kmr_penghuni = kp.id
                WHERE kp.id_kamar = :id_kamar AND dkp.tgl_keluar IS NULL AND kp.tgl_keluar IS NULL";
        
        $result = $this->db->fetch($sql, ['id_kamar' => $id_kamar]);
        return $result ? $result['total'] : 0;
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: DetailKamarPenghuniModel
 * PURPOSE: Manages detailed tenant-room relationships and occupancy tracking
 * DATABASE_TABLE: tb_detail_kmr_penghuni
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model manages the detailed relationship between tenants and rooms. It tracks
 * individual tenant assignments to specific rooms within room occupancy periods.
 * Multiple tenants can be assigned to the same room, and the model handles entry
 * and exit dates for each tenant individually. This enables multi-tenant room
 * management and detailed occupancy tracking.
 * 
 * CLASS_METHODS:
 * 
 * 1. findActiveByPenghuni($id_penghuni)
 *    PURPOSE: Find active room assignment for a specific tenant
 *    PARAMETERS: $id_penghuni: int - Tenant ID to find room assignment for
 *    RETURNS: array|null - Active room assignment details or null
 *    SQL_QUERY: SELECT * FROM tb_detail_kmr_penghuni WHERE id_penghuni = ? AND tgl_keluar IS NULL
 *    USED_IN:
 *      - Tenant checkout processes
 *      - Room assignment validation
 *      - Current tenant location tracking
 *    AI_CONTEXT: Determines which room a tenant currently occupies
 * 
 * 2. findActiveByKamarPenghuni($id_kmr_penghuni)
 *    PURPOSE: Get all active tenants in a specific room occupancy period
 *    PARAMETERS: $id_kmr_penghuni: int - Room occupancy period ID
 *    RETURNS: array - List of active tenants with personal information
 *    SQL_QUERY: Complex JOIN with tb_penghuni to get tenant details
 *    USED_IN:
 *      - Room occupancy displays
 *      - Tenant management for specific rooms
 *      - Billing calculations (multiple tenants per room)
 *    AI_CONTEXT: Shows all current occupants of a specific room
 * 
 * 3. checkoutPenghuni($id, $tgl_keluar)
 *    PURPOSE: Mark tenant as moved out from room by setting exit date
 *    PARAMETERS: 
 *      - $id: int - Detail record ID
 *      - $tgl_keluar: string - Exit date (YYYY-MM-DD)
 *    RETURNS: int - Number of affected rows
 *    BUSINESS_LOGIC: Updates tgl_keluar to mark tenant exit from room
 *    USED_IN:
 *      - Tenant checkout processes
 *      - Room assignment changes
 *    AI_CONTEXT: Handles individual tenant movement out of rooms
 * 
 * 4. assignPenghuniToRoom($id_penghuni, $id_kmr_penghuni, $tgl_masuk)
 *    PURPOSE: Assign tenant to room with entry date
 *    PARAMETERS:
 *      - $id_penghuni: int - Tenant ID
 *      - $id_kmr_penghuni: int - Room occupancy period ID
 *      - $tgl_masuk: string - Entry date
 *    RETURNS: int - New assignment ID
 *    BUSINESS_LOGIC: Creates new tenant-room assignment record
 *    USED_IN:
 *      - New tenant assignments
 *      - Tenant room changes
 *    AI_CONTEXT: Creates new tenant-room relationships
 * 
 * 5. getTotalPenghuniByKamar($id_kamar)
 *    PURPOSE: Count total active tenants in a specific room
 *    PARAMETERS: $id_kamar: int - Room ID
 *    RETURNS: int - Number of active tenants in the room
 *    SQL_QUERY: Complex COUNT with JOINs across occupancy tables
 *    USED_IN:
 *      - Room capacity calculations
 *      - Availability determinations
 *      - Occupancy rate calculations
 *    AI_CONTEXT: Determines current occupancy level for capacity management
 * 
 * DATABASE_RELATIONSHIPS:
 * - MANY-TO-ONE with tb_penghuni (tenant information)
 * - MANY-TO-ONE with tb_kmr_penghuni (room occupancy periods)
 * - INDIRECT with tb_kamar through tb_kmr_penghuni
 * 
 * KEY_FIELDS:
 * - id: Primary key
 * - id_penghuni: Foreign key to tb_penghuni (which tenant)
 * - id_kmr_penghuni: Foreign key to tb_kmr_penghuni (which room period)
 * - tgl_masuk: Entry date for this tenant-room assignment
 * - tgl_keluar: Exit date (NULL for active assignments)
 * 
 * BUSINESS_RULES:
 * - One tenant can only have one active room assignment at a time
 * - Multiple tenants can be assigned to the same room simultaneously
 * - Entry/exit dates track individual tenant movements
 * - Historical assignments preserved for audit and reporting
 * - Exit date (tgl_keluar) NULL indicates active assignment
 * 
 * OCCUPANCY_TRACKING:
 * - Active assignments: tgl_keluar IS NULL
 * - Historical assignments: tgl_keluar IS NOT NULL
 * - Room capacity determined by counting active assignments
 * - Individual tenant movement history maintained
 * 
 * INTEGRATION_POINTS:
 * - Used by billing system for determining room occupants
 * - Room availability calculations depend on this model
 * - Tenant management workflows use this for assignments
 * - Reporting and analytics use occupancy data
 * 
 * USAGE_PATTERNS:
 * 1. New Tenant Assignment:
 *    Admin::penghuni() -> DetailKamarPenghuniModel::assignPenghuniToRoom()
 * 
 * 2. Tenant Checkout:
 *    Admin::penghuni() -> DetailKamarPenghuniModel::checkoutPenghuni()
 * 
 * 3. Room Occupancy Check:
 *    KamarModel::getKamarTersedia() -> DetailKamarPenghuniModel::getTotalPenghuniByKamar()
 * 
 * 4. Billing Calculations:
 *    TagihanModel::generateTagihan() -> DetailKamarPenghuniModel::findActiveByKamarPenghuni()
 * 
 * AI_INTEGRATION_NOTES:
 * - Critical for multi-tenant room management
 * - Enables detailed occupancy tracking and history
 * - Essential for capacity management and room assignments
 * - Supports complex tenant movement scenarios
 * - Integrates with billing system for accurate charge calculations
 * - Provides foundation for occupancy analytics and reporting
 */