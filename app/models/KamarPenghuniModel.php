<?php

namespace App\Models;

use App\Core\Model;

class KamarPenghuniModel extends Model
{
    protected $table = 'tb_kmr_penghuni';

    public function findActiveByKamar($id_kamar)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE id_kamar = :id_kamar AND tgl_keluar IS NULL", 
                               ['id_kamar' => $id_kamar]);
    }

    public function findKamarByPenghuni($id_penghuni)
    {
        $sql = "SELECT kp.* FROM {$this->table} kp
                INNER JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni
                WHERE dkp.id_penghuni = :id_penghuni AND dkp.tgl_keluar IS NULL AND kp.tgl_keluar IS NULL";
        
        return $this->db->fetch($sql, ['id_penghuni' => $id_penghuni]);
    }

    public function checkoutKamar($id, $tgl_keluar)
    {
        return $this->update($id, ['tgl_keluar' => $tgl_keluar]);
    }

    public function createKamarPenghuni($id_kamar, $tgl_masuk)
    {
        // Create main kamar penghuni record
        $id_kmr_penghuni = $this->create([
            'id_kamar' => $id_kamar,
            'tgl_masuk' => $tgl_masuk
        ]);

        return $id_kmr_penghuni;
    }

    // New method to handle the creation without model dependency
    // The controller should handle the detail records creation separately
    public function createKamarPenghuniWithDetails($id_kamar, $tgl_masuk, $penghuni_ids, $detailKamarPenghuniModel)
    {
        // Create main kamar penghuni record
        $id_kmr_penghuni = $this->createKamarPenghuni($id_kamar, $tgl_masuk);

        // Create detail records for each penghuni using injected model
        foreach ($penghuni_ids as $id_penghuni) {
            $detailKamarPenghuniModel->create([
                'id_kmr_penghuni' => $id_kmr_penghuni,
                'id_penghuni' => $id_penghuni,
                'tgl_masuk' => $tgl_masuk
            ]);
        }

        return $id_kmr_penghuni;
    }

    public function addPenghuniToKamar($id_kmr_penghuni, $id_penghuni, $tgl_masuk, $detailKamarPenghuniModel)
    {
        return $detailKamarPenghuniModel->create([
            'id_kmr_penghuni' => $id_kmr_penghuni,
            'id_penghuni' => $id_penghuni,
            'tgl_masuk' => $tgl_masuk
        ]);
    }

    public function pindahKamar($id_penghuni, $id_kamar_baru, $tgl_pindah, $detailKamarPenghuniModel)
    {
        // Get original room entry date before checkout
        $kamarPenghuniLama = $this->findKamarByPenghuni($id_penghuni);
        $tgl_masuk_kamar_asli = $kamarPenghuniLama ? $kamarPenghuniLama['tgl_masuk'] : $tgl_pindah;
        
        // Checkout dari kamar lama
        $detailKamarPenghuniModel->checkoutPenghuniFromKamar($id_penghuni, $tgl_pindah);

        // Check if original room becomes empty and close it
        if ($kamarPenghuniLama) {
            $remainingPenghuni = $detailKamarPenghuniModel->findActiveByKamarPenghuni($kamarPenghuniLama['id']);
            if (empty($remainingPenghuni)) {
                $this->checkoutKamar($kamarPenghuniLama['id'], $tgl_pindah);
            }
        }

        // Cek apakah kamar baru sudah ada entry aktif
        $kamarPenghuniAktif = $this->findActiveByKamar($id_kamar_baru);
        
        if ($kamarPenghuniAktif) {
            // Tambahkan ke kamar yang sudah ada
            return $this->addPenghuniToKamar($kamarPenghuniAktif['id'], $id_penghuni, $tgl_pindah, $detailKamarPenghuniModel);
        } else {
            // Buat entry kamar baru dengan tanggal masuk yang sama dengan kamar asli
            // untuk menjaga konsistensi billing cycle
            return $this->createKamarPenghuniForTransfer($id_kamar_baru, $tgl_masuk_kamar_asli, $id_penghuni, $tgl_pindah, $detailKamarPenghuniModel);
        }
    }

    public function createKamarPenghuniForTransfer($id_kamar, $tgl_masuk_kamar, $id_penghuni, $tgl_pindah, $detailKamarPenghuniModel)
    {
        // Create main kamar penghuni record with original room entry date
        $id_kmr_penghuni = $this->create([
            'id_kamar' => $id_kamar,
            'tgl_masuk' => $tgl_masuk_kamar
        ]);

        // Create detail record with move date for the occupant
        $detailKamarPenghuniModel->create([
            'id_kmr_penghuni' => $id_kmr_penghuni,
            'id_penghuni' => $id_penghuni,
            'tgl_masuk' => $tgl_pindah
        ]);

        return $id_kmr_penghuni;
    }

    public function getPenghuniKamarActive()
    {
        $sql = "SELECT kp.*, k.nomor as nomor_kamar, k.harga as harga_kamar,
                       GROUP_CONCAT(CONCAT(p.nama, ' (', COALESCE(p.no_ktp, 'No KTP'), ')') SEPARATOR ', ') as penghuni_list,
                       COUNT(dkp.id) as jumlah_penghuni
                FROM {$this->table} kp
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                WHERE kp.tgl_keluar IS NULL
                GROUP BY kp.id
                ORDER BY k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getKamarSewaanMendekatiJatuhTempo($days = 30)
    {
        $sql = "SELECT kp.id as id_kmr_penghuni, 
                       kp.tgl_masuk, kp.tgl_keluar,
                       k.id as id_kamar, k.nomor as nomor_kamar, k.gedung, k.harga,
                       GROUP_CONCAT(DISTINCT p.nama ORDER BY p.nama SEPARATOR ', ') as nama_penghuni,
                       GROUP_CONCAT(DISTINCT p.id ORDER BY p.nama SEPARATOR ',') as id_penghuni,
                       GROUP_CONCAT(DISTINCT p.no_hp ORDER BY p.nama SEPARATOR ', ') as no_hp,
                       t.id as id_tagihan, t.tanggal as tanggal_tagihan,
                       t.bulan, t.tahun, t.jml_tagihan,
                       DATEDIFF(t.tanggal, CURDATE()) as hari_tersisa,
                       COUNT(DISTINCT p.id) as jumlah_penghuni
                FROM {$this->table} kp
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                INNER JOIN tb_tagihan t ON kp.id = t.id_kmr_penghuni
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                WHERE kp.tgl_keluar IS NULL
                AND t.tanggal IS NOT NULL
                AND DATEDIFF(t.tanggal, CURDATE()) BETWEEN 0 AND :days
                GROUP BY kp.id, k.id, k.nomor, k.gedung, k.harga, t.id, t.tanggal, t.bulan, t.tahun, t.jml_tagihan, kp.tgl_masuk, kp.tgl_keluar
                HAVING COUNT(DISTINCT p.id) > 0
                ORDER BY hari_tersisa ASC, k.nomor";
        
        return $this->db->fetchAll($sql, ['days' => $days]);
    }

    public function checkKamarCapacity($id_kamar, $max_occupants = 2, $detailKamarPenghuniModel = null)
    {
        if (!$detailKamarPenghuniModel) {
            throw new \InvalidArgumentException("DetailKamarPenghuniModel must be provided by controller");
        }
        $current_count = $detailKamarPenghuniModel->countActivePenghuniInKamar($id_kamar);
        return $current_count < $max_occupants;
    }


}