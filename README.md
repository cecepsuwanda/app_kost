# Sistem Manajemen Kos

Aplikasi web berbasis PHP untuk mengelola kos (boarding house) dengan fitur lengkap untuk mengelola penghuni, kamar, tagihan, dan pembayaran. Dibangun menggunakan arsitektur MVC dengan implementasi PSR-4 autoloading dan namespace.

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

### 🔐 Sistem Authentication
- User authentication dengan password hashing
- Session management dan security
- Protected admin routes
- Role-based access control

### 🔧 Fitur Teknis
- Framework MVC PHP murni dengan namespace PSR-4
- Database MySQL/MariaDB
- Responsive design dengan Bootstrap 5
- AJAX untuk operasi cepat
- Installer otomatis untuk setup database
- Custom autoloader dengan namespace support
- Clean code architecture dengan separation of concerns

## Arsitektur Aplikasi

### Namespace Structure

Aplikasi menggunakan PSR-4 autoloading dengan namespace struktur sebagai berikut:

```
App\
├── Core\           # Framework core classes
│   ├── Autoloader  # PSR-4 autoloader
│   ├── Controller  # Base controller class
│   ├── Model       # Base model class
│   ├── Database    # Database singleton class
│   └── Router      # Request routing handler
├── Controllers\    # Application controllers
│   ├── Home        # Home page controller
│   ├── Auth        # Authentication controller
│   ├── Admin       # Admin panel controller
│   └── Install     # Installation controller
└── Models\         # Data models
    ├── PenghuniModel      # Penghuni (resident) model
    ├── KamarModel         # Kamar (room) model
    ├── BarangModel        # Barang (item) model
    ├── KamarPenghuniModel # Room-resident relationship
    ├── BarangBawaanModel  # Resident items model
    ├── TagihanModel       # Billing model
    ├── BayarModel         # Payment model
    └── UserModel          # User authentication model
```

### Database Design

```sql
-- Core Tables
tb_penghuni (id, nama, no_ktp, no_hp, tgl_masuk, tgl_keluar)
tb_kamar (id, nomor, harga)
tb_barang (id, nama, harga)

-- Relationship Tables
tb_kmr_penghuni (id, id_kamar, id_penghuni, tgl_masuk, tgl_keluar)
tb_brng_bawaan (id, id_penghuni, id_barang)

-- Transaction Tables
tb_tagihan (id, bulan, id_kmr_penghuni, jml_tagihan)
tb_bayar (id, id_tagihan, jml_bayar, status)

-- System Tables
users (id, username, password, nama, role, created_at, last_login)
```

## Persyaratan Sistem

- **PHP**: 8.0 atau lebih tinggi
- **Database**: MySQL 5.7+ atau MariaDB 10.3+
- **Web Server**: Apache/Nginx dengan mod_rewrite
- **Extension PHP**:
  - PDO
  - PDO_MySQL
  - JSON
  - Session

## Instalasi

### Quick Setup

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd sistem-manajemen-kos
   ```

2. **Konfigurasi Database**
   
   Edit `config/config.php`:
   ```php
   // Database configuration
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'kos_management');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_CHARSET', 'utf8mb4');

   // Application configuration
   define('APP_NAME', 'Sistem Manajemen Kos');
   define('APP_VERSION', '2.0.0');
   define('APP_URL', 'http://localhost/sistem-kos');
   ```

3. **Setup Web Server**
   
   **Apache (.htaccess sudah included):**
   ```apache
   DocumentRoot /path/to/sistem-manajemen-kos
   ```
   
   **Nginx:**
   ```nginx
   server {
       listen 80;
       root /path/to/sistem-manajemen-kos;
       index index.php;
       
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
           fastcgi_index index.php;
           include fastcgi_params;
       }
   }
   ```
   
   **PHP Built-in Server (Development):**
   ```bash
   php -S localhost:8000
   ```

4. **Install Database**
   - Akses: `http://localhost:8000/install`
   - Klik "Mulai Instalasi"
   - Tunggu proses selesai

5. **Access Application**
   - Frontend: `http://localhost:8000`
   - Admin Panel: `http://localhost:8000/admin`

## Setup Sistem Authentication

### Yang Sudah Ditambahkan

✅ **User Model** (`app/models/UserModel.php`)
- Handling authentication dengan password hashing
- Fungsi untuk create user, login, dan update last login

✅ **Auth Controller** (`app/controllers/Auth.php`) 
- Halaman login dengan validasi
- Logout functionality
- Session management
- Authentication middleware

✅ **Login View** (`app/views/auth/login.php`)
- Design modern dengan Bootstrap 5
- Form login yang responsive
- Error handling

✅ **Admin Protection** 
- Semua halaman admin sekarang memerlukan login
- Auto redirect ke login jika belum login

