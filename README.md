# Sistem Manajemen Kos

Aplikasi web berbasis PHP untuk mengelola kos (boarding house) dengan fitur lengkap untuk mengelola penghuni, kamar, tagihan, dan pembayaran. Dibangun menggunakan arsitektur MVC dengan implementasi PSR-4 autoloading dan namespace.

## Copyright & Credits

**Sistem Manajemen Kos v2.2.0**

© 2024 - Aplikasi ini dikembangkan dengan bantuan **Cursor AI**, editor kode bertenaga artificial intelligence yang memungkinkan pengembangan aplikasi yang efisien dan berkualitas tinggi.

**Development Tools:**
- **Cursor AI**: AI-powered code editor untuk rapid development
- **Claude (Anthropic)**: AI assistant untuk code generation dan refactoring
- **Modern Web Technologies**: PHP 8.0+, Bootstrap 5, MySQL

**Acknowledgments:**
- Terima kasih kepada tim Cursor AI yang telah menyediakan tools revolusioner untuk pengembangan software
- Aplikasi ini memanfaatkan teknologi AI untuk menghasilkan kode yang bersih, terstruktur, dan mengikuti best practices
- Arsitektur MVC dan implementasi PSR-4 dirancang dengan bantuan AI untuk memastikan maintainability dan scalability

**AI-Assisted Development Features:**
- ✅ Automated code refactoring and optimization
- ✅ Intelligent dependency injection implementation  
- ✅ Comprehensive documentation generation
- ✅ Best practices enforcement
- ✅ Clean architecture design patterns

---

*"Built with the power of AI, designed for human needs"*

## Daftar Isi

