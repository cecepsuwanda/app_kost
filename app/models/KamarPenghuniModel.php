<?php

namespace App\Models;

use App\Core\Model;

class KamarPenghuniModel extends Model
{
    protected $table = 'tb_kmr_penghuni';

    public function findActiveByPenghuni($id_penghuni)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id_penghuni = :id_penghuni AND tgl_keluar IS NULL", 
                               ['id_penghuni' => $id_penghuni]);
    }

    public function findActiveByKamar($id_kamar)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id_kamar = :id_kamar AND tgl_keluar IS NULL", 
                               ['id_kamar' => $id_kamar]);
    }

    public function checkoutKamar($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function pindahKamar($id_penghuni, $id_kamar_baru, $tgl_pindah)
    {
        // Checkout dari kamar lama
        $kamarLama = $this->findActiveByPenghuni($id_penghuni);
        if ($kamarLama) {
            $this->checkoutKamar($kamarLama['id'], $tgl_pindah);
        }

        // Masuk ke kamar baru
        return $this->create([
            'id_kamar' => $id_kamar_baru,
            'id_penghuni' => $id_penghuni,
            'tgl_masuk' => $tgl_pindah
        ]);
    }

    public function getPenghuniKamarActive()
    {
        $sql = "SELECT kp.*, p.nama as nama_penghuni, p.no_ktp, p.no_hp,
                       k.nomor as nomor_kamar, k.harga as harga_kamar
                FROM {$this->table} kp
                INNER JOIN tb_penghuni p ON kp.id_penghuni = p.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE kp.tgl_keluar IS NULL
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarSewaanMendekatiJatuhTempo($days = 30)
    {
        $sql = "SELECT kp.*, p.nama as nama_penghuni, k.nomor as nomor_kamar,
                       DATEDIFF(DATE_ADD(kp.tgl_masuk, INTERVAL 1 MONTH), CURDATE()) as hari_tersisa
                FROM {$this->table} kp
                INNER JOIN tb_penghuni p ON kp.id_penghuni = p.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE kp.tgl_keluar IS NULL
                AND DATEDIFF(DATE_ADD(kp.tgl_masuk, INTERVAL 1 MONTH), CURDATE()) <= :days
                ORDER BY hari_tersisa ASC";
        
        return $this->db->fetchAll($sql, ['days' => $days]);
    }
}