<?php

namespace App\Models;

use App\Core\Model;

class BarangModel extends Model
{
    protected $table = 'tb_barang';

    public function findByNama($nama)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE nama = :nama", ['nama' => $nama]);
    }

    public function getBarangByPenghuni($id_penghuni)
    {
        $sql = "SELECT b.*, bb.id as id_bawaan
                FROM tb_barang b
                INNER JOIN tb_brng_bawaan bb ON b.id = bb.id_barang
                WHERE bb.id_penghuni = :id_penghuni";
        
        return $this->db->fetchAll($sql, ['id_penghuni' => $id_penghuni]);
    }

    public function getTotalHargaBarangPenghuni($id_penghuni)
    {
        $sql = "SELECT SUM(b.harga) as total_harga
                FROM tb_barang b
                INNER JOIN tb_brng_bawaan bb ON b.id = bb.id_barang
                WHERE bb.id_penghuni = :id_penghuni";
        
        $result = $this->db->fetch($sql, ['id_penghuni' => $id_penghuni]);
        return $result['total_harga'] ?? 0;
    }
}

/**
 * =============================================================================
 * CLASS DOCUMENTATION FOR AI LLM UNDERSTANDING
 * =============================================================================
 * 
 * CLASS: BarangModel
 * PURPOSE: Manages master data for billable items/belongings in the boarding house
 * DATABASE_TABLE: tb_barang
 * EXTENDS: Model (base model class)
 * 
 * BUSINESS_CONTEXT:
 * This model manages the master data for items/belongings that tenants can bring
 * to the boarding house and are subject to additional monthly charges. Items like
 * motorcycles, bicycles, or other belongings that require space or services have
 * defined prices that get added to tenant bills. This is the catalog/reference
 * table for all billable items in the system.
 * 
 * CLASS_METHODS:
 * 
 * 1. findByNama($nama)
 *    PURPOSE: Find item by name for validation and lookup
 *    PARAMETERS: $nama: string - Item name to search for
 *    RETURNS: array|null - Item data or null if not found
 *    SQL_QUERY: SELECT * FROM tb_barang WHERE nama = ?
 *    USED_IN:
 *      - Item creation validation (prevent duplicate names)
 *      - Item search and lookup functionality
 *      - Admin item management interfaces
 *    AI_CONTEXT: Primary item identification method for admin operations
 * 
 * 2. getBarangByPenghuni($id_penghuni)
 *    PURPOSE: Get all items/belongings for a specific tenant with relationship IDs
 *    PARAMETERS: $id_penghuni: int - Tenant ID to find items for
 *    RETURNS: array - Items with relationship IDs for management
 *    SQL_QUERY: INNER JOIN with tb_brng_bawaan to get tenant's items
 *    USED_IN:
 *      - Tenant belongings management interfaces
 *      - Displaying tenant's current items with removal options
 *      - Tenant checkout processes
 *    AI_CONTEXT: Shows tenant's billable items with management capabilities
 * 
 * 3. getTotalHargaBarangPenghuni($id_penghuni)
 *    PURPOSE: Calculate total cost of all items for a tenant (billing calculation)
 *    PARAMETERS: $id_penghuni: int - Tenant ID to calculate total for
 *    RETURNS: float - Total cost of all tenant's items (0 if none)
 *    SQL_QUERY: SUM(b.harga) with JOIN to calculate total costs
 *    USED_IN:
 *      - TagihanModel::generateTagihan() - for billing calculations
 *      - Financial reporting and analytics
 *      - Tenant cost breakdowns and statements
 *    AI_CONTEXT: Critical method for billing system integration
 * 
 * DATABASE_RELATIONSHIPS:
 * - ONE-TO-MANY with tb_brng_bawaan (item usage by tenants)
 * - REFERENCED in billing calculations through belongings relationships
 * 
 * KEY_FIELDS:
 * - id: Primary key
 * - nama: Item name (unique identifier, e.g., "Motor", "Sepeda")
 * - harga: Monthly cost/price for this item
 * - deskripsi: Item description (optional)
 * - created_at: Record creation timestamp
 * 
 * BUSINESS_RULES:
 * - Each item must have unique name
 * - Item prices are used in monthly billing calculations
 * - Items are reusable catalog entries (multiple tenants can have same item)
 * - Prices can be updated affecting future billing periods
 * - Deleted items should be handled carefully due to billing history
 * 
 * PRICING_LOGIC:
 * - Fixed monthly rate per item type
 * - No quantity-based pricing (each instance charged separately)
 * - Prices added to room rent for total monthly bill
 * - Price changes affect future bills, not historical ones
 * 
 * USAGE_PATTERNS:
 * 1. Item Master Data Management:
 *    Admin::barang() -> BarangModel::findByNama() -> BarangModel::create()
 * 
 * 2. Billing Integration:
 *    TagihanModel::generateTagihan() -> BarangModel::getTotalHargaBarangPenghuni()
 * 
 * 3. Tenant Belongings Display:
 *    Admin::penghuni() -> BarangModel::getBarangByPenghuni()
 * 
 * 4. Financial Reporting:
 *    Reports -> BarangModel::getTotalHargaBarangPenghuni() for cost analysis
 * 
 * BILLING_INTEGRATION:
 * - Central to additional billing calculations beyond room rent
 * - Prices from this model are summed in billing generation
 * - Changes to item prices affect future billing periods
 * - Historical billing preserved even if item prices change
 * 
 * AI_INTEGRATION_NOTES:
 * - Master data foundation for flexible billing system
 * - Enables dynamic pricing for different services/items
 * - Critical for automated billing calculation accuracy
 * - Supports scalable item catalog management
 * - Important for financial planning and pricing strategies
 * - Provides foundation for item-based analytics and reporting
 */