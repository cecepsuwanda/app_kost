# Sistem Manajemen Kos

Aplikasi web berbasis PHP untuk mengelola kos (boarding house) dengan fitur lengkap untuk mengelola penghuni, kamar, tagihan, dan pembayaran.

## Fitur Utama

### 📊 Dashboard
- Overview statistik kamar dan penghuni
- Monitoring kamar kosong dan terisi
- Alert tagihan jatuh tempo dan terlambat
- Aksi cepat untuk operasi sehari-hari

### 👥 Manajemen Penghuni
- Tambah, edit, dan hapus data penghuni
- Pencatatan data lengkap (nama, KTP, HP, tanggal masuk/keluar)
- Assign penghuni ke kamar
- Pindah kamar dan checkout penghuni
- Pencatatan barang bawaan

### 🏠 Manajemen Kamar
- Data kamar dengan nomor dan harga sewa
- Status kamar (kosong/terisi)
- Tracking occupancy rates

### 📦 Manajemen Barang
- Master data barang yang dikenai biaya tambahan
- Integrasi dengan penghuni untuk barang bawaan
- Kalkulasi otomatis biaya tambahan

### 💰 Sistem Tagihan
- Generate tagihan bulanan otomatis
- Kalkulasi berdasarkan harga kamar + biaya barang
- Tracking tagihan per periode
- Status pembayaran (lunas/cicil/belum bayar)

### 💳 Manajemen Pembayaran
- Pencatatan pembayaran dengan sistem cicilan
- Tracking pembayaran per tagihan
- Laporan pembayaran
- Auto-update status tagihan

### 🔧 Fitur Teknis
- Framework MVC PHP murni
- Database MySQL/MariaDB
- Responsive design dengan Bootstrap 5
- AJAX untuk operasi cepat
- Installer otomatis untuk setup database

## Struktur Database

### Tabel Utama
```sql
tb_penghuni (id, nama, no_ktp, no_hp, tgl_masuk, tgl_keluar)
tb_kamar (id, nomor, harga)
tb_barang (id, nama, harga)
tb_kmr_penghuni (id, id_kamar, id_penghuni, tgl_masuk, tgl_keluar)
tb_brng_bawaan (id, id_penghuni, id_barang)
tb_tagihan (id, bulan, id_kmr_penghuni, jml_tagihan)
tb_bayar (id, id_tagihan, jml_bayar, status)
```

## Instalasi

### Persyaratan Sistem
- PHP 8.0 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Web server (Apache/Nginx)
- Extension PHP yang diperlukan:
  - PDO
  - PDO_MySQL
  - JSON

### Langkah Instalasi

1. **Clone atau Download Repository**
   ```bash
   git clone <repository-url>
   cd sistem-manajemen-kos
   ```

2. **Konfigurasi Database**
   Edit file `config/config.php` sesuai dengan setting database Anda:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'kos_management');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. **Setup Web Server**
   - Arahkan document root ke folder aplikasi
   - Pastikan mod_rewrite aktif untuk Apache
   - Atau gunakan PHP built-in server untuk testing:
     ```bash
     php -S localhost:8000
     ```

4. **Jalankan Installer**
   - Akses `http://localhost:8000/install`
   - Klik "Mulai Instalasi"
   - Tunggu proses selesai

5. **Selesai!**
   - Akses aplikasi di `http://localhost:8000`
   - Masuk ke admin panel di `http://localhost:8000/admin`

## Panduan Penggunaan

### Menambah Penghuni Baru
1. Masuk ke menu **Admin > Kelola Penghuni**
2. Klik tombol **"Tambah Penghuni"**
3. Isi data lengkap penghuni
4. Pilih kamar (opsional)
5. Pilih barang bawaan (opsional)
6. Klik **"Simpan"**

### Generate Tagihan Bulanan
1. Masuk ke menu **Admin > Kelola Tagihan**
2. Pilih bulan yang akan di-generate
3. Klik **"Generate Tagihan"**
4. Sistem akan otomatis membuat tagihan untuk semua penghuni aktif

### Mencatat Pembayaran
1. Masuk ke menu **Admin > Pembayaran**
2. Cari tagihan yang akan dibayar
3. Klik tombol **"Bayar"**
4. Masukkan jumlah pembayaran
5. Sistem akan otomatis menentukan status (cicil/lunas)

### Pindah Kamar
1. Masuk ke menu **Admin > Kelola Penghuni**
2. Klik ikon **"Pindah"** pada penghuni yang ingin dipindah
3. Pilih kamar tujuan
4. Tentukan tanggal pindah
5. Klik **"Pindah Kamar"**

### Checkout Penghuni
1. Masuk ke menu **Admin > Kelola Penghuni**
2. Klik ikon **"Checkout"** pada penghuni
3. Konfirmasi checkout
4. Sistem akan otomatis:
   - Update tanggal keluar di tb_penghuni
   - Update tanggal keluar di tb_kmr_penghuni
   - Membebaskan kamar

## Struktur Aplikasi

```
├── app/
│   ├── controllers/     # Controller files
│   ├── models/         # Model files
│   ├── views/          # View templates
│   └── core/           # Core framework files
├── config/             # Configuration files
├── public/             # Public assets (CSS, JS, images)
├── index.php           # Main entry point
└── README.md          # This file
```

## API dan Routing

Aplikasi menggunakan sistem routing sederhana:
- `/` - Halaman utama
- `/admin` - Dashboard admin
- `/admin/penghuni` - Kelola penghuni
- `/admin/kamar` - Kelola kamar
- `/admin/barang` - Kelola barang
- `/admin/tagihan` - Kelola tagihan
- `/admin/pembayaran` - Kelola pembayaran
- `/install` - Halaman installer

## Customization

### Mengubah Theme
Edit file `app/views/layouts/main.php` untuk mengubah tampilan dasar aplikasi.

### Menambah Field Baru
1. Ubah struktur database
2. Update model yang sesuai
3. Modifikasi view form
4. Update controller

### Mengubah Logic Bisnis
Edit file model yang sesuai di folder `app/models/`.

## Troubleshooting

### Database Connection Error
- Pastikan MySQL/MariaDB running
- Cek kredensial database di `config/config.php`
- Pastikan database user memiliki privilege yang cukup

### Permission Error
- Pastikan web server memiliki akses read/write ke folder aplikasi
- Cek permission file dan folder

### PHP Error
- Pastikan PHP version minimal 8.0
- Aktifkan extension yang diperlukan
- Cek error log PHP

## Kontribusi

1. Fork repository ini
2. Buat branch feature (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## License

Aplikasi ini dilisensikan di bawah [MIT License](LICENSE).

## Support

Jika mengalami masalah atau membutuhkan bantuan, silakan:
1. Buka issue di repository ini
2. Atau hubungi developer

---

**Sistem Manajemen Kos** - Dibuat dengan ❤️ menggunakan PHP 8.0 dan Bootstrap 5
