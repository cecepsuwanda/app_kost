<?php

namespace App\Models;

use App\Core\Model;

class TagihanModel extends Model
{
    protected $table = 'tb_tagihan';

    public function findByBulanTahun($bulan, $tahun)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE bulan = :bulan AND tahun = :tahun", 
                                 ['bulan' => $bulan, 'tahun' => $tahun]);
    }

    public function findByKamarPenghuni($id_kmr_penghuni)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_kmr_penghuni = :id_kmr_penghuni", 
                                 ['id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function findByBulanTahunKamarPenghuni($bulan, $tahun, $id_kmr_penghuni)
    {
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

        // Get all active penghuni kamar
        $kmrPenghuniModel = new \App\Models\KamarPenghuniModel();
        $activeKamarPenghuni = $kmrPenghuniModel->getPenghuniKamarActive();

        $generated = 0;
        foreach ($activeKamarPenghuni as $kp) {
            // Check if tagihan already exists for this month and year
            $existing = $this->findByBulanTahunKamarPenghuni($bulan, $tahun, $kp['id']);
            if ($existing) {
                continue; // Skip if already generated
            }

            // Calculate total tagihan (harga kamar + harga barang for all penghuni)
            $barangModel = new \App\Models\BarangModel();
            $detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();
            $penghuniList = $detailKamarPenghuniModel->findActiveByKamarPenghuni($kp['id']);
            
            $totalHargaBarang = 0;
            foreach ($penghuniList as $penghuni) {
                $totalHargaBarang += $barangModel->getTotalHargaBarangPenghuni($penghuni['id_penghuni']);
            }
            
            $totalTagihan = $kp['harga_kamar'] + $totalHargaBarang;

            // Create tagihan
            $this->create([
                'bulan' => $bulan,
                'tahun' => $tahun,
                'id_kmr_penghuni' => $kp['id'],
                'jml_tagihan' => $totalTagihan
            ]);

            $generated++;
        }

        return $generated;
    }

    public function recalculateTagihan($id_tagihan)
    {
        // Get tagihan details
        $tagihan = $this->find($id_tagihan);
        if (!$tagihan) {
            return false;
        }

        // Get kamar penghuni details
        $kmrPenghuniModel = new \App\Models\KamarPenghuniModel();
        $kamarPenghuni = $kmrPenghuniModel->find($tagihan['id_kmr_penghuni']);
        if (!$kamarPenghuni) {
            return false;
        }

        // Get room price
        $kamarModel = new \App\Models\KamarModel();
        $kamar = $kamarModel->find($kamarPenghuni['id_kamar']);
        if (!$kamar) {
            return false;
        }

        // Calculate total barang bawaan for all penghuni in this kamar
        $barangModel = new \App\Models\BarangModel();
        $detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();
        $penghuniList = $detailKamarPenghuniModel->findActiveByKamarPenghuni($tagihan['id_kmr_penghuni']);
        
        $totalHargaBarang = 0;
        foreach ($penghuniList as $penghuni) {
            $totalHargaBarang += $barangModel->getTotalHargaBarangPenghuni($penghuni['id_penghuni']);
        }
        
        $newTotalTagihan = $kamar['harga'] + $totalHargaBarang;

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

        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar,p.no_hp, 
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni,
                       k.nomor as nomor_kamar, k.harga as harga_kamar,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar,
                       CASE 
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) > 0 THEN 'Cicil'
                           ELSE 'Belum Bayar'
                       END as status_bayar
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan
                " . $whereCondition . "
                GROUP BY t.id,p.no_hp
                ORDER BY t.tahun DESC, t.bulan DESC, k.nomor";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getTagihanTerlambat()
    {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar, 
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni, k.nomor as nomor_kamar,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan                
                WHERE (t.tahun < :current_year) OR (t.tahun = :current_year AND t.bulan < :current_month)
                GROUP BY t.id
                HAVING COALESCE(SUM(byr.jml_bayar), 0) < t.jml_tagihan
                ORDER BY t.tahun DESC, t.bulan DESC, k.nomor";
        
        return $this->db->fetchAll($sql, [
            'current_month' => $currentMonth, 
            'current_year' => $currentYear
        ]);
    }
}