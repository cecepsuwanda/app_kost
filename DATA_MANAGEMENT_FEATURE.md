# Fitur Kelola Data (Data Management)

## Deskripsi
Fitur Kelola Data telah berhasil ditambahkan ke Sistem Manajemen Kos. Fitur ini memungkinkan admin untuk mengekspor dan mengimpor data dari/ke database dalam format SQL.

## Fitur yang Ditambahkan

### 1. Menu Navigation
- **Lokasi**: Menu Admin → Kelola Data
- **URL**: `/admin/data-management`
- **Akses**: Hanya untuk user yang sudah login sebagai admin

### 2. Export Data ke SQL
- **Fungsi**: Mengekspor seluruh data dari semua tabel ke file SQL
- **URL**: `/admin/export-sql`
- **Format file**: `kos_data_export_YYYY-MM-DD_HH-mm-ss.sql`
- **Data yang diekspor**:
  - Data Pengguna (users)
  - Data Penghuni (tb_penghuni)
  - Data Kamar (tb_kamar)
  - Data Barang (tb_barang)
  - Data Hunian (tb_kmr_penghuni)
  - Detail Hunian (tb_detail_kmr_penghuni)
  - Barang Bawaan (tb_brng_bawaan)
  - Data Tagihan (tb_tagihan)
  - Data Pembayaran (tb_bayar)

### 3. Import Data dari SQL
- **Fungsi**: Mengimpor data dari file SQL ke database
- **URL**: `/admin/import-sql`
- **Format yang diterima**: File .sql (maksimal 50MB)
- **Validasi**: Format file, ukuran file, dan syntax SQL
- **Keamanan**: Menggunakan transaction untuk keamanan data

## File yang Ditambahkan/Dimodifikasi

### Controller
- **File**: `app/controllers/Admin.php`
- **Method baru**:
  - `dataManagement()` - Menampilkan halaman kelola data
  - `exportSql()` - Mengekspor data ke file SQL
  - `importSql()` - Mengimpor data dari file SQL
  - `parseSqlStatements()` - Parsing SQL statements dengan aman

### View
- **File**: `app/views/admin/data-management.php`
- **Konten**:
  - Interface untuk export data
  - Form upload untuk import data
  - Validasi JavaScript untuk file upload
  - Informasi penggunaan dan tips

### Routing
- **File**: `app/core/Application.php`
- **Routes baru**:
  - `/admin/data-management` → `Admin@dataManagement`
  - `/admin/export-sql` → `Admin@exportSql`
  - `/admin/import-sql` → `Admin@importSql`

### Navigation
- **File**: `app/views/layouts/main.php`
- **Penambahan**:
  - Menu "Kelola Data" di dropdown Admin
  - Menu "Kelola Data" di sidebar navigation

### Core Enhancement
- **File**: `app/core/Controller.php`
- **Penambahan**: Method `db()` untuk akses database connection

### Storage
- **Directory**: `storage/uploads/`
- **Fungsi**: Temporary storage untuk file upload (auto-created)

## Cara Penggunaan

### Export Data:
1. Login sebagai admin
2. Akses menu "Admin" → "Kelola Data"
3. Klik tombol "Download File SQL" pada card Export Data
4. File akan didownload otomatis dengan nama timestamp

### Import Data:
1. Login sebagai admin
2. Akses menu "Admin" → "Kelola Data"
3. Pilih file SQL pada form Import Data
4. Klik tombol "Import Data SQL"
5. Sistem akan memberikan konfirmasi hasil import

## Fitur Keamanan

### Export:
- Hanya user yang sudah login dan memiliki akses admin
- Data disanitasi dengan proper quoting
- Foreign key handling yang aman

### Import:
- Validasi file format (.sql only)
- Validasi ukuran file (maksimal 50MB)
- Transaction-based import untuk data integrity
- SQL parsing yang aman untuk mencegah injection
- Error handling yang komprehensif

## Error Handling

### Export:
- Validasi koneksi database
- Handling untuk tabel yang tidak ada
- Proper error messages

### Import:
- Validasi file upload errors
- SQL syntax error handling
- Transaction rollback pada error
- Detailed error reporting

## Technical Details

### Export Process:
1. Ambil daftar semua tabel
2. Generate CREATE TABLE statements
3. Export data dengan INSERT statements
4. Handle foreign key constraints
5. Generate downloadable file

### Import Process:
1. Validasi file upload
2. Parse SQL statements dengan aman
3. Disable foreign key checks
4. Execute statements dalam transaction
5. Re-enable foreign key checks
6. Commit atau rollback based on success

### SQL Parsing:
- Handle quoted strings dengan benar
- Remove comments (-- dan /* */)
- Split statements pada semicolon
- Filter empty statements

## Browser Compatibility
- Modern browsers dengan JavaScript enabled
- File API support untuk upload validation
- Bootstrap 5 compatible

## File Size Limits
- **Upload**: 50MB maksimal
- **Export**: Tidak ada limit (tergantung ukuran database)
- **Server**: Tergantung konfigurasi PHP (upload_max_filesize, post_max_size)

## Backup Recommendations
- Lakukan export secara berkala sebagai backup
- Test import di environment development sebelum production
- Simpan backup file di lokasi yang aman

## Future Enhancements
- Selective table export/import
- Compressed SQL files (.sql.gz)
- Scheduled automatic backups
- Export to other formats (JSON, CSV)
- Import data validation preview

## Support
Fitur ini terintegrasi penuh dengan sistem existing dan mengikuti arsitektur MVC yang sudah ada. Semua method menggunakan authentication dan authorization yang sama dengan fitur admin lainnya.