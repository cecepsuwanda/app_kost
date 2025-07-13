<?php

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
                       p.nama as nama_penghuni, k.nomor as nomor_kamar
                FROM {$this->table} b
                INNER JOIN tb_tagihan t ON b.id_tagihan = t.id
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_penghuni p ON kp.id_penghuni = p.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                " . ($id_tagihan ? "WHERE b.id_tagihan = :id_tagihan " : "") . "
                ORDER BY b.id DESC";
        
        $params = $id_tagihan ? ['id_tagihan' => $id_tagihan] : [];
        return $this->db->fetchAll($sql, $params);
    }

    public function getLaporanPembayaran($bulan = null)
    {
        $sql = "SELECT t.bulan, p.nama as nama_penghuni, k.nomor as nomor_kamar,
                       t.jml_tagihan, COALESCE(SUM(b.jml_bayar), 0) as total_bayar,
                       CASE 
                           WHEN COALESCE(SUM(b.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
                           WHEN COALESCE(SUM(b.jml_bayar), 0) > 0 THEN 'Cicil'
                           ELSE 'Belum Bayar'
                       END as status_bayar
                FROM tb_tagihan t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_penghuni p ON kp.id_penghuni = p.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN {$this->table} b ON t.id = b.id_tagihan
                " . ($bulan ? "WHERE t.bulan = :bulan " : "") . "
                GROUP BY t.id
                ORDER BY t.bulan DESC, k.nomor";
        
        $params = $bulan ? ['bulan' => $bulan] : [];
        return $this->db->fetchAll($sql, $params);
    }
}