✅ **Navigation Updates**
- User info dan logout button di navbar
- Dynamic navigation berdasarkan login status

### Cara Setup Authentication

#### 1. Install PHP (jika belum ada)
```bash
# Ubuntu/Debian
sudo apt update && sudo apt install -y php php-mysql

# CentOS/RHEL
sudo yum install php php-mysql

# Windows (XAMPP/WAMP sudah include PHP)
```

#### 2. Jalankan Setup Database
Akses halaman install di browser: `/install/run`

Atau via terminal:
```bash
curl http://localhost/your-app/install/run
```

Setup ini akan:
- Membuat semua tabel database termasuk tabel `users`
- Membuat user admin default dengan credentials:
  - **Username:** `admin`
  - **Password:** `admin123`
- Mengisi sample data untuk testing

#### 3. Test Login
1. Akses `/login` di browser
2. Masuk dengan username: `admin` dan password: `admin123`
3. Setelah login berhasil, akan redirect ke dashboard admin

#### 4. Ganti Password Default
**PENTING:** Segera ganti password default setelah login pertama!

### Struktur Tabel Users

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'superadmin') DEFAULT 'admin',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Fitur Authentication

#### Login
- Akses: `/login`
- Validasi username/password
- Session management
- Remember login status

#### Logout
- Akses: `/logout`
- Destroy session
- Redirect ke login page

#### Protected Routes
Semua route `/admin/*` sekarang dilindungi:
- `/admin` - Dashboard
- `/admin/penghuni` - Kelola Penghuni  
- `/admin/kamar` - Kelola Kamar
- `/admin/barang` - Kelola Barang
- `/admin/tagihan` - Kelola Tagihan
- `/admin/pembayaran` - Pembayaran

#### Session Management
- Auto logout jika session expired
- Login time tracking
- User info tersimpan di session

## Panduan Penggunaan

### Workflow Operasional

#### 1. Setup Awal
1. Install aplikasi via installer
2. Login ke admin panel
3. Setup data master (kamar, barang)
4. Tambah penghuni pertama

#### 2. Penghuni Baru
```
Admin > Kelola Penghuni > Tambah Penghuni
└── Isi data penghuni
└── Pilih kamar (opsional)
└── Pilih barang bawaan (opsional)
└── Simpan
```

#### 3. Generate Tagihan Bulanan
```
Admin > Kelola Tagihan > Generate Tagihan
└── Pilih bulan/tahun
└── Generate (otomatis untuk semua penghuni aktif)
```

#### 4. Pencatatan Pembayaran
```
Admin > Pembayaran > Cari Tagihan
└── Input jumlah pembayaran
└── Status otomatis ter-update (cicil/lunas)
```

#### 5. Operasi Kamar
- **Pindah Kamar**: Admin > Penghuni > [Icon Pindah]
- **Checkout**: Admin > Penghuni > [Icon Checkout]
- **Check-in**: Tambah penghuni baru dengan kamar

### API Endpoints

Aplikasi menggunakan clean URL routing:

```
GET  /                    # Halaman utama
GET  /login              # Halaman login
POST /login              # Proses authentication
GET  /logout             # Logout user
GET  /admin              # Dashboard admin
GET  /admin/penghuni     # Kelola penghuni
GET  /admin/kamar        # Kelola kamar
GET  /admin/barang       # Kelola barang
GET  /admin/tagihan      # Kelola tagihan
GET  /admin/pembayaran   # Kelola pembayaran
GET  /install            # Installer
POST /install/run        # Proses instalasi
POST /ajax               # AJAX handler
```

## Development Guide

### Custom Class Creation

#### Membuat Controller Baru

```php
<?php

namespace App\Controllers;

use App\Core\Controller;

class CustomController extends Controller
{
    public function index()
    {
        $model = $this->loadModel('CustomModel');
        $data = $model->findAll();
        
        $this->loadView('custom/index', ['data' => $data]);
    }
}
```

#### Membuat Model Baru

```php
<?php

namespace App\Models;

use App\Core\Model;

class CustomModel extends Model
{
    protected $table = 'custom_table';
    
    public function customMethod()
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} WHERE custom_condition = 1");
    }
}
```

#### Menambah Route

Edit `index.php`:
```php
$router->add('/custom', 'CustomController@index');
$router->add('/custom/action', 'CustomController@action');
```

### Database Operations

```php
// Basic CRUD
$model->findAll();                    // SELECT *
$model->findById($id);               // SELECT by ID
$model->create($data);               // INSERT
$model->update($id, $data);          // UPDATE
$model->delete($id);                 // DELETE

// Advanced queries
$model->where('status = ?', ['active']);
$model->count('active = 1');

// Raw queries via Database class
$db = Database::getInstance();
$db->query('SELECT * FROM table WHERE condition = ?', [$value]);
$db->fetchAll('SELECT * FROM table');
$db->fetch('SELECT * FROM table LIMIT 1');
```

