<?php

namespace App\Models;

use App\Core\Model;

class BarangBawaanModel extends Model
{
    protected $table = 'tb_brng_bawaan';

    public function findByPenghuni($id_penghuni)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_penghuni = :id_penghuni", 
                                 ['id_penghuni' => $id_penghuni]);
    }

    public function findByBarang($id_barang)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_barang = :id_barang", 
                                 ['id_barang' => $id_barang]);
    }

    public function findByPenghuniBarang($id_penghuni, $id_barang)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id_penghuni = :id_penghuni AND id_barang = :id_barang", 
                               ['id_penghuni' => $id_penghuni, 'id_barang' => $id_barang]);
    }

    public function removeBarangFromPenghuni($id_penghuni, $id_barang)
    {
        return $this->db->delete($this->table, 'id_penghuni = :id_penghuni AND id_barang = :id_barang', 
                                ['id_penghuni' => $id_penghuni, 'id_barang' => $id_barang]);
    }

    public function getPenghuniBarangDetail($id_penghuni)
    {
        $sql = "SELECT bb.*, b.nama as nama_barang, b.harga as harga_barang
                FROM {$this->table} bb
                INNER JOIN tb_barang b ON bb.id_barang = b.id
                WHERE bb.id_penghuni = :id_penghuni";
        
        return $this->db->fetchAll($sql, ['id_penghuni' => $id_penghuni]);
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: BarangBawaanModel
 * PURPOSE: Manages tenant belongings/items relationship for billing calculations
 * DATABASE_TABLE: tb_brng_bawaan
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model manages the relationship between tenants and the items/belongings they
 * bring to the boarding house. Each item has an associated cost that gets added to
 * the tenant's monthly bill. This enables additional billing beyond basic room rent
 * for items like motorcycles, bicycles, or other belongings that require space or
 * services. The model supports many-to-many relationships between tenants and items.
 * 
 * CLASS_METHODS:
 * 
 * 1. findByPenghuni($id_penghuni)
 *    PURPOSE: Get all belongings/items for a specific tenant
 *    PARAMETERS: $id_penghuni: int - Tenant ID to find belongings for
 *    RETURNS: array - List of tenant's belongings with item IDs
 *    SQL_QUERY: SELECT * FROM tb_brng_bawaan WHERE id_penghuni = ?
 *    USED_IN:
 *      - Tenant management displays showing belongings
 *      - Billing calculations for additional charges
 *      - Tenant checkout processes (belongings removal)
 *    AI_CONTEXT: Primary method for tenant belongings management
 * 
 * 2. findByBarang($id_barang)
 *    PURPOSE: Get all tenants who have a specific item/belonging
 *    PARAMETERS: $id_barang: int - Item ID to find tenants for
 *    RETURNS: array - List of tenants who have this specific item
 *    SQL_QUERY: SELECT * FROM tb_brng_bawaan WHERE id_barang = ?
 *    USED_IN:
 *      - Item usage tracking and analytics
 *      - Popular items analysis
 *      - Item management and pricing decisions
 *    AI_CONTEXT: Reverse lookup for item popularity and usage
 * 
 * 3. findByPenghuniBarang($id_penghuni, $id_barang)
 *    PURPOSE: Check if specific tenant has specific item (relationship validation)
 *    PARAMETERS: 
 *      - $id_penghuni: int - Tenant ID
 *      - $id_barang: int - Item ID
 *    RETURNS: array|null - Relationship record or null if doesn't exist
 *    SQL_QUERY: SELECT * WHERE id_penghuni = ? AND id_barang = ?
 *    USED_IN:
 *      - Duplicate prevention when adding belongings
 *      - Validation before item assignment
 *      - Relationship existence checking
 *    AI_CONTEXT: Validates tenant-item relationships to prevent duplicates
 * 
 * 4. removeBarangFromPenghuni($id_penghuni, $id_barang)
 *    PURPOSE: Remove specific item from tenant's belongings list
 *    PARAMETERS:
 *      - $id_penghuni: int - Tenant ID
 *      - $id_barang: int - Item ID to remove
 *    RETURNS: int - Number of affected rows (0 or 1)
 *    SQL_QUERY: DELETE FROM tb_brng_bawaan WHERE id_penghuni = ? AND id_barang = ?
 *    USED_IN:
 *      - Tenant checkout processes
 *      - Belongings management when tenants remove items
 *      - Billing adjustments when items are returned
 *    AI_CONTEXT: Handles belongings removal affecting billing calculations
 * 
 * 5. getPenghuniBarangDetail($id_penghuni)
 *    PURPOSE: Get detailed belongings information with item names and prices
 *    PARAMETERS: $id_penghuni: int - Tenant ID
 *    RETURNS: array - Belongings with item details (name, price)
 *    SQL_QUERY: INNER JOIN with tb_barang to get complete item information
 *    USED_IN:
 *      - Detailed tenant belongings displays
 *      - Billing calculations showing itemized charges
 *      - Tenant statements and receipts
 *    AI_CONTEXT: Comprehensive view for billing and display purposes
 * 
 * DATABASE_RELATIONSHIPS:
 * - MANY-TO-ONE with tb_penghuni (belongings owner)
 * - MANY-TO-ONE with tb_barang (item details)
 * - CREATES many-to-many relationship between tenants and items
 * 
 * KEY_FIELDS:
 * - id: Primary key
 * - id_penghuni: Foreign key to tb_penghuni (which tenant)
 * - id_barang: Foreign key to tb_barang (which item)
 * - jumlah: Quantity of items (optional, may not be used in current implementation)
 * 
 * BUSINESS_RULES:
 * - One tenant can have multiple different items
 * - Multiple tenants can have the same item type
 * - Each tenant-item combination should be unique (no duplicates)
 * - Item costs are added to monthly billing calculations
 * - Belongings affect billing until removed
 * 
 * BILLING_INTEGRATION:
 * - Used in TagihanModel::generateTagihan() for additional charges
 * - Item prices from tb_barang are summed for billing
 * - Affects total monthly bill amount
 * - Changes to belongings affect future billing periods
 * 
 * USAGE_PATTERNS:
 * 1. Adding Tenant Belongings:
 *    Admin::penghuni() -> BarangBawaanModel::findByPenghuniBarang() -> BarangBawaanModel::create()
 * 
 * 2. Billing Calculation:
 *    TagihanModel::generateTagihan() -> BarangBawaanModel::findByPenghuni() -> sum prices
 * 
 * 3. Tenant Checkout:
 *    Admin::penghuni() -> BarangBawaanModel::removeBarangFromPenghuni()
 * 
 * 4. Display Belongings:
 *    Admin::penghuni() -> BarangBawaanModel::getPenghuniBarangDetail()
 * 
 * AI_INTEGRATION_NOTES:
 * - Critical for additional billing beyond room rent
 * - Enables flexible pricing models for different services
 * - Supports dynamic billing adjustments based on belongings
 * - Important for tenant lifecycle management
 * - Integrates with billing system for automated charge calculations
 * - Provides foundation for belongings-based analytics and reporting
 */