<?php

namespace App\Models;

use App\Core\Model;

class BayarModel extends Model
{
    protected $table = 'tb_bayar';

    public function findByTagihan($id_tagihan)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_tagihan = :id_tagihan ORDER BY id DESC", 
                                 ['id_tagihan' => $id_tagihan]);
    }

    public function getTotalBayarByTagihan($id_tagihan)
    {
        $result = $this->db->fetch("SELECT SUM(jml_bayar) as total FROM {$this->table} WHERE id_tagihan = :id_tagihan", 
                                  ['id_tagihan' => $id_tagihan]);
        return $result['total'] ?? 0;
    }

    public function getStatusByTagihan($id_tagihan)
    {
        $sql = "SELECT t.jml_tagihan, COALESCE(SUM(b.jml_bayar), 0) as total_bayar,
                       CASE 
                           WHEN COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan THEN 'lunas'
                           WHEN COALESCE(SUM(b.jml_bayar), 0) > 0 THEN 'cicil'
                           ELSE 'belum_bayar'
                       END as status
                FROM tb_tagihan t
                LEFT JOIN {$this->table} b ON t.id = b.id_tagihan
                WHERE t.id = :id_tagihan
                GROUP BY t.id";
        
        return $this->db->fetch($sql, ['id_tagihan' => $id_tagihan]);
    }

    public function bayar($id_tagihan, $jml_bayar)
    {
        // Get tagihan info
        $tagihanModel = new TagihanModel();
        $tagihan = $tagihanModel->findById($id_tagihan);
        
        if (!$tagihan) {
            return false;
        }

        // Get total already paid
        $totalBayar = $this->getTotalBayarByTagihan($id_tagihan);
        $sisaTagihan = $tagihan['jml_tagihan'] - $totalBayar;

        // Determine status
        if ($jml_bayar >= $sisaTagihan) {
            $status = 'lunas';
            $jml_bayar = $sisaTagihan; // Don't allow overpayment
        } else {
            $status = 'cicil';
        }

        // Create payment record
        return $this->create([
            'id_tagihan' => $id_tagihan,
            'jml_bayar' => $jml_bayar,
            'status' => $status
        ]);
    }

    public function getPembayaranDetail($id_tagihan = null)
    {
        $sql = "SELECT b.*, t.bulan, t.jml_tagihan,
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni, k.nomor as nomor_kamar
                FROM {$this->table} b
                INNER JOIN tb_tagihan t ON b.id_tagihan = t.id
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                " . ($id_tagihan ? "WHERE b.id_tagihan = :id_tagihan " : "") . "
                GROUP BY b.id
                ORDER BY b.id DESC";
        
        $params = $id_tagihan ? ['id_tagihan' => $id_tagihan] : [];
        return $this->db->fetchAll($sql, $params);
    }

    public function getLaporanPembayaran($periode = null)
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


        $sql = "SELECT t.id,t.bulan, t.tahun, t.tanggal, GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni, k.nomor as nomor_kamar, k.gedung,
                       t.jml_tagihan, COALESCE(SUM(b.jml_bayar), 0) as total_bayar,
                       DATEDIFF(CURDATE(), t.tanggal) as selisih_hari,
                       CASE 
                           WHEN COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
                           WHEN COALESCE(SUM(b.jml_bayar), 0) > 0 THEN 'Cicil'
                           ELSE 'Belum Bayar'
                       END as status_bayar,
                       CASE 
                           WHEN COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan THEN 'lunas'
                           WHEN DATEDIFF(CURDATE(), t.tanggal) > 0 THEN 'terlambat'
                           WHEN DATEDIFF(CURDATE(), t.tanggal) >= -3 AND DATEDIFF(CURDATE(), t.tanggal) <= 0 THEN 'mendekati'
                           ELSE 'normal'
                       END as status_waktu
                FROM tb_tagihan t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN {$this->table} b ON t.id = b.id_tagihan
                " . $whereCondition . "
                GROUP BY t.id
                ORDER BY t.tahun DESC, t.bulan DESC, k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalBayarPerGedung($periode = null)
    {
        $whereCondition = "";
        $params = [];
        
        if ($periode) {
            $date = date_create_from_format('Y-m', $periode);
            if ($date) {
                $bulan = (int)$date->format('n');
                $tahun = (int)$date->format('Y');
                $whereCondition = "WHERE t.bulan = :bulan AND t.tahun = :tahun ";
                $params = ['bulan' => $bulan, 'tahun' => $tahun];
            }
        }

        $sql = "SELECT k.gedung,
                       COUNT(DISTINCT t.id) as jumlah_tagihan,
                       SUM(COALESCE(b.jml_bayar, 0)) as total_dibayar,
                       COUNT(DISTINCT b.id) as jumlah_pembayaran
                FROM tb_tagihan t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN {$this->table} b ON t.id = b.id_tagihan
                " . $whereCondition . "
                GROUP BY k.gedung
                ORDER BY k.gedung";
        
        return $this->db->fetchAll($sql, $params);
    }
}