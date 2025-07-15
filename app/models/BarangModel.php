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