- [Fitur Utama](#fitur-utama)
  - [📊 Dashboard](#-dashboard)
  - [👥 Manajemen Penghuni](#-manajemen-penghuni)
  - [🏠 Manajemen Kamar](#-manajemen-kamar)
  - [📦 Manajemen Barang](#-manajemen-barang)
  - [💰 Sistem Tagihan](#-sistem-tagihan)
  - [💳 Manajemen Pembayaran](#-manajemen-pembayaran)
  - [🔐 Sistem Authentication](#-sistem-authentication)
  - [🔧 Fitur Teknis](#-fitur-teknis)
- [Recent Implementation Updates](#recent-implementation-updates)
- [Arsitektur Aplikasi](#arsitektur-aplikasi)
  - [Namespace Structure](#namespace-structure)
  - [Database Design](#database-design)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
  - [Quick Setup](#quick-setup)
  - [Manual Setup](#manual-setup)
- [Konfigurasi](#konfigurasi)
- [Penggunaan](#penggunaan)
- [Rekomendasi Perbaikan Arsitektur](#rekomendasi-perbaikan-arsitektur)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [License](#license)
- [Support & Documentation](#support--documentation)
- [Database Migration Summary](#database-migration-summary)

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
- **🆕 Multi-occupancy**: Satu kamar dapat dihuni hingga 2 penghuni
- Pindah kamar dan checkout penghuni
- Pencatatan barang bawaan

### 🏠 Manajemen Kamar
- Data kamar dengan nomor dan harga sewa
- Status kamar (kosong/tersedia/penuh)
- **🆕 Kapasitas kamar**: Otomatis tracking slot tersedia
- Tracking occupancy rates

### 📦 Manajemen Barang
- Master data barang yang dikenai biaya tambahan
- Integrasi dengan penghuni untuk barang bawaan
- Kalkulasi otomatis biaya tambahan

### 💰 Sistem Tagihan
- Generate tagihan bulanan otomatis
- **🆕 Kalkulasi berdasarkan harga kamar + biaya barang untuk semua penghuni**
- **🆕 Tagihan terkumpul per kamar untuk multi-occupancy**
- Tracking tagihan per periode
- Status pembayaran (lunas/cicil/belum bayar)
- **Modal-based interface untuk generate tagihan bulanan**
- **Filter berdasarkan bulan dengan comprehensive billing table**
- **Summary statistics dengan totals dan percentages**
- **Status badges (Lunas/Paid, Cicil/Installment, Belum Bayar/Unpaid)**

### 💳 Manajemen Pembayaran
- Pencatatan pembayaran dengan sistem cicilan
- Tracking pembayaran per tagihan
- Laporan pembayaran
- Auto-update status tagihan
- **Payment recording dengan modal form dan validation**
- **Payment history tracking dengan month-based filtering**
- **Payment summary dashboard dengan breakdown status**
- **Smart form dengan auto-calculation remaining amounts**

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

## Recent Implementation Updates

### Tagihan dan Pembayaran Views Implementation
Telah berhasil diimplementasikan comprehensive views untuk modul **Tagihan** (Billing) dan **Pembayaran** (Payment) yang terintegrasi penuh dengan arsitektur MVC yang ada.

#### Files yang Dibuat:

**1. app/views/admin/tagihan.php**
- Monthly bill generation dengan modal interface
- Month-based filtering system
- Comprehensive billing table dengan tenant info, room details, amounts, dan payment status
- Summary statistics dengan totals dan percentages
- Status badges dan integration dengan payment module

**2. app/views/admin/pembayaran.php**  
- Payment recording dengan modal form dan validation
- Payment history tracking dengan month-based filtering
- Payment summary dashboard dengan status breakdown
- Interactive payment buttons dan automatic calculation

#### Key Features yang Diimplementasikan:

**Tagihan Module:**
- Bill generation dengan modal-based interface
- Automatic calculation berdasarkan room rent + additional items
- Comprehensive listing dengan filter options dan status tracking
- Financial summary dengan total amounts dan payment completion percentage

**Pembayaran Module:**
- Smart form dengan bill selection dan remaining amount calculation
- Complete payment history dengan status monitoring
- Monthly payment summaries dengan status distribution
- Outstanding balance tracking

#### Technical Implementation:

- **UI/UX**: Bootstrap 5 dengan responsive design dan modern interface elements
- **JavaScript**: Interactive functionality dengan AJAX integration
- **Data Processing**: Seamless integration dengan existing MVC architecture
- **Database**: Integration dengan existing models dan database structure
- **Architecture**: PSR-4 autoloading, MVC with dependency injection

### File Structure
```
app/views/admin/
├── tagihan.php      # Billing management interface
├── pembayaran.php   # Payment management interface
├── dashboard.php    # Dashboard utama
├── penghuni.php     # Manajemen penghuni
├── kamar.php        # Manajemen kamar
└── barang.php       # Manajemen barang
```

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
tb_kmr_penghuni (id, id_kamar, tgl_masuk, tgl_keluar)
tb_detail_kmr_penghuni (id, id_kmr_penghuni, id_penghuni, tgl_masuk, tgl_keluar)
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
       server_name localhost;
       root /path/to/sistem-manajemen-kos;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass 127.0.0.1:9000;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```

4. **Install Database**
   
   Akses `/install` di browser atau jalankan:
   ```bash
   php -r "include 'app/controllers/Install.php'; $install = new Install(); $install->run();"
   ```

### Manual Setup

1. **Import Database**
   ```sql
   mysql -u root -p kos_management < database/schema.sql
   ```

2. **Set Permissions**
   ```bash
   chmod 755 -R .
   chmod 644 config/config.php
   ```

3. **Create Admin User**
   ```sql
   INSERT INTO users (username, password, nama, role) 
   VALUES ('admin', '$2y$10$hash_password_here', 'Administrator', 'admin');
   ```

## Konfigurasi

### Environment Variables

Edit `config/config.php` untuk kustomisasi:

```php
// Security
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('PASSWORD_MIN_LENGTH', 6);

// Application
define('DEBUG_MODE', false);
define('TIMEZONE', 'Asia/Jakarta');

// Upload
define('MAX_FILE_SIZE', 2048); // KB
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
```

### Database Configuration

```php
// config/database.php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'kos_management'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]
    ]
];
```

## Penggunaan

### Login Admin

1. Akses aplikasi di browser
2. Login dengan credentials admin
3. Kelola data melalui dashboard admin

### Workflow Operasional

1. **Setup Master Data**
   - Tambah data kamar
   - Tambah data barang

2. **Manajemen Penghuni**
   - Daftarkan penghuni baru
   - Assign ke kamar
   - Catat barang bawaan

3. **Generate Tagihan**
   - Pilih bulan/periode
   - Generate tagihan otomatis
   - Review dan approve

4. **Proses Pembayaran**
   - Input pembayaran
   - Update status tagihan
   - Generate laporan

## Rekomendasi Perbaikan Arsitektur

### ⚠️ Masalah Arsitektur Saat Ini

**Struktur Saat Ini (Router-Centric):**
```php
// index.php - Router sebagai pusat aplikasi
$router = new App\Core\Router();
$router->add('/', 'Home@index');
$router->add('/admin', 'Admin@index');
// ... route definitions
$router->run();
```

**Masalah:**
- Router bertanggung jawab terlalu banyak (routing + application lifecycle)
- Tidak ada central application class untuk dependency injection
- Konfigurasi aplikasi tersebar di berbagai tempat
- Sulit untuk implementasi middleware dan interceptors
- Testing menjadi kompleks karena tight coupling

### ✅ Solusi yang Disarankan: Application-Centric Architecture

**Struktur yang Disarankan:**
```php
// index.php - Application sebagai pusat kontrol
$app = new App\Core\Application();
$app->initialize();
$app->boot();
$app->run();
```

**Implementasi `App\Core\Application` yang Disarankan:**
```php
<?php

namespace App\Core;

class Application
{
    private Router $router;
    private Config $config;
    private Database $database;
    private Session $session;
    
    public function __construct()
    {
        $this->initializeComponents();
    }
    
    public function initialize(): void
    {
        // Initialize configuration
        $this->config = Config::getInstance();
        
        // Initialize session
        $this->session = Session::getInstance();
        
        // Initialize database
        $this->database = Database::getInstance();
        
        // Initialize router
        $this->router = new Router();
    }
    
    public function boot(): void
    {
        // Register routes
        $this->registerRoutes();
        
        // Register middleware
        $this->registerMiddleware();
        
        // Register error handlers
        $this->registerErrorHandlers();
    }
    
    public function run(): void
    {
        try {
            $this->router->run();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    private function registerRoutes(): void
    {
        $this->router->add('/', 'Home@index');
        $this->router->add('/login', 'Auth@login');
        $this->router->add('/logout', 'Auth@logout');
        $this->router->add('/admin', 'Admin@index');
        $this->router->add('/admin/penghuni', 'Admin@penghuni');
        $this->router->add('/admin/kamar', 'Admin@kamar');
        $this->router->add('/admin/barang', 'Admin@barang');
        $this->router->add('/admin/tagihan', 'Admin@tagihan');
        $this->router->add('/admin/pembayaran', 'Admin@pembayaran');
        $this->router->add('/install', 'Install@index');
        $this->router->add('/install/run', 'Install@run');
        
        // Handle AJAX requests
        $request = Request::getInstance();
        if ($request->hasParam('action')) {
            $this->router->add('/ajax', 'Ajax@handle');
        }
    }
    
    private function registerMiddleware(): void
    {
        // Authentication middleware
        // Rate limiting middleware
        // CSRF protection middleware
    }
    
    private function registerErrorHandlers(): void
    {
        // Custom error handlers
    }
    
    private function handleException(\Exception $e): void
    {
        // Centralized exception handling
        error_log($e->getMessage());
        
        if ($this->config->get('debug')) {
            throw $e;
        }
        
        // Show user-friendly error page
        include APP_PATH . '/views/errors/500.php';
    }
    
    // Getter methods for dependency injection
    public function getRouter(): Router { return $this->router; }
    public function getConfig(): Config { return $this->config; }
    public function getDatabase(): Database { return $this->database; }
    public function getSession(): Session { return $this->session; }
}
```

### 🎯 Keuntungan Application-Centric Architecture

1. **Single Responsibility**: Setiap class memiliki tanggung jawab yang jelas
   - `Application`: Application lifecycle dan dependency management
   - `Router`: Hanya routing logic
   
2. **Dependency Injection**: Central container untuk semua dependencies
   ```php
   // Controllers dapat mengakses dependencies dengan mudah
   class AdminController extends Controller
   {
       public function __construct(Application $app)
       {
           parent::__construct();
           $this->app = $app;
           $this->database = $app->getDatabase();
       }
   }
   ```

3. **Middleware Support**: Mudah implementasi middleware untuk:
   - Authentication
   - Rate limiting
   - CSRF protection
   - Request/Response transformation

4. **Better Error Handling**: Centralized exception handling dengan logging

5. **Testability**: Mudah untuk unit testing dengan dependency injection

6. **Extensibility**: Mudah menambah services baru (caching, logging, queue, etc.)

### 📋 Migration Plan

**Phase 1: Create Application Class**
1. Buat `app/core/Application.php` dengan implementasi di atas
2. Update `index.php` untuk menggunakan Application class
3. Testing untuk memastikan tidak ada breaking changes

**Phase 2: Enhance Router**
1. Update Router untuk fokus hanya pada routing logic
2. Remove application lifecycle code dari Router
3. Add middleware support di Router

**Phase 3: Dependency Injection**
1. Update Controllers untuk receive Application instance
2. Update Models untuk receive Database instance via DI
3. Remove static calls dan global dependencies

**Phase 4: Advanced Features**
1. Implement middleware system
2. Add service container
3. Add configuration caching
4. Add route caching

### 🔄 Backward Compatibility

Implementasi ini dapat dilakukan secara bertahap dengan mempertahankan backward compatibility:

```php
// index.php - Transition approach
if (class_exists('App\Core\Application')) {
    // New application-centric approach
    $app = new App\Core\Application();
    $app->initialize();
    $app->boot();
    $app->run();
} else {
    // Fallback to current router-centric approach
    // ... current implementation
}
```

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

### Version 2.2.0 - **Instance-Based Core Access Pattern**
- ✅ **BREAKING CHANGE**: Migrated from static method calls to instance-based access for Config, Session, and Request
- ✅ **NEW**: Instance properties in Controllers and Models (`$this->config`, `$this->session`, `$this->request`)
- ✅ **NEW**: Backward compatibility maintained with static methods
- ✅ **NEW**: Improved dependency injection and testability
- ✅ **NEW**: Enhanced method naming for better clarity
- ✅ **IMPROVED**: Consistent access patterns across all MVC components
- ✅ **IMPROVED**: Better separation of concerns and cleaner code architecture
- ✅ **IMPROVED**: Views now receive config, session, and request instances automatically
- ✅ **UPDATED**: All controllers, models, and views migrated to new pattern

### Version 2.1.0 - **Multi-Occupancy Support**
- ✅ **NEW**: Multi-occupancy support (up to 2 tenants per room)
- ✅ **NEW**: Enhanced room capacity management
- ✅ **NEW**: Individual tenant tracking within shared rooms
- ✅ **NEW**: Aggregated billing for multi-tenant rooms

### Version 2.0.0 - **PSR-4 Architecture**
- ✅ **NEW**: PSR-4 namespace implementation
- ✅ **NEW**: Enhanced autoloader with namespace support
- ✅ **NEW**: Comprehensive Tagihan dan Pembayaran views
- ✅ **NEW**: Modal-based interfaces untuk billing dan payment
- ✅ **NEW**: Advanced filtering dan status tracking
- ✅ **IMPROVED**: Better code organization and separation of concerns
- ✅ **IMPROVED**: Enhanced documentation and code comments
- ✅ **IMPROVED**: Better error handling and debugging support
- ✅ **IMPROVED**: Streamlined workflow untuk billing dan payment operations

### Version 1.0.0 - **Initial Release**
- Initial release with basic MVC structure

## License

Aplikasi ini dilisensikan di bawah [MIT License](LICENSE).

## Support & Documentation

- **Technical Documentation**: README.md (this file)
- **Issues**: GitHub Issues
- **Wiki**: Comprehensive guides and examples

## Database Migration Summary

### tb_tagihan Table Changes

#### Changes Made

**Database Schema Changes**
- **Column `bulan`**: Changed from `VARCHAR` to `INT` (values 1-12)
- **Column `tahun`**: New `INT` column added
- **Unique Constraint**: Updated to `(bulan, tahun, id_kmr_penghuni)`

#### Codebase Updates

**1. Models Updated**

**`app/models/TagihanModel.php`**
- **Method `findByBulan()`** → **`findByBulanTahun($bulan, $tahun)`**
- **Method `findByBulanKamarPenghuni()`** → **`findByBulanTahunKamarPenghuni($bulan, $tahun, $id_kmr_penghuni)`**
- **Method `generateTagihan($periode)`**: Now parses 'YYYY-MM' format and extracts separate bulan/tahun integers
- **Method `getTagihanDetail($periode)`**: Updated to filter by both bulan and tahun
- **Method `getTagihanTerlambat()`**: Updated to use proper date comparison with separate bulan/tahun fields

**`app/models/BayarModel.php`**
- **Method `getLaporanPembayaran($periode)`**: Updated to filter by both bulan and tahun
- Added `t.tahun` to SELECT clause for proper date display

**2. Controllers Updated**

**`app/controllers/Admin.php`**
- **Method `tagihan()`**: Updated to pass both bulan and tahun to model methods
- **Method `pembayaran()`**: Updated date handling for new schema

**3. Views Updated**

**`app/views/admin/tagihan.php`**
- Updated periode display to show 'Month YYYY' format
- JavaScript updated to handle separate bulan/tahun values

**`app/views/admin/pembayaran.php`**  
- Updated to display periode in proper 'Month YYYY' format
- Form handling updated for new date structure

#### Migration Notes

**Data Migration Strategy:**
```sql
-- If you have existing data, migrate it first:
UPDATE tb_tagihan 
SET bulan = MONTH(STR_TO_DATE(bulan, '%Y-%m')),
    tahun = YEAR(STR_TO_DATE(bulan, '%Y-%m'))
WHERE bulan REGEXP '^[0-9]{4}-[0-9]{2}$';

-- Then change column type:
ALTER TABLE tb_tagihan 
MODIFY COLUMN bulan INT NOT NULL,
ADD COLUMN tahun INT NOT NULL AFTER bulan;
```

**Testing Checklist:**
- ✅ Generate tagihan works with new date format
- ✅ Filter tagihan by month/year works correctly  
- ✅ Payment recording works with new schema
- ✅ Reports display proper dates (Month YYYY format)
- ✅ No duplicate tagihan for same month/year/room

**Database Performance:**
- Added composite index on `(bulan, tahun, id_kmr_penghuni)` for faster queries
- Separate integer columns improve query performance vs VARCHAR date parsing

#### Compatibility

**Backward Compatibility:** 
- ❌ **BREAKING CHANGE**: Old date format ('YYYY-MM') no longer supported
- ✅ **Migration Required**: Existing installations need to run migration script
- ✅ **API Changes**: Model method signatures updated (documented above)
