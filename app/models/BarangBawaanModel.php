<?php

class BarangBawaanModel extends Model
{
    protected $table = 'tb_brng_bawaan';

    public function findByPenghuni($id_penghuni)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_penghuni = :id_penghuni", 
                                 ['id_penghuni' => $id_penghuni]);
    }

    public function findByBarang($id_barang)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_barang = :id_barang", 
                                 ['id_barang' => $id_barang]);
    }

    public function findByPenghuniBarang($id_penghuni, $id_barang)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id_penghuni = :id_penghuni AND id_barang = :id_barang", 
                               ['id_penghuni' => $id_penghuni, 'id_barang' => $id_barang]);
    }

    public function removeBarangFromPenghuni($id_penghuni, $id_barang)
    {
        return $this->db->delete($this->table, 'id_penghuni = :id_penghuni AND id_barang = :id_barang', 
                                ['id_penghuni' => $id_penghuni, 'id_barang' => $id_barang]);
    }

    public function getPenghuniBarangDetail($id_penghuni)
    {
        $sql = "SELECT bb.*, b.nama as nama_barang, b.harga as harga_barang
                FROM {$this->table} bb
                INNER JOIN tb_barang b ON bb.id_barang = b.id
                WHERE bb.id_penghuni = :id_penghuni";
        
        return $this->db->fetchAll($sql, ['id_penghuni' => $id_penghuni]);
    }
}