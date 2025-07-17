<?php

namespace App\Models;

use App\Core\Model;

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

    public function getKamarTersedia($max_occupants = 2)
    {
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
                       (? - COALESCE(COUNT(dkp.id), 0)) as slot_tersedia
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.id
                HAVING slot_tersedia > 0
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql, [$max_occupants]);
    }

    public function getKamarTerisi()
    {
        $sql = "SELECT k.*, 
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni,
                       COUNT(dkp.id) as jumlah_penghuni,
                       kp.tgl_masuk, kp.tgl_keluar
                FROM tb_kamar k
                INNER JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                INNER JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                GROUP BY k.id, kp.id
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarWithStatus($max_occupants = 2)
    {
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
                       CASE 
                           WHEN COUNT(dkp.id) = 0 THEN 'kosong'
                           WHEN COUNT(dkp.id) < ? THEN 'tersedia'
                           ELSE 'penuh'
                       END as status,
                       GROUP_CONCAT(p.nama SEPARATOR ', ') as nama_penghuni,
                       kp.tgl_masuk, kp.id as id_kmr_penghuni
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                GROUP BY k.id,kp.tgl_masuk,kp.id
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql, [$max_occupants]);
    }

    public function getDetailKamar($id_kamar)
    {
        $sql = "SELECT k.*, 
                       kp.id as id_kmr_penghuni, kp.tgl_masuk as tgl_masuk_kamar,
                       dkp.id as id_detail, dkp.tgl_masuk as tgl_masuk_penghuni,
                       p.id as id_penghuni, p.nama, p.no_ktp, p.no_hp
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                WHERE k.id = :id_kamar
                ORDER BY dkp.tgl_masuk";
        
        return $this->db->fetchAll($sql, ['id_kamar' => $id_kamar]);
    }
}