<?php

namespace App\Models;

use App\Core\Model;

class PenghuniModel extends Model
{
    protected $table = 'tb_penghuni';

    public function findActive()
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE tgl_keluar IS NULL");
    }

    public function findByKtp($no_ktp)
    {
        if (empty($no_ktp)) {
            return null;
        }
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE no_ktp = :no_ktp", ['no_ktp' => $no_ktp]);
    }

    public function checkout($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function getPenghuniWithKamar()
    {
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