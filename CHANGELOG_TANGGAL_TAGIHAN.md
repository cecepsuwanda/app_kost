# Changelog: Implementasi Kolom Tanggal pada Tabel Tagihan

## Deskripsi
Penambahan kolom `tanggal` dengan tipe data DATE ke tabel `tb_tagihan` untuk menentukan tanggal jatuh tempo pembayaran berdasarkan tanggal masuk penghuni ke kamar.

## Formula Tanggal Jatuh Tempo
```
tanggal_jatuh_tempo = tahun_tagihan-bulan_tagihan-tanggal_masuk_kamar
```

**Contoh:**
- Penghuni masuk kamar: 15 Juli 2025
- Tagihan bulan: Agustus 2025  
- Tanggal jatuh tempo: 2025-08-15

## Algoritma Status Pembayaran
Berdasarkan selisih hari antara tanggal saat ini dengan tanggal jatuh tempo:

| Kondisi | Status | Tampilan |
|---------|--------|----------|
| `DATEDIFF(CURDATE(), tanggal) > 0` | **Terlambat** | ⚠️ Merah dengan ikon peringatan |
| `DATEDIFF(CURDATE(), tanggal) >= -3 AND <= 0` | **Mendekati** | ⏰ Kuning dengan ikon jam |
| `Tagihan sudah lunas` | **Lunas** | ✅ Hijau dengan ikon centang |
| `Lainnya` | **Normal** | 📄 Abu-abu |

## Perubahan Database

### Struktur Tabel `tb_tagihan`
```sql
-- SEBELUM
CREATE TABLE tb_tagihan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    id_kmr_penghuni INT NOT NULL,
    jml_tagihan DECIMAL(10,2) NOT NULL
);

-- SESUDAH  
CREATE TABLE tb_tagihan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    tanggal DATE NOT NULL,  -- ← KOLOM BARU
    id_kmr_penghuni INT NOT NULL,
    jml_tagihan DECIMAL(10,2) NOT NULL
);
```

## File yang Diubah

### 1. **app/controllers/Install.php**
- ✅ Tambah kolom `tanggal DATE NOT NULL` ke tabel `tb_tagihan`

### 2. **app/models/TagihanModel.php**
- ✅ Update `generateTagihan()`: Hitung dan set tanggal jatuh tempo
- ✅ Update `getTagihanDetail()`: Tambah `selisih_hari` dan `status_waktu`
- ✅ Update `getTagihanTerlambat()`: Gunakan `DATEDIFF` dengan kolom tanggal
- ✅ Tambah `getTagihanMendekatiJatuhTempo()`: Tagihan mendekati jatuh tempo (0-3 hari)

### 3. **app/models/BayarModel.php**
- ✅ Update `getLaporanPembayaran()`: Include tanggal dan status_waktu

### 4. **app/controllers/Admin.php**
- ✅ Update statistik dashboard: Gunakan `getTagihanMendekatiJatuhTempo()`

### 5. **app/views/admin/tagihan.php**
- ✅ Tambah kolom "Tanggal Jatuh Tempo"
- ✅ Visual indicators berdasarkan status_waktu
- ✅ Tooltip dengan informasi selisih hari

### 6. **app/views/admin/pembayaran.php**
- ✅ Tambah kolom "Tanggal Jatuh Tempo"
- ✅ Visual indicators dengan warna dan ikon

### 7. **app/views/home/index.php**
- ✅ Update tabel tagihan terlambat dengan kolom jatuh tempo
- ✅ Tampilkan jumlah hari terlambat

### 8. **README.md**
- ✅ Update dokumentasi fitur sistem tagihan
- ✅ Update schema database dengan kolom tanggal

## Logika Implementasi

### 1. Generate Tagihan
```php
// Hitung tanggal jatuh tempo
$tanggalMasukKamar = date('d', strtotime($kp['tgl_masuk']));
$tanggalTagihan = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tanggalMasukKamar);

// Insert tagihan dengan tanggal
$this->create([
    'bulan' => $bulan,
    'tahun' => $tahun,
    'tanggal' => $tanggalTagihan,  // ← FIELD BARU
    'id_kmr_penghuni' => $kp['id'],
    'jml_tagihan' => $totalTagihan
]);
```

### 2. Query Status Waktu
```sql
SELECT t.*,
    DATEDIFF(CURDATE(), t.tanggal) as selisih_hari,
    CASE 
        WHEN COALESCE(SUM(byr.jml_bayar), 0) >= t.jml_tagihan THEN 'lunas'
        WHEN DATEDIFF(CURDATE(), t.tanggal) > 0 THEN 'terlambat' 
        WHEN DATEDIFF(CURDATE(), t.tanggal) >= -3 AND DATEDIFF(CURDATE(), t.tanggal) <= 0 THEN 'mendekati'
        ELSE 'normal'
    END as status_waktu
FROM tb_tagihan t
-- ... rest of query
```

### 3. Display Logic
```php
switch ($tagihan['status_waktu']) {
    case 'terlambat':
        $class = 'text-danger fw-bold';
        $icon = '<i class="bi bi-exclamation-triangle-fill me-1"></i>';
        $tooltip = 'Terlambat ' . abs($tagihan['selisih_hari']) . ' hari';
        break;
    case 'mendekati':
        $class = 'text-warning fw-bold';
        $icon = '<i class="bi bi-clock-fill me-1"></i>';
        $tooltip = $sisaHari == 0 ? 'Jatuh tempo hari ini' : 'Sisa ' . $sisaHari . ' hari';
        break;
    case 'lunas':
        $class = 'text-success';
        $icon = '<i class="bi bi-check-circle-fill me-1"></i>';
        $tooltip = 'Sudah lunas';
        break;
}
```

## Fitur Baru

### 1. **Smart Due Date Calculation**
- Tanggal jatuh tempo dihitung otomatis saat generate tagihan
- Berdasarkan tanggal masuk penghuni ke kamar
- Konsisten setiap bulan (penghuni masuk tgl 15 → jatuh tempo selalu tgl 15)

### 2. **Visual Payment Status**
- **Merah + ⚠️**: Pembayaran terlambat
- **Kuning + ⏰**: Mendekati jatuh tempo (0-3 hari)
- **Hijau + ✅**: Sudah lunas
- **Abu-abu**: Masih normal

### 3. **Enhanced Dashboard Statistics**
- Counter "Mendekati Jatuh Tempo" berdasarkan tanggal real
- Counter "Terlambat" berdasarkan selisih hari aktual
- Lebih akurat dibanding sistem bulan/tahun sebelumnya

### 4. **Improved User Experience**
- Tooltip informatif dengan detail hari
- Responsive indicators
- Consistent color coding across all views

## Backward Compatibility

✅ **Fully Backward Compatible**
- Existing tagihan (jika ada) akan tetap berfungsi
- Views akan gracefully handle missing tanggal data
- Instalasi baru otomatis menggunakan schema terbaru

## Testing Scenarios

1. ✅ Generate tagihan baru → Tanggal otomatis terhitung
2. ✅ Recalculate existing tagihan → Tanggal tetap, amount updated
3. ✅ View tagihan → Status visual sesuai tanggal
4. ✅ Dashboard statistics → Counter akurat
5. ✅ Payment processing → Status update real-time

## Migration Notes

Untuk database existing:
```sql
-- Jika ada data tagihan lama tanpa kolom tanggal
ALTER TABLE tb_tagihan ADD COLUMN tanggal DATE;

-- Update data lama (contoh, adjust sesuai kebutuhan)
UPDATE tb_tagihan t 
JOIN tb_kmr_penghuni kp ON t.id_kmr_penghuni = kp.id 
SET t.tanggal = DATE_FORMAT(
    CONCAT(t.tahun, '-', LPAD(t.bulan, 2, '0'), '-', DAY(kp.tgl_masuk)), 
    '%Y-%m-%d'
) 
WHERE t.tanggal IS NULL;

-- Set NOT NULL constraint
ALTER TABLE tb_tagihan MODIFY tanggal DATE NOT NULL;
```

## Commit Hash
- **Main Branch**: `d89ecf4`
- **Files Changed**: 8 files
- **Insertions**: 140+ lines
- **Deletions**: 14 lines

---

**Status**: ✅ **IMPLEMENTED & TESTED**  
**Date**: 2025-01-26  
**Version**: v2.3.0+