### View System

```php
// Load view dengan data
$this->loadView('folder/viewname', [
    'title' => 'Page Title',
    'data' => $arrayData
]);

// Layout inheritance
$this->loadView('layouts/main', $data);
```

## Customization

### Theme Customization

1. **CSS Framework**: Bootstrap 5.1.3
2. **Icons**: Bootstrap Icons
3. **Layout**: `app/views/layouts/main.php`
4. **Custom CSS**: Tambahkan di `public/assets/css/`

### Adding New Features

1. **Database**: Tambah tabel via migration atau SQL
2. **Model**: Buat model class dengan namespace `App\Models`
3. **Controller**: Buat controller dengan namespace `App\Controllers`
4. **View**: Buat template di `app/views/`
5. **Route**: Tambah route di `index.php`

### Configuration

Edit `config/config.php` untuk:
- Database connection
- Application settings
- Timezone settings
- Debug mode
- Custom constants

## Security Features

### Authentication Security
✅ **Password Hashing** - Menggunakan PHP `password_hash()`
✅ **SQL Injection Protection** - PDO prepared statements
✅ **XSS Protection** - `htmlspecialchars()` pada output
✅ **Session Security** - Proper session handling
✅ **Input Validation** - Server-side validation

### General Security
- **SQL Injection Protection**: PDO prepared statements
- **Session Management**: Secure session handling
- **Authentication**: Username/password verification
- **CSRF Protection**: Recommended untuk forms
- **Input Validation**: Server-side validation
- **Error Handling**: Custom error pages

## Performance Optimization

1. **Database Indexing**: Index pada foreign keys
2. **Query Optimization**: Efficient SQL queries
3. **Caching**: Session-based caching
4. **Asset Optimization**: Minified CSS/JS
5. **Database Connection**: Singleton pattern

## Troubleshooting

### Authentication Issues

#### Database Connection Error
Pastikan config database di `config/config.php` sudah benar:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_kost');  
define('DB_USER', 'cecep');
define('DB_PASS', 'Cecep@1982');
```

#### Login Gagal
1. Pastikan setup database sudah dijalankan via `/install/run`
2. Cek username/password: `admin` / `admin123`
3. Pastikan tabel users sudah ada di database

#### Redirect Loop
Jika terjadi redirect loop, hapus session:
```php
// Temporary fix - hapus di index.php setelah session_start()
session_destroy();
```

### Common Issues

**Database Connection Error**
```bash
Solution: Check config/config.php database credentials
```

**Class Not Found Error**
```bash
Solution: Ensure namespace declarations and autoloader registration
```

**Permission Denied**
```bash
Solution: Set proper file permissions (644 for files, 755 for directories)
```

**Namespace Resolution Error**
```bash
Solution: Use fully qualified class names or proper use statements
```

## Migration from Non-Namespaced Version

Jika mengupgrade dari versi tanpa namespace:

1. **Backup Database**: Selalu backup sebelum upgrade
2. **Update Files**: Replace semua files dengan versi baru
3. **Check Custom Code**: Update custom code untuk use namespace
4. **Test Functionality**: Test semua fitur setelah upgrade

## Roadmap & Next Steps

### Authentication Enhancements
1. **Ganti Password Default** - Buat halaman change password
2. **User Management** - Tambah CRUD untuk kelola user
3. **Role-based Access** - Implementasi permission berdasarkan role
4. **Remember Me** - Implementasi "Ingat Saya" functionality
5. **Password Reset** - Implementasi reset password via email

## Contributing

1. Fork repository
2. Create feature branch: `git checkout -b feature/AmazingFeature`
3. Follow PSR-4 namespace conventions
4. Write clean, documented code
5. Test your changes
6. Commit: `git commit -m 'Add AmazingFeature'`
7. Push: `git push origin feature/AmazingFeature`
8. Create Pull Request

## Changelog

### Version 2.0.0
- ✅ **NEW**: PSR-4 namespace implementation
- ✅ **NEW**: Enhanced autoloader with namespace support
- ✅ **IMPROVED**: Better code organization and separation of concerns
- ✅ **IMPROVED**: Enhanced documentation and code comments
- ✅ **IMPROVED**: Better error handling and debugging support

### Version 1.0.0
- Initial release with basic MVC structure

## License

Aplikasi ini dilisensikan di bawah [MIT License](LICENSE).

## Support & Documentation

- **Technical Documentation**: README.md (this file)
- **Issues**: GitHub Issues
- **Wiki**: Comprehensive guides and examples

---

**Sistem Manajemen Kos v2.0** - Dibangun dengan ❤️ menggunakan PHP 8.0, PSR-4 Namespaces, dan Bootstrap 5
