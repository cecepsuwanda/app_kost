<?php

namespace App\Examples;

use App\Core\QueryBuilder;
use App\Core\Database;

/**
 * QueryBuilder Usage Examples
 * 
 * This class demonstrates how to use the QueryBuilder in MODELS to replace
 * complex SQL queries with clean, readable code.
 * 
 * IMPORTANT: QueryBuilder should ONLY be used in Models, not Controllers!
 * Controllers should call model methods, not query database directly.
 */
class QueryBuilderExamples
{
    private $qb;

    public function __construct()
    {
        $this->qb = new QueryBuilder();
    }

    /**
     * Example 1: Simple SELECT with WHERE
     * 
     * OLD WAY (from PenghuniModel.php):
     * $sql = "SELECT * FROM tb_penghuni WHERE tgl_keluar IS NULL";
     * return $this->db->fetchAll($sql);
     */
    public function getActivePenghuni()
    {
        return $this->qb->table('tb_penghuni')
            ->whereNull('tgl_keluar')
            ->get();
    }

    /**
     * Example 2: Complex JOIN with WHERE (from TagihanModel.php)
     * 
     * OLD WAY:
     * $sql = "SELECT kp.id, kp.id_kamar, kp.tgl_masuk, k.harga as harga_kamar
     *         FROM tb_kmr_penghuni kp
     *         INNER JOIN tb_kamar k ON kp.id_kamar = k.id
     *         WHERE kp.tgl_keluar IS NULL
     *         GROUP BY kp.id,kp.id_kamar, kp.tgl_masuk, k.harga";
     */
    public function getActiveKamarWithHarga()
    {
        return $this->qb->table('tb_kmr_penghuni as kp')
            ->select('kp.id', 'kp.id_kamar', 'kp.tgl_masuk', 'k.harga as harga_kamar')
            ->innerJoin('tb_kamar k', 'kp.id_kamar', '=', 'k.id')
            ->whereNull('kp.tgl_keluar')
            ->groupBy('kp.id', 'kp.id_kamar', 'kp.tgl_masuk', 'k.harga')
            ->get();
    }

    /**
     * Example 3: Complex query with multiple JOINs (from DetailKamarPenghuniModel.php)
     * 
     * OLD WAY:
     * $sql = "SELECT dkp.*, p.nama, p.no_ktp, p.no_hp 
     *         FROM tb_detail_kmr_penghuni dkp 
     *         INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id 
     *         WHERE dkp.id_kmr_penghuni = :id_kmr_penghuni AND dkp.tgl_keluar IS NULL";
     */
    public function getPenghuniInKamar($id_kmr_penghuni)
    {
        return $this->qb->table('tb_detail_kmr_penghuni as dkp')
            ->select('dkp.*', 'p.nama', 'p.no_ktp', 'p.no_hp')
            ->innerJoin('tb_penghuni p', 'dkp.id_penghuni', '=', 'p.id')
            ->where('dkp.id_kmr_penghuni', '=', $id_kmr_penghuni)
            ->whereNull('dkp.tgl_keluar')
            ->get();
    }

    /**
     * Example 4: Aggregate functions with SUM
     * 
     * OLD WAY:
     * $sql = "SELECT SUM(jml_bayar) as total FROM tb_bayar WHERE id_tagihan = :id_tagihan";
     */
    public function getTotalPembayaran($id_tagihan)
    {
        return $this->qb->table('tb_bayar')
            ->select('SUM(jml_bayar) as total')
            ->where('id_tagihan', '=', $id_tagihan)
            ->first();
    }

    /**
     * Example 5: Complex query with COALESCE and multiple JOINs
     * 
     * OLD WAY (from TagihanModel.php):
     * $sql = "SELECT COALESCE(SUM(b.harga), 0) as total_harga
     *         FROM tb_brng_bawaan bb
     *         INNER JOIN tb_barang b ON bb.id_barang = b.id
     *         WHERE bb.id_penghuni = :id_penghuni";
     */
    public function getTotalHargaBarangPenghuni($id_penghuni)
    {
        return $this->qb->table('tb_brng_bawaan as bb')
            ->select('COALESCE(SUM(b.harga), 0) as total_harga')
            ->innerJoin('tb_barang b', 'bb.id_barang', '=', 'b.id')
            ->where('bb.id_penghuni', '=', $id_penghuni)
            ->first();
    }

