# Setup Sistem Authentication

## Yang Sudah Ditambahkan

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

## Cara Setup

### 1. Install PHP (jika belum ada)
```bash
# Ubuntu/Debian
sudo apt update && sudo apt install -y php php-mysql

# CentOS/RHEL
sudo yum install php php-mysql

# Windows (XAMPP/WAMP sudah include PHP)
```

### 2. Jalankan Setup Database
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

### 3. Test Login
1. Akses `/login` di browser
2. Masuk dengan username: `admin` dan password: `admin123`
3. Setelah login berhasil, akan redirect ke dashboard admin

### 4. Ganti Password Default
**PENTING:** Segera ganti password default setelah login pertama!

## Struktur Tabel Users

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

## Fitur Authentication

### Login
- Akses: `/login`
- Validasi username/password
- Session management
- Remember login status

### Logout
- Akses: `/logout`
- Destroy session
- Redirect ke login page

### Protected Routes
Semua route `/admin/*` sekarang dilindungi:
- `/admin` - Dashboard
- `/admin/penghuni` - Kelola Penghuni  
- `/admin/kamar` - Kelola Kamar
- `/admin/barang` - Kelola Barang
- `/admin/tagihan` - Kelola Tagihan
- `/admin/pembayaran` - Pembayaran

### Session Management
- Auto logout jika session expired
- Login time tracking
- User info tersimpan di session

## Security Features

✅ **Password Hashing** - Menggunakan PHP `password_hash()`
✅ **SQL Injection Protection** - Prepared statements
✅ **XSS Protection** - `htmlspecialchars()` pada output
✅ **Session Security** - Proper session handling
✅ **Input Validation** - Server-side validation

## Troubleshooting

### Database Connection Error
Pastikan config database di `config/config.php` sudah benar:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_kost');  
define('DB_USER', 'cecep');
define('DB_PASS', 'Cecep@1982');
```

### Login Gagal
1. Pastikan setup database sudah dijalankan via `/install/run`
2. Cek username/password: `admin` / `admin123`
3. Pastikan tabel users sudah ada di database

### Redirect Loop
Jika terjadi redirect loop, hapus session:
```php
// Temporary fix - hapus di index.php setelah session_start()
session_destroy();
```

## Next Steps

1. **Ganti Password Default** - Buat halaman change password
2. **User Management** - Tambah CRUD untuk kelola user
3. **Role-based Access** - Implementasi permission berdasarkan role
4. **Remember Me** - Implementasi "Ingat Saya" functionality
5. **Password Reset** - Implementasi reset password via email