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
        $kmrPenghuniModel = new KamarPenghuniModel();
        $activeKamarPenghuni = $kmrPenghuniModel->getPenghuniKamarActive();

        $generated = 0;
        foreach ($activeKamarPenghuni as $kp) {
            // Check if tagihan already exists for this month and year
            $existing = $this->findByBulanTahunKamarPenghuni($bulan, $tahun, $kp['id']);
            if ($existing) {
                continue; // Skip if already generated
            }

            // Calculate total tagihan (harga kamar + harga barang)
            $barangModel = new BarangModel();
            $hargaBarang = $barangModel->getTotalHargaBarangPenghuni($kp['id_penghuni']);
            $totalTagihan = $kp['harga_kamar'] + $hargaBarang;

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
                       p.nama as nama_penghuni, p.no_ktp, p.no_hp,
                       k.nomor as nomor_kamar, k.harga as harga_kamar,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar,
                       CASE 
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) > 0 THEN 'Cicil'
                           ELSE 'Belum Bayar'
                       END as status_bayar
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_penghuni p ON kp.id_penghuni = p.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan
                " . $whereCondition . "
                GROUP BY t.id
                ORDER BY t.tahun DESC, t.bulan DESC, k.nomor";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getTagihanTerlambat()
    {
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar, 
                       p.nama as nama_penghuni, k.nomor as nomor_kamar,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_penghuni p ON kp.id_penghuni = p.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
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