    /**
     * Example 6: Using WHERE IN for multiple values
     */
    public function getKamarInGedung($gedungList)
    {
        return $this->qb->table('tb_kamar')
            ->whereIn('gedung', $gedungList)
            ->orderBy('nomor')
            ->get();
    }

    /**
     * Example 7: Using WHERE BETWEEN for date ranges
     */
    public function getTagihanBetweenDates($startDate, $endDate)
    {
        return $this->qb->table('tb_tagihan')
            ->whereBetween('tanggal', $startDate, $endDate)
            ->orderBy('tanggal', 'DESC')
            ->get();
    }

    /**
     * Example 8: Using LIKE for search functionality
     */
    public function searchPenghuniByNama($nama)
    {
        return $this->qb->table('tb_penghuni')
            ->whereLike('nama', "%$nama%")
            ->whereNull('tgl_keluar')
            ->get();
    }

    /**
     * Example 9: Complex reporting query with GROUP BY and HAVING
     */
    public function getLaporanTagihan()
    {
        return $this->qb->table('tb_tagihan as t')
            ->select(
                't.bulan',
                't.tahun', 
                'COUNT(*) as jumlah_tagihan',
                'SUM(t.jml_tagihan) as total_tagihan',
                'AVG(t.jml_tagihan) as rata_rata'
            )
            ->groupBy('t.bulan', 't.tahun')
            ->having('COUNT(*)', '>', 0)
            ->orderBy('t.tahun', 'DESC')
            ->orderBy('t.bulan', 'DESC')
            ->get();
    }

    /**
     * Example 10: Update with WHERE conditions
     */
    public function updateStatusPembayaran($id_tagihan, $status)
    {
        return $this->qb->table('tb_bayar')
            ->where('id_tagihan', '=', $id_tagihan)
            ->update(['status' => $status]);
    }

    /**
     * Example 11: Delete with complex WHERE
     */
    public function deleteOldTagihan($tahun)
    {
        return $this->qb->table('tb_tagihan')
            ->where('tahun', '<', $tahun)
            ->delete();
    }

    /**
     * Example 12: Insert new data
     */
    public function createNewPenghuni($data)
    {
        return $this->qb->table('tb_penghuni')
            ->insert($data);
    }

    /**
     * Example 13: Count with conditions
     */
    public function countKamarTersedia()
    {
        return $this->qb->table('tb_kamar as k')
            ->leftJoin('tb_kmr_penghuni kp', 'k.id', '=', 'kp.id_kamar')
            ->where('kp.tgl_keluar', 'IS NOT', 'NULL')
            ->orWhere('kp.id', 'IS', 'NULL')
            ->count();
    }

    /**
     * Example 14: Pagination
     */
    public function getPenghuniPaginated($page = 1, $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->qb->table('tb_penghuni')
            ->whereNull('tgl_keluar')
            ->orderBy('nama')
            ->limit($perPage)
            ->offset($offset)
            ->get();
    }

    /**
     * Example 15: Complex dashboard query
     */
    public function getDashboardStats()
    {
        // Multiple queries can be combined
        $totalKamar = $this->qb->table('tb_kamar')->count();
        
        $kamarTerisi = $this->qb->table('tb_kmr_penghuni')
            ->whereNull('tgl_keluar')
            ->count();
            
        $penghuniAktif = $this->qb->table('tb_penghuni')
            ->whereNull('tgl_keluar')
            ->count();
            
        $totalTagihan = $this->qb->table('tb_tagihan')
            ->select('SUM(jml_tagihan) as total')
            ->where('bulan', '=', date('n'))
            ->where('tahun', '=', date('Y'))
            ->first();

        return [
            'total_kamar' => $totalKamar,
            'kamar_terisi' => $kamarTerisi,
            'kamar_tersedia' => $totalKamar - $kamarTerisi,
            'penghuni_aktif' => $penghuniAktif,
            'total_tagihan_bulan_ini' => $totalTagihan['total'] ?? 0
        ];
    }

    /**
     * Debug: Show the generated SQL and parameters
     */
    public function debugQuery()
    {
        $query = $this->qb->table('tb_penghuni')
            ->select('nama', 'no_ktp')
            ->where('tgl_keluar', 'IS', 'NULL')
            ->whereLike('nama', '%John%')
            ->orderBy('nama');
            
        echo "SQL: " . $query->toSql() . "\n";
        echo "Parameters: " . json_encode($query->getParams()) . "\n";
        
        return $query->get();
    }
}