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
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE no_ktp = :no_ktp", ['no_ktp' => $no_ktp]);
    }

    public function checkout($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function getPenghuniWithKamar()
    {
        $sql = "SELECT p.*, k.nomor as nomor_kamar, k.harga as harga_kamar, 
                       kp.tgl_masuk as tgl_masuk_kamar, kp.tgl_keluar as tgl_keluar_kamar
                FROM tb_penghuni p
                LEFT JOIN tb_kmr_penghuni kp ON p.id = kp.id_penghuni AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE p.tgl_keluar IS NULL";
        
        return $this->db->fetchAll($sql);
    }
}