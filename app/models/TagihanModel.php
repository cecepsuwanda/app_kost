<?php

namespace App\Models;

use App\Core\Model;

class TagihanModel extends Model
{
    protected $table = 'tb_tagihan';

    public function findByBulanTahun($bulan, $tahun)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE bulan = :bulan AND tahun = :tahun", 
                                 ['bulan' => $bulan, 'tahun' => $tahun]);
    }

    public function findByKamarPenghuni($id_kmr_penghuni)
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE id_kmr_penghuni = :id_kmr_penghuni", 
                                 ['id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function findByBulanTahunKamarPenghuni($bulan, $tahun, $id_kmr_penghuni)
    {
        return $this->db->fetch("SELECT * FROM {$this->table} WHERE bulan = :bulan AND tahun = :tahun AND id_kmr_penghuni = :id_kmr_penghuni", 
                               ['bulan' => $bulan, 'tahun' => $tahun, 'id_kmr_penghuni' => $id_kmr_penghuni]);
    }

    public function generateTagihan($periode)
    {
        // Parse periode (format: YYYY-MM) to extract bulan and tahun
        $date = date_create_from_format('Y-m', $periode);
        if (!$date) {
            throw new \InvalidArgumentException("Invalid periode format. Expected YYYY-MM");
        }
        
        $bulan = (int)$date->format('n'); // 1-12
        $tahun = (int)$date->format('Y'); // YYYY

        // Validasi periode - hanya bisa generate bulan sekarang dan bulan berikutnya
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        // Hitung selisih bulan dari bulan sekarang
        $monthDiff = ($tahun - $currentYear) * 12 + ($bulan - $currentMonth);
        
        // Hanya boleh generate untuk bulan sekarang (0) atau bulan berikutnya (1)
        if ($monthDiff < 0) {
            throw new \InvalidArgumentException("Tidak bisa generate tagihan untuk bulan yang sudah lewat");
        }
        
        if ($monthDiff > 1) {
            throw new \InvalidArgumentException("Tidak bisa generate tagihan untuk bulan yang terlalu jauh ke depan");
        }

        // Get all active kamar with their penghuni
        $kamarModel = new \App\Models\KamarModel();
        $detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();
        $barangModel = new \App\Models\BarangModel();
        
        // Get all active kamar penghuni (group by kamar)
        $sql = "SELECT kp.id, kp.id_kamar, kp.tgl_masuk, k.harga as harga_kamar
                FROM tb_kmr_penghuni kp
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE kp.tgl_keluar IS NULL
                GROUP BY kp.id_kamar
                ORDER BY k.nomor";
        
        $activeKamarList = $this->db->fetchAll($sql);

        $generated = 0;
        foreach ($activeKamarList as $kamar) {
            // Check if tagihan already exists for this month, year, and kamar_penghuni
            $existing = $this->findByBulanTahunKamarPenghuni($bulan, $tahun, $kamar['id']);
            if ($existing) {
                continue; // Skip if already generated
            }

            // Get all active penghuni in this kamar
            $penghuniList = $detailKamarPenghuniModel->findActiveByKamarPenghuni($kamar['id']);
            
            if (empty($penghuniList)) {
                continue; // Skip if no active penghuni
            }
            
            // Get the earliest entry date (tgl_masuk) from penghuni table for this kamar
            $sqlEarliestDate = "SELECT MIN(p.tgl_masuk) as earliest_tgl_masuk
                               FROM tb_detail_kmr_penghuni dkp
                               INNER JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                               WHERE dkp.id_kmr_penghuni = ? AND dkp.tgl_keluar IS NULL";
            
            $earliestDateResult = $this->db->fetch($sqlEarliestDate, [$kamar['id']]);
            $earliestTglMasuk = $earliestDateResult['earliest_tgl_masuk'];
            
            // Calculate total harga barang for all penghuni in this kamar
            $totalHargaBarang = 0;
            foreach ($penghuniList as $penghuni) {
                $totalHargaBarang += $barangModel->getTotalHargaBarangPenghuni($penghuni['id_penghuni']);
            }
            
            $totalTagihan = $kamar['harga_kamar'] + $totalHargaBarang;

            // Calculate tanggal tagihan using earliest penghuni entry date
            $tanggalMasukPenghuni = date('d', strtotime($earliestTglMasuk));
            $tanggalTagihan = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tanggalMasukPenghuni);
            
            // Create tagihan (satu tagihan per kamar)
            $this->create([
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggal' => $tanggalTagihan,
                'id_kmr_penghuni' => $kamar['id'],
                'jml_tagihan' => $totalTagihan
            ]);

            $generated++;
        }

        return $generated;
    }

    public function recalculateTagihan($id_tagihan)
    {
        // Get tagihan details
        $tagihan = $this->find($id_tagihan);
        if (!$tagihan) {
            return false;
        }

        // Validasi periode - hanya bisa recalculate bulan sekarang dan bulan berikutnya
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        // Hitung selisih bulan dari bulan sekarang
        $monthDiff = ($tagihan['tahun'] - $currentYear) * 12 + ($tagihan['bulan'] - $currentMonth);
        
        // Hanya boleh recalculate untuk bulan sekarang (0) atau bulan berikutnya (1)
        if ($monthDiff < 0) {
            throw new \InvalidArgumentException("Tidak bisa recalculate tagihan untuk bulan yang sudah lewat");
        }
        
        if ($monthDiff > 1) {
            throw new \InvalidArgumentException("Tidak bisa recalculate tagihan untuk bulan yang terlalu jauh ke depan");
        }

        // Get kamar penghuni details
        $kmrPenghuniModel = new \App\Models\KamarPenghuniModel();
        $kamarPenghuni = $kmrPenghuniModel->find($tagihan['id_kmr_penghuni']);
        if (!$kamarPenghuni) {
            return false;
        }

        // Get room price
        $kamarModel = new \App\Models\KamarModel();
        $kamar = $kamarModel->find($kamarPenghuni['id_kamar']);
        if (!$kamar) {
            return false;
        }

        // Calculate total barang bawaan for all penghuni in this kamar
        $barangModel = new \App\Models\BarangModel();
        $detailKamarPenghuniModel = new \App\Models\DetailKamarPenghuniModel();
        $penghuniList = $detailKamarPenghuniModel->findActiveByKamarPenghuni($tagihan['id_kmr_penghuni']);
        
        $totalHargaBarang = 0;
        foreach ($penghuniList as $penghuni) {
            $totalHargaBarang += $barangModel->getTotalHargaBarangPenghuni($penghuni['id_penghuni']);
        }
        
        $newTotalTagihan = $kamar['harga'] + $totalHargaBarang;

        // Update tagihan with new calculated amount
        $result = $this->update($id_tagihan, [
            'jml_tagihan' => $newTotalTagihan
        ]);

        return $result ? $newTotalTagihan : false;
    }

    public function recalculateAllTagihan($periode)
    {
        // Parse periode (format: YYYY-MM) to extract bulan and tahun
        $date = date_create_from_format('Y-m', $periode);
        if (!$date) {
            throw new \InvalidArgumentException("Invalid periode format. Expected YYYY-MM");
        }
        
        $bulan = (int)$date->format('n'); // 1-12
        $tahun = (int)$date->format('Y'); // YYYY

        // Validasi periode - hanya bisa recalculate bulan sekarang dan bulan berikutnya
        $currentMonth = (int)date('n');
        $currentYear = (int)date('Y');
        
        // Hitung selisih bulan dari bulan sekarang
        $monthDiff = ($tahun - $currentYear) * 12 + ($bulan - $currentMonth);
        
        // Hanya boleh recalculate untuk bulan sekarang (0) atau bulan berikutnya (1)
        if ($monthDiff < 0) {
            throw new \InvalidArgumentException("Tidak bisa recalculate tagihan untuk bulan yang sudah lewat");
        }
        
        if ($monthDiff > 1) {
            throw new \InvalidArgumentException("Tidak bisa recalculate tagihan untuk bulan yang terlalu jauh ke depan");
        }

        // Get all tagihan for the specified periode
        $allTagihan = $this->findByBulanTahun($bulan, $tahun);
        
        $recalculated = 0;
        foreach ($allTagihan as $tagihan) {
            $result = $this->recalculateTagihan($tagihan['id']);
            if ($result !== false) {
                $recalculated++;
            }
        }

        return $recalculated;
    }

    public function getTagihanDetail($periode = null)
    {
        $whereCondition = "";
        $params = [];
        
        if ($periode) {
            // Parse periode (format: YYYY-MM) to extract bulan and tahun
            $date = date_create_from_format('Y-m', $periode);
            if ($date) {
                $bulan = (int)$date->format('n'); // 1-12
                $tahun = (int)$date->format('Y'); // YYYY
                $whereCondition = "WHERE t.bulan = :bulan AND t.tahun = :tahun ";
                $params = ['bulan' => $bulan, 'tahun' => $tahun];
            }
        }

        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar,
                       GROUP_CONCAT(DISTINCT p.nama SEPARATOR ', ') as nama_penghuni,
                       GROUP_CONCAT(DISTINCT p.no_hp SEPARATOR ', ') as no_hp,
                       GROUP_CONCAT(DISTINCT p.tgl_masuk SEPARATOR ', ') as tgl_masuk_penghuni,
                       k.nomor as nomor_kamar, k.gedung, k.harga as harga_kamar,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar,
                       DATEDIFF(CURDATE(), t.tanggal) as selisih_hari,
                       DATEDIFF(t.tanggal, (SELECT MIN(p2.tgl_masuk) FROM tb_detail_kmr_penghuni dkp2 
                                           INNER JOIN tb_penghuni p2 ON dkp2.id_penghuni = p2.id 
                                           WHERE dkp2.id_kmr_penghuni = t.id_kmr_penghuni 
                                           AND dkp2.tgl_keluar IS NULL)) as selisih_dari_tgl_masuk_penghuni,
                       CASE 
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) > 0 THEN 'Cicil'
                           ELSE 'Belum Bayar'
                       END as status_bayar,
                       CASE 
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'lunas'
                           WHEN DATEDIFF(t.tanggal, (SELECT MIN(p2.tgl_masuk) FROM tb_detail_kmr_penghuni dkp2 
                                                     INNER JOIN tb_penghuni p2 ON dkp2.id_penghuni = p2.id 
                                                     WHERE dkp2.id_kmr_penghuni = t.id_kmr_penghuni 
                                                     AND dkp2.tgl_keluar IS NULL)) < 0 THEN 'terlambat'
                           WHEN DATEDIFF(t.tanggal, (SELECT MIN(p2.tgl_masuk) FROM tb_detail_kmr_penghuni dkp2 
                                                     INNER JOIN tb_penghuni p2 ON dkp2.id_penghuni = p2.id 
                                                     WHERE dkp2.id_kmr_penghuni = t.id_kmr_penghuni 
                                                     AND dkp2.tgl_keluar IS NULL)) BETWEEN 0 AND 3 THEN 'mendekati'
                           ELSE 'normal'
                       END as status_waktu
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan
                " . $whereCondition . "
                GROUP BY t.id
                ORDER BY t.tahun DESC, t.bulan DESC, k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getTagihanTerlambat()
    {
        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar, 
                       GROUP_CONCAT(DISTINCT p.nama SEPARATOR ', ') as nama_penghuni, 
                       GROUP_CONCAT(DISTINCT p.no_hp SEPARATOR ', ') as no_hp,
                       GROUP_CONCAT(DISTINCT p.tgl_masuk SEPARATOR ', ') as tgl_masuk_penghuni,
                       k.nomor as nomor_kamar, k.gedung,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar,
                       DATEDIFF(CURDATE(), t.tanggal) as selisih_hari,
                       DATEDIFF(t.tanggal, (SELECT MIN(p2.tgl_masuk) FROM tb_detail_kmr_penghuni dkp2 
                                           INNER JOIN tb_penghuni p2 ON dkp2.id_penghuni = p2.id 
                                           WHERE dkp2.id_kmr_penghuni = t.id_kmr_penghuni 
                                           AND dkp2.tgl_keluar IS NULL)) as selisih_dari_tgl_masuk_penghuni
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan                
                WHERE DATEDIFF(t.tanggal, (SELECT MIN(p2.tgl_masuk) FROM tb_detail_kmr_penghuni dkp2 
                                           INNER JOIN tb_penghuni p2 ON dkp2.id_penghuni = p2.id 
                                           WHERE dkp2.id_kmr_penghuni = t.id_kmr_penghuni 
                                           AND dkp2.tgl_keluar IS NULL)) < 0
                GROUP BY t.id
                HAVING COALESCE(SUM(byr.jml_bayar), 0) < t.jml_tagihan
                ORDER BY t.tanggal DESC, k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getTagihanMendekatiJatuhTempo()
    {
        $sql = "SELECT t.*, kp.tgl_masuk as tgl_masuk_kamar, 
                       GROUP_CONCAT(DISTINCT p.nama SEPARATOR ', ') as nama_penghuni, 
                       GROUP_CONCAT(DISTINCT p.no_hp SEPARATOR ', ') as no_hp,
                       GROUP_CONCAT(DISTINCT p.tgl_masuk SEPARATOR ', ') as tgl_masuk_penghuni,
                       k.nomor as nomor_kamar, k.gedung,
                       COALESCE(SUM(byr.jml_bayar), 0) as jml_dibayar,
                       DATEDIFF(CURDATE(), t.tanggal) as selisih_hari,
                       DATEDIFF(t.tanggal, (SELECT MIN(p2.tgl_masuk) FROM tb_detail_kmr_penghuni dkp2 
                                           INNER JOIN tb_penghuni p2 ON dkp2.id_penghuni = p2.id 
                                           WHERE dkp2.id_kmr_penghuni = t.id_kmr_penghuni 
                                           AND dkp2.tgl_keluar IS NULL)) as selisih_dari_tgl_masuk_penghuni
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan                
                WHERE DATEDIFF(t.tanggal, (SELECT MIN(p2.tgl_masuk) FROM tb_detail_kmr_penghuni dkp2 
                                           INNER JOIN tb_penghuni p2 ON dkp2.id_penghuni = p2.id 
                                           WHERE dkp2.id_kmr_penghuni = t.id_kmr_penghuni 
                                           AND dkp2.tgl_keluar IS NULL)) BETWEEN 0 AND 3
                GROUP BY t.id
                HAVING COALESCE(SUM(byr.jml_bayar), 0) < t.jml_tagihan
                ORDER BY t.tanggal DESC, k.gedung, k.nomor";
        
        return $this->db->fetchAll($sql);
    }

    public function getTotalTagihanPerGedung($periode = null)
    {
        $whereCondition = "";
        $params = [];
        
        if ($periode) {
            $date = date_create_from_format('Y-m', $periode);
            if ($date) {
                $bulan = (int)$date->format('n');
                $tahun = (int)$date->format('Y');
                $whereCondition = "WHERE t.bulan = :bulan AND t.tahun = :tahun ";
                $params = ['bulan' => $bulan, 'tahun' => $tahun];
            }
        }

        $sql = "SELECT k.gedung,
                       COUNT(t.id) as jumlah_tagihan,
                       SUM(t.jml_tagihan) as total_tagihan,
                       SUM(COALESCE(byr.total_bayar, 0)) as total_dibayar,
                       SUM(t.jml_tagihan) - SUM(COALESCE(byr.total_bayar, 0)) as sisa_tagihan
                FROM tb_tagihan t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN (
                    SELECT id_tagihan, SUM(jml_bayar) as total_bayar
                    FROM tb_bayar 
                    GROUP BY id_tagihan
                ) byr ON t.id = byr.id_tagihan
                " . $whereCondition . "
                GROUP BY k.gedung
                ORDER BY k.gedung";
        
        return $this->db->fetchAll($sql, $params);
    }
}