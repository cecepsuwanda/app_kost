<?php

namespace App\Models;

use App\Core\Model;

class KamarModel extends Model
{
    protected $table = 'tb_kamar';

    public function findByNomor($nomor)
    {
        // SQL: Mencari kamar berdasarkan nomor kamar
        // SELECT * FROM tb_kamar WHERE nomor = ?
        // 
        // Penjelasan:
        // - WHERE nomor = ?: filter berdasarkan nomor kamar yang unik
        // - Digunakan untuk validasi kamar baru (mencegah duplikasi nomor)
        // - Parameter binding (:nomor) mencegah SQL injection
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE nomor = :nomor", ['nomor' => $nomor]);
    }

    public function getKamarKosong()
    {
        // SQL LEFT JOIN: Mencari kamar yang benar-benar kosong (tidak ada penghuni)
        // SELECT k.* FROM tb_kamar k
        // LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
        // WHERE kp.id IS NULL ORDER BY k.gedung, k.nomor
        //
        // Penjelasan:
        // - LEFT JOIN: ambil semua kamar, termasuk yang tidak punya relasi penghuni
        // - AND kp.tgl_keluar IS NULL: hanya periode penghunian yang masih aktif
        // - WHERE kp.id IS NULL: kamar yang tidak memiliki relasi aktif = kamar kosong
        // - ORDER BY gedung, nomor: urutkan berdasarkan gedung lalu nomor kamar
        $sql = "SELECT k.* FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                WHERE kp.id IS NULL
                ORDER BY k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarTersedia($max_occupants = 2)
    {
        // SQL COMPLEX WITH COUNT & CALCULATIONS: Mencari kamar yang masih tersedia (belum penuh)
        // SELECT k.*, COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
        //        (? - COALESCE(COUNT(dkp.id), 0)) as slot_tersedia
        // FROM tb_kamar k
        // LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
        // LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
        // GROUP BY k.id HAVING slot_tersedia > 0 ORDER BY k.gedung, k.nomor
        //
        // Penjelasan:
        // - COUNT(dkp.id): hitung jumlah penghuni aktif per kamar
        // - COALESCE(..., 0): jika tidak ada penghuni, set ke 0
        // - (? - COUNT(...)): hitung slot tersedia = max_occupants - jumlah_penghuni
        // - GROUP BY k.id: kelompokkan per kamar untuk menghitung COUNT
        // - HAVING slot_tersedia > 0: hanya tampilkan kamar yang masih ada slot
        // - Parameter [$max_occupants]: maksimal penghuni per kamar (default 2)
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
                       (? - COALESCE(COUNT(dkp.id), 0)) as slot_tersedia
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.id
                HAVING slot_tersedia > 0
                ORDER BY k.gedung, k.nomor";
        
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
                ORDER BY k.gedung, k.nomor";
        
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
                ORDER BY k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql, [$max_occupants]);
    }

    public function getKamarWithBasicStatus($max_occupants = 2)
    {
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(dkp.id), 0) as jumlah_penghuni,
                       CASE 
                           WHEN COUNT(dkp.id) = 0 THEN 'kosong'
                           WHEN COUNT(dkp.id) < ? THEN 'tersedia'
                           ELSE 'penuh'
                       END as status
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.id
                ORDER BY k.gedung, k.nomor";
        
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

    public function getStatistikPerGedung()
    {
        $sql = "SELECT k.gedung,
                       COUNT(k.id) as total_kamar,
                       COALESCE(COUNT(CASE WHEN dkp.id IS NOT NULL THEN 1 END), 0) as kamar_terisi,
                       COALESCE(COUNT(CASE WHEN dkp.id IS NULL THEN 1 END), 0) as kamar_kosong,
                       MIN(k.harga) as harga_terendah,
                       MAX(k.harga) as harga_tertinggi,
                       AVG(k.harga) as harga_rata_rata
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.gedung
                ORDER BY k.gedung";
        
        return $this->db->fetchAll($sql);
    }

    public function getStatistikKamarKosongPerGedung()
    {
        $sql = "SELECT k.gedung,
                       COUNT(k.id) as jumlah_tersedia,
                       MIN(k.harga) as harga_terendah,
                       MAX(k.harga) as harga_tertinggi,
                       AVG(k.harga) as harga_rata_rata
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                WHERE kp.id IS NULL
                GROUP BY k.gedung
                ORDER BY k.gedung";
        
        return $this->db->fetchAll($sql);
    }

    public function getGedungList()
    {
        $sql = "SELECT DISTINCT gedung FROM tb_kamar ORDER BY gedung";
        return $this->db->fetchAll($sql);
    }

    public function getKamarWithAllOccupantsAndBelongings($max_occupants = 2)
    {
        // First get all rooms with basic info
        $sql = "SELECT k.*, 
                       COALESCE(COUNT(DISTINCT dkp.id), 0) as jumlah_penghuni,
                       CASE 
                           WHEN COUNT(DISTINCT dkp.id) = 0 THEN 'kosong'
                           WHEN COUNT(DISTINCT dkp.id) < ? THEN 'tersedia'
                           ELSE 'penuh'
                       END as status,
                       kp.tgl_masuk, kp.id as id_kmr_penghuni
                FROM tb_kamar k
                LEFT JOIN tb_kmr_penghuni kp ON k.id = kp.id_kamar AND kp.tgl_keluar IS NULL
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                GROUP BY k.id, kp.id, kp.tgl_masuk
                ORDER BY k.gedung, k.nomor";
        
        $rooms = $this->db->fetchAll($sql, [$max_occupants]);
        
        // Now get detailed occupant and belongings info for each room
        foreach ($rooms as &$room) {
            $room['penghuni_list'] = [];
            $room['nama_penghuni'] = '';
            
            if ($room['id_kmr_penghuni']) {
                // Get all occupants for this room
                $occupantSql = "SELECT dkp.*, p.nama, p.no_ktp, p.no_hp, p.id as id_penghuni
                               FROM tb_detail_kmr_penghuni dkp
                               INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                               WHERE dkp.id_kmr_penghuni = ? AND dkp.tgl_keluar IS NULL
                               ORDER BY dkp.tgl_masuk";
                
                $occupants = $this->db->fetchAll($occupantSql, [$room['id_kmr_penghuni']]);
                
                $occupantNames = [];
                foreach ($occupants as $occupant) {
                    // Get belongings for each occupant
                    $belongingsSql = "SELECT bb.*, b.nama as nama_barang, b.harga as harga_barang
                                     FROM tb_brng_bawaan bb
                                     INNER JOIN tb_barang b ON bb.id_barang = b.id
                                     WHERE bb.id_penghuni = ?";
                    
                    $belongings = $this->db->fetchAll($belongingsSql, [$occupant['id_penghuni']]);
                    $occupant['barang_bawaan'] = $belongings;
                    
                    $room['penghuni_list'][] = $occupant;
                    $occupantNames[] = $occupant['nama'];
                }
                
                $room['nama_penghuni'] = implode(', ', $occupantNames);
                
                // Collect all belongings for display
                $allBelongings = [];
                foreach ($room['penghuni_list'] as $occupant) {
                    foreach ($occupant['barang_bawaan'] as $belonging) {
                        $allBelongings[] = $belonging;
                    }
                }
                $room['barang_bawaan'] = $allBelongings;
            } else {
                $room['barang_bawaan'] = [];
            }
        }
        
        return $rooms;
    }
}