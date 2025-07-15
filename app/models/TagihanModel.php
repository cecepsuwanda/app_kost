<?php

namespace App\Models;

use App\Core\Model;

class TagihanModel extends Model
{
    protected $table = 'tb_tagihan';

    public function findByBulan($bulan)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE bulan = :bulan", ['bulan' => $bulan]);
    }

    public function findByKamarPenghuni($id_kmr_penghuni)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_kmr_penghuni = :id_kmr_penghuni", 
                                 ['id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function findByBulanKamarPenghuni($bulan, $id_kmr_penghuni)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE bulan = :bulan AND id_kmr_penghuni = :id_kmr_penghuni", 
                               ['bulan' => $bulan, 'id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function generateTagihan($bulan)
    {
        // Get all active penghuni kamar
        $kmrPenghuniModel = new KamarPenghuniModel();
        $activeKamarPenghuni = $kmrPenghuniModel->getPenghuniKamarActive();

        $generated = 0;
        foreach ($activeKamarPenghuni as $kp) {
            // Check if tagihan already exists for this month
            $existing = $this->findByBulanKamarPenghuni($bulan, $kp['id']);
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
                'id_kmr_penghuni' => $kp['id'],
                'jml_tagihan' => $totalTagihan
            ]);

            $generated++;
        }

        return $generated;
    }

    public function getTagihanDetail($bulan = null)
    {
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
                " . ($bulan ? "WHERE t.bulan = :bulan " : "") . "
                GROUP BY t.id
                ORDER BY t.bulan DESC, k.nomor";
        
        $params = $bulan ? ['bulan' => $bulan] : [];
        return $this->db->fetchAll($sql, $params);
    }

    public function getTagihanTerlambat()
    {
        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar, 
                       p.nama as nama_penghuni, k.nomor as nomor_kamar,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_penghuni p ON kp.id_penghuni = p.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan                
                WHERE t.bulan = month(now()) AND t.tahun = year(now())
                GROUP BY t.id
                HAVING COALESCE(SUM(byr.jml_bayar), 0) < t.jml_tagihan
                ORDER BY t.bulan DESC, k.nomor";

                //WHERE t.bulan < :current_month AND t.tahun = :current_year
        
        return $this->db->fetchAll($sql, []); //'current_month' => $currentMonth, 'current_year' => $currentYear
    }
}