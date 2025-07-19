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

        // Get all active kamar penghuni data directly via SQL join
        // This eliminates the need for multiple model instantiations
        $sql = "SELECT kp.id, kp.id_kamar, kp.tgl_masuk, k.harga as harga_kamar
                FROM tb_kmr_penghuni kp
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                WHERE kp.tgl_keluar IS NULL
                GROUP BY kp.id,kp.id_kamar, kp.tgl_masuk, k.harga";
        
        $activeKamarList = $this->db->fetchAll($sql);

        $generated = 0;
        foreach ($activeKamarList as $kamar) {
            // Check if tagihan already exists for this month, year, and kamar_penghuni
            $existing = $this->findByBulanTahunKamarPenghuni($bulan, $tahun, $kamar['id']);
            if ($existing) {
                continue; // Skip if already generated
            }

            // Get all active penghuni in this kamar via direct SQL
            $penghuniSql = "SELECT id_penghuni FROM tb_detail_kmr_penghuni 
                           WHERE id_kmr_penghuni = :id_kmr_penghuni AND tgl_keluar IS NULL";
            $penghuniList = $this->db->fetchAll($penghuniSql, ['id_kmr_penghuni' => $kamar['id']]);
            
            if (empty($penghuniList)) {
                continue; // Skip if no active penghuni
            }
            
            // Use room occupancy entry date (tgl_masuk from tb_kmr_penghuni)
            $tglMasukKamarPenghuni = $kamar['tgl_masuk'];
            
            // Calculate total harga barang for all penghuni in this kamar via direct SQL
            $totalHargaBarang = 0;
            foreach ($penghuniList as $penghuni) {
                $barangSql = "SELECT COALESCE(SUM(b.harga), 0) as total_harga
                             FROM tb_brng_bawaan bb
                             INNER JOIN tb_barang b ON bb.id_barang = b.id
                             WHERE bb.id_penghuni = :id_penghuni";
                $barangResult = $this->db->fetch($barangSql, ['id_penghuni' => $penghuni['id_penghuni']]);
                $totalHargaBarang += $barangResult['total_harga'] ?? 0;
            }
            
            $totalTagihan = $kamar['harga_kamar'] + $totalHargaBarang;

            // Calculate tanggal tagihan using room occupancy entry date
            $tanggalMasukKamarPenghuniDay = date('d', strtotime($tglMasukKamarPenghuni));
            
            // Validate if the date is valid for the target month/year
            // If not valid (e.g., Feb 30), subtract one day from room entry date
            if (!checkdate($bulan, $tanggalMasukKamarPenghuniDay, $tahun)) {
                $tanggalMasukKamarPenghuniDay = $tanggalMasukKamarPenghuniDay - 1;
            }
            
            $tanggalTagihan = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tanggalMasukKamarPenghuniDay);
            
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
        $tagihan = $this->findAll($id_tagihan);
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

        // Get kamar penghuni and kamar details via direct SQL join
        $kamarSql = "SELECT kp.id, kp.id_kamar, k.harga 
                     FROM tb_kmr_penghuni kp 
                     INNER JOIN tb_kamar k ON kp.id_kamar = k.id 
                     WHERE kp.id = :id_kmr_penghuni";
        $kamarData = $this->db->fetch($kamarSql, ['id_kmr_penghuni' => $tagihan['id_kmr_penghuni']]);
        
        if (!$kamarData) {
            return false;
        }

        // Get all active penghuni in this kamar
        $penghuniSql = "SELECT id_penghuni FROM tb_detail_kmr_penghuni 
                       WHERE id_kmr_penghuni = :id_kmr_penghuni AND tgl_keluar IS NULL";
        $penghuniList = $this->db->fetchAll($penghuniSql, ['id_kmr_penghuni' => $tagihan['id_kmr_penghuni']]);
        
        // Calculate total barang bawaan for all penghuni in this kamar via direct SQL
        $totalHargaBarang = 0;
        foreach ($penghuniList as $penghuni) {
            $barangSql = "SELECT COALESCE(SUM(b.harga), 0) as total_harga
                         FROM tb_brng_bawaan bb
                         INNER JOIN tb_barang b ON bb.id_barang = b.id
                         WHERE bb.id_penghuni = :id_penghuni";
            $barangResult = $this->db->fetch($barangSql, ['id_penghuni' => $penghuni['id_penghuni']]);
            $totalHargaBarang += $barangResult['total_harga'] ?? 0;
        }
        
        $newTotalTagihan = $kamarData['harga'] + $totalHargaBarang;

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
                       DATEDIFF(t.tanggal, kp.tgl_masuk) as selisih_dari_tgl_masuk_kamar_penghuni,
                       CASE 
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'Lunas'
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) > 0 THEN 'Cicil'
                           ELSE 'Belum Bayar'
                       END as status_bayar,
                       CASE 
                           WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'lunas'
                           WHEN DATEDIFF(CURDATE(), t.tanggal) < 0 THEN 'terlambat'
                           WHEN DATEDIFF(CURDATE(), t.tanggal) BETWEEN 0 AND 3 THEN 'mendekati'
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
                       DATEDIFF(t.tanggal, kp.tgl_masuk) as selisih_dari_tgl_masuk_kamar_penghuni
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan                
                WHERE DATEDIFF(CURDATE(), kp.tgl_masuk) < 0
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
                       DATEDIFF(t.tanggal, kp.tgl_masuk) as selisih_dari_tgl_masuk_kamar_penghuni
                FROM {$this->table} t
                INNER JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id
                INNER JOIN tb_kamar k ON kp.id_kamar = k.id
                LEFT JOIN tb_detail_kmr_penghuni dkp ON kp.id = dkp.id_kmr_penghuni AND dkp.tgl_keluar IS NULL
                LEFT JOIN tb_penghuni p ON dkp.id_penghuni = p.id
                LEFT JOIN tb_bayar byr ON t.id = byr.id_tagihan                
                WHERE DATEDIFF(CURDATE(), kp.tgl_masuk) BETWEEN 0 AND 3
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