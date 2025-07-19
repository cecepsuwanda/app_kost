<?php

namespace App\Models;

use App\Core\Model;

class PenghuniModel extends Model
{
    protected $table = 'tb_penghuni';

    public function findActive()
    {
        // SQL: Mengambil semua penghuni yang masih aktif (belum keluar)
        // SELECT * FROM tb_penghuni WHERE tgl_keluar IS NULL
        // 
        // Penjelasan:
        // - tgl_keluar IS NULL: penghuni yang belum memiliki tanggal keluar
        // - Digunakan untuk menampilkan daftar penghuni yang masih tinggal di kos
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE tgl_keluar IS NULL");
    }

    public function findByKtp($no_ktp)
    {
        if (empty($no_ktp)) {
            return null;
        }
        // SQL: Mencari penghuni berdasarkan nomor KTP
        // SELECT * FROM tb_penghuni WHERE no_ktp = ?
        // 
        // Penjelasan:
        // - WHERE no_ktp = ?: filter berdasarkan nomor KTP yang unik
        // - Digunakan untuk validasi penghuni baru (mencegah duplikasi KTP)
        // - Parameter binding (:no_ktp) mencegah SQL injection
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE no_ktp = :no_ktp", ['no_ktp' => $no_ktp]);
    }

    public function checkout($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function getPenghuniWithKamar()
    {
        // SQL COMPLEX JOIN: Mengambil data penghuni beserta informasi kamar yang ditempati
        // SELECT p.*, k.nomor as nomor_kamar, k.harga as harga_kamar, 
        //        dkp.tgl_masuk as tgl_masuk_kamar, dkp.tgl_keluar as tgl_keluar_kamar,
        //        kp.id as id_kmr_penghuni
        // FROM tb_penghuni p
        // LEFT JOIN tb_detail_kmr_penghuni dkp ON p.id = dkp.id_penghuni AND dkp.tgl_keluar IS NULL
        // LEFT JOIN tb_kmr_penghuni kp ON dkp.id_kmr_penghuni = kp.id AND kp.tgl_keluar IS NULL
        // LEFT JOIN tb_kamar k ON kp.id_kamar = k.id
        // WHERE p.tgl_keluar IS NULL ORDER BY p.nama
        //
        // Penjelasan:
        // - LEFT JOIN: ambil semua penghuni, meskipun belum punya kamar
        // - tb_detail_kmr_penghuni: tabel detail hubungan penghuni-kamar
        // - tb_kmr_penghuni: tabel periode penghunian kamar
        // - tb_kamar: tabel master data kamar
        // - AND dkp.tgl_keluar IS NULL: hanya relasi yang masih aktif
        // - ORDER BY p.nama: urutkan berdasarkan nama penghuni
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