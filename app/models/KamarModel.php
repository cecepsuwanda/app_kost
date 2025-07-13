<?php

class KamarModel extends Model
{
    protected $table = 'tb_kamar';

    public function findByNomor($nomor)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE nomor = :nomor", ['nomor' => $nomor]);
    }

    public function getKamarKosong()
    {
        $sql = "SELECT k.* FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                WHERE kp.id IS NULL
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarTerisi()
    {
        $sql = "SELECT k.*, p.nama as nama_penghuni, kp.tgl_masuk, kp.tgl_keluar
                FROM tb_kamar k
                INNER JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                INNER JOIN tb_penghuni p ON kp.id_penghuni = p.id
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarWithStatus()
    {
        $sql = "SELECT k.*, 
                       CASE WHEN kp.id IS NULL THEN 'kosong' ELSE 'terisi' END as status,
                       p.nama as nama_penghuni, kp.tgl_masuk
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON kp.id_penghuni = p.id
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql);
    }
}