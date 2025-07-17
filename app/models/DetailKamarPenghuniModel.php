<?php

namespace App\Models;

use App\Core\Model;

class DetailKamarPenghuniModel extends Model
{
    protected $table = 'tb_detail_kmr_penghuni';

    public function findActiveByPenghuni($id_penghuni)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id_penghuni = :id_penghuni AND tgl_keluar IS NULL", 
                               ['id_penghuni' => $id_penghuni]);
    }

    public function findActiveByKamarPenghuni($id_kmr_penghuni)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_kmr_penghuni = :id_kmr_penghuni AND tgl_keluar IS NULL", 
                                   ['id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function checkoutPenghuni($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function checkoutPenghuniFromKamar($id_penghuni, $tgl_keluar)
    {
        $detailActive = $this->findActiveByPenghuni($id_penghuni);
        if ($detailActive) {
            return $this->checkoutPenghuni($detailActive['id'], $tgl_keluar);
        }
        return false;
    }

    public function getPenghuniByKamarPenghuni($id_kmr_penghuni)
    {
        $sql = "SELECT dkp.*, p.nama, p.no_ktp, p.no_hp
                FROM {$this->table} dkp
                INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                WHERE dkp.id_kmr_penghuni = :id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                ORDER BY dkp.tgl_masuk";
        
        return $this->db->fetchAll($sql, ['id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function getAllActivePenghuniWithKamar()
    {
        $sql = "SELECT dkp.*, p.nama as nama_penghuni, p.no_ktp, p.no_hp,
                       kp.id_kamar, k.nomor as nomor_kamar, k.harga as harga_kamar,
                       kp.tgl_masuk as tgl_masuk_kamar
                FROM {$this->table} dkp
                INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                INNER JOIN tb_kmr_penghuni kp ON dkp.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE dkp.tgl_keluar IS NULL AND kp.tgl_keluar IS NULL
                ORDER BY k.nomor, dkp.tgl_masuk";
        
        return $this->db->fetchAll($sql);
    }

    public function countActivePenghuniInKamar($id_kamar)
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} dkp
                INNER JOIN tb_kmr_penghuni kp ON dkp.id_kmr_penghuni = kp.id
                WHERE kp.id_kamar = :id_kamar AND dkp.tgl_keluar IS NULL AND kp.tgl_keluar IS NULL";
        
        $result = $this->db->fetch($sql, ['id_kamar' => $id_kamar]);
        return $result ? $result['total'] : 0;
    }
}