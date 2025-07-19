<?php

namespace App\Models;

use App\Core\Model;

class PenghuniModel extends Model
{
    protected $table = 'tb_penghuni';

    public function findActive()
    {
        // SQL: Mengambil semua penghuni yang masih aktif (belum keluar)
        // SELECT * FROM tb_penghuni WHERE tgl_keluar IS NULL
        // 
        // Penjelasan:
        // - tgl_keluar IS NULL: penghuni yang belum memiliki tanggal keluar
        // - Digunakan untuk menampilkan daftar penghuni yang masih tinggal di kos
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE tgl_keluar IS NULL");
    }

    public function findByKtp($no_ktp)
    {
        if (empty($no_ktp)) {
            return null;
        }
        // SQL: Mencari penghuni berdasarkan nomor KTP
        // SELECT * FROM tb_penghuni WHERE no_ktp = ?
        // 
        // Penjelasan:
        // - WHERE no_ktp = ?: filter berdasarkan nomor KTP yang unik
        // - Digunakan untuk validasi penghuni baru (mencegah duplikasi KTP)
        // - Parameter binding (:no_ktp) mencegah SQL injection
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE no_ktp = :no_ktp", ['no_ktp' => $no_ktp]);
    }

    public function checkout($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function getPenghuniWithKamar()
    {
        // SQL COMPLEX JOIN: Mengambil data penghuni beserta informasi kamar yang ditempati
        // SELECT p.*, k.nomor as nomor_kamar, k.harga as harga_kamar, 
        //        dkp.tgl_masuk as tgl_masuk_kamar, dkp.tgl_keluar as tgl_keluar_kamar,
        //        kp.id as id_kmr_penghuni
        // FROM tb_penghuni p
        // LEFT JOIN tb_detail_kmr_penghuni dkp ON p.id = dkp.id_penghuni AND dkp.tgl_keluar IS NULL
        // LEFT JOIN tb_kmr_penghuni kp ON dkp.id_kmr_penghuni = kp.id AND kp.tgl_keluar IS NULL
        // LEFT JOIN tb_kamar k ON kp.id_kamar = k.id
        // WHERE p.tgl_keluar IS NULL ORDER BY p.nama
        //
        // Penjelasan:
        // - LEFT JOIN: ambil semua penghuni, meskipun belum punya kamar
        // - tb_detail_kmr_penghuni: tabel detail hubungan penghuni-kamar
        // - tb_kmr_penghuni: tabel periode penghunian kamar
        // - tb_kamar: tabel master data kamar
        // - AND dkp.tgl_keluar IS NULL: hanya relasi yang masih aktif
        // - ORDER BY p.nama: urutkan berdasarkan nama penghuni
        $sql = "SELECT p.*, k.nomor as nomor_kamar, k.harga as harga_kamar, 
                       dkp.tgl_masuk as tgl_masuk_kamar, dkp.tgl_keluar as tgl_keluar_kamar,
                       kp.id as id_kmr_penghuni
                FROM tb_penghuni p
                LEFT JOIN tb_detail_kmr_penghuni dkp ON p.id = dkp.id_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_kmr_penghuni kp ON dkp.id_kmr_penghuni = kp.id AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE p.tgl_keluar IS NULL
                ORDER BY p.nama";
        
        return $this->db->fetchAll($sql);
    }

    public function getPenghuniAvailable()
    {
        $sql = "SELECT p.* FROM tb_penghuni p
                LEFT JOIN tb_detail_kmr_penghuni dkp ON p.id = dkp.id_penghuni AND dkp.tgl_keluar IS NULL
                WHERE p.tgl_keluar IS NULL AND dkp.id IS NULL
                ORDER BY p.nama";
        
        return $this->db->fetchAll($sql);
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: PenghuniModel
 * PURPOSE: Manages boarding house tenant/resident data and operations
 * DATABASE_TABLE: tb_penghuni
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model manages tenant information in the boarding house system. It handles
 * tenant registration, check-in/check-out processes, and tenant status tracking.
 * Tenants can be active (still living in the boarding house) or inactive (moved out).
 * 
 * CLASS_METHODS:
 * 
 * 1. findActive()
 *    PURPOSE: Get all active tenants (who haven't moved out)
 *    PARAMETERS: None
 *    RETURNS: array - List of active tenants
 *    SQL_QUERY: SELECT * FROM tb_penghuni WHERE tgl_keluar IS NULL
 *    USED_IN:
 *      - Admin::penghuni() - displaying active tenant list
 *      - Admin dashboard statistics
 *      - Tenant management interfaces
 *    AI_CONTEXT: Primary method to get current residents of the boarding house
 * 
 * 2. findByKtp($no_ktp)
 *    PURPOSE: Find tenant by their ID card number (KTP)
 *    PARAMETERS: $no_ktp: string - Indonesian ID card number
 *    RETURNS: array|null - Tenant data or null if not found
 *    SQL_QUERY: SELECT * FROM tb_penghuni WHERE no_ktp = ?
 *    USED_IN:
 *      - Tenant registration validation (prevent duplicate KTP)
 *      - Tenant search and identification
 *    AI_CONTEXT: Unique identifier validation for tenant registration
 * 
 * 3. checkout($id, $tgl_keluar)
 *    PURPOSE: Mark tenant as moved out by setting exit date
 *    PARAMETERS:
 *      - $id: int - Tenant ID
 *      - $tgl_keluar: string - Exit date (YYYY-MM-DD format)
 *    RETURNS: int - Number of affected rows
 *    SQL_QUERY: UPDATE tb_penghuni SET tgl_keluar = ? WHERE id = ?
 *    USED_IN:
 *      - Admin::penghuni() - tenant checkout process
 *      - Tenant management operations
 *    AI_CONTEXT: Marks tenant as no longer residing in the boarding house
 * 
 * 4. getPenghuniWithKamar()
 *    PURPOSE: Get tenants with their current room information
 *    PARAMETERS: None
 *    RETURNS: array - Tenants with room details
 *    SQL_QUERY: Complex LEFT JOIN between tb_penghuni, tb_detail_kmr_penghuni, 
 *               tb_kmr_penghuni, and tb_kamar tables
 *    USED_IN:
 *      - Admin dashboard - showing tenant-room relationships
 *      - Tenant management with room details
 *    AI_CONTEXT: Comprehensive view of tenants and their current room assignments
 * 
 * 5. getPenghuniWithoutKamar()
 *    PURPOSE: Get tenants who don't have room assignments yet
 *    PARAMETERS: None
 *    RETURNS: array - Tenants without rooms
 *    SQL_QUERY: LEFT JOIN to find tenants with no room relationships
 *    USED_IN:
 *      - Room assignment processes
 *      - Tenant management for unassigned tenants
 *    AI_CONTEXT: Identifies tenants who need room assignments
 * 
 * DATABASE_RELATIONSHIPS:
 * - ONE-TO-MANY with tb_detail_kmr_penghuni (tenant room details)
 * - INDIRECT relationship with tb_kamar through tb_detail_kmr_penghuni
 * - ONE-TO-MANY with tb_brng_bawaan (tenant belongings)
 * 
 * KEY_FIELDS:
 * - id: Primary key
 * - nama: Tenant name
 * - no_ktp: ID card number (unique identifier)
 * - no_hp: Phone number
 * - tgl_masuk: Entry date (when tenant first joined)
 * - tgl_keluar: Exit date (NULL for active tenants)
 * 
 * BUSINESS_RULES:
 * - Active tenants have tgl_keluar = NULL
 * - Each tenant must have unique KTP number
 * - Tenants can have multiple room assignments over time
 * - Historical data is preserved even after checkout
 * 
 * USAGE_PATTERNS:
 * 1. Tenant Registration:
 *    Admin::penghuni() -> PenghuniModel::findByKtp() -> PenghuniModel::create()
 * 
 * 2. Tenant Checkout:
 *    Admin::penghuni() -> PenghuniModel::checkout() -> DetailKamarPenghuniModel::checkout()
 * 
 * 3. Dashboard Display:
 *    Admin::index() -> PenghuniModel::getPenghuniWithKamar()
 * 
 * AI_INTEGRATION_NOTES:
 * - This model is central to the boarding house management system
 * - Tenant status (active/inactive) affects billing and room availability
 * - KTP validation is crucial for preventing duplicate registrations
 * - Room relationships are managed through separate detail tables
 * - Historical data preservation allows for reporting and analytics
 */