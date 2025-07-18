# Perbaikan Sistem Generate Tagihan

## Masalah yang Ditemukan

1. **Tagihan digenerate untuk setiap penghuni kamar** - Sistem sebelumnya membuat tagihan terpisah untuk setiap penghuni di kamar yang sama
2. **Tidak ada validasi periode** - Bisa generate/rekalkulasi tagihan untuk bulan yang sudah lewat atau terlalu jauh ke depan
3. **Duplikasi tagihan** - Beberapa penghuni dalam satu kamar mendapat tagihan terpisah

## Perbaikan yang Dilakukan

### 1. Perubahan Logic Generate Tagihan (`TagihanModel.php`)

**Sebelum:**
- Generate tagihan untuk setiap pasangan kamar-penghuni (`$activeKamarPenghuni`)
- Bisa generate untuk periode apapun

**Sesudah:**
- Generate tagihan per kamar (group by `kp.id_kamar`)
- Satu tagihan per kamar untuk semua penghuni yang tinggal di kamar tersebut
- Validasi periode: hanya bisa generate bulan sekarang atau bulan berikutnya

### 2. Validasi Periode

Ditambahkan validasi pada method:
- `generateTagihan()`
- `recalculateTagihan()`
- `recalculateAllTagihan()`

**Aturan periode:**
- `$monthDiff < 0`: Tidak bisa generate/rekalkulasi bulan yang sudah lewat
- `$monthDiff > 1`: Tidak bisa generate/rekalkulasi bulan yang terlalu jauh ke depan
- Hanya boleh untuk bulan sekarang (monthDiff = 0) atau bulan berikutnya (monthDiff = 1)

### 3. Perbaikan Query Database

**Perubahan di `getTagihanDetail()`:**
- Gunakan `GROUP_CONCAT(DISTINCT p.nama SEPARATOR ', ')` untuk menampilkan semua penghuni
- Tambah `GROUP_CONCAT(DISTINCT p.no_hp SEPARATOR ', ')` untuk nomor HP
- Group by `t.id` bukan `t.id,p.no_hp`

**Perubahan di `getTagihanTerlambat()` dan `getTagihanMendekatiJatuhTempo()`:**
- Konsistensi dalam penggunaan `DISTINCT` pada GROUP_CONCAT

### 4. Perbaikan Controller (`Admin.php`)

**Error Handling:**
- Tambahkan try-catch untuk menangani `InvalidArgumentException`
- Tampilkan pesan error yang sesuai ke user

**Data Processing:**
- Ubah logic untuk mengambil detail semua penghuni per kamar
- Buat array `detail_penghuni` yang berisi info setiap penghuni beserta barang bawaannya

### 5. Perbaikan View (`tagihan.php`)

**Validasi Input:**
- Tambahkan atribut `min` dan `max` pada input month
- Batasi input hanya untuk bulan sekarang dan bulan berikutnya

**Tampilan Barang Bawaan:**
- Tampilkan barang bawaan per penghuni dengan nama penghuni
- Gunakan separator visual untuk membedakan antar penghuni

## Hasil Perbaikan

### 1. Tagihan Per Kamar
- Setiap kamar hanya mendapat 1 tagihan per bulan
- Jumlah tagihan = harga kamar + total harga barang bawaan semua penghuni

### 2. Validasi Periode yang Ketat
- Juli sekarang: bisa generate/rekalkulasi Juli dan Agustus
- Agustus sekarang: tidak bisa generate/rekalkulasi Juli, bisa Agustus dan September
- Tidak bisa generate/rekalkulasi untuk bulan yang sudah lewat atau terlalu jauh

### 3. UI yang Lebih Informatif
- Tampilkan semua penghuni di kamar beserta barang bawaannya
- Pesan error yang jelas untuk periode yang tidak valid
- Input field dengan validasi client-side

## File yang Diubah

1. `app/models/TagihanModel.php` - Logic utama generate dan validasi
2. `app/controllers/Admin.php` - Error handling dan data processing
3. `app/views/admin/tagihan.php` - Validasi input dan tampilan

## Testing

Untuk menguji perbaikan:

1. **Test Generate Tagihan:**
   - Coba generate untuk bulan sekarang ✓
   - Coba generate untuk bulan berikutnya ✓
   - Coba generate untuk bulan lalu (harus error) ✓
   - Coba generate untuk 2 bulan ke depan (harus error) ✓

2. **Test Tagihan Per Kamar:**
   - Pastikan kamar dengan multiple penghuni hanya dapat 1 tagihan
   - Pastikan jumlah tagihan = harga kamar + total barang semua penghuni

3. **Test UI:**
   - Pastikan input month field terbatas periode yang benar
   - Pastikan barang bawaan ditampilkan per penghuni