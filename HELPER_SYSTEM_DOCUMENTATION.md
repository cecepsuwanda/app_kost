# 🛠️ Sistem Helper Terkonfigurasi

## 📖 Overview

Implementasi sistem helper yang fleksibel dan performant dengan konfigurasi melalui config file. Sistem ini memungkinkan:

- ✅ **Conditional Loading** - Load helper berdasarkan route/controller
- ✅ **Performance Optimization** - Hanya load yang diperlukan
- ✅ **Multiple Access Methods** - Berbagai cara akses helper
- ✅ **Global Functions** - Function shortcuts untuk kemudahan
- ✅ **Debugging Support** - Monitor helper yang ter-load

## 🏗️ Arsitektur

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Config File   │───▶│  HelperManager   │───▶│     Views       │
│  config.php     │    │                  │    │                 │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌──────────────────┐
                       │   Controllers    │
                       │                  │
                       └──────────────────┘
```

## ⚙️ Konfigurasi

### 📄 config/config.php

```php
'helpers' => [
    // Auto-load specific helpers (recommended for performance)
    'autoload' => [
        'HtmlHelper',
        'ViewHelper'
    ],
    
    // Load all helpers in directory (set to true for convenience)
    'load_all' => false,
    
    // Helpers directory path (relative to APP_PATH)
    'path' => '/helpers/',
    
    // Load global helper functions for easier access
    'load_functions' => true,
    
    // Global aliases for easier access in views
    'aliases' => [
        'Html' => 'App\\Helpers\\HtmlHelper',
        'View' => 'App\\Helpers\\ViewHelper'
    ],
    
    // Conditional loading based on routes/controllers
    'conditional' => [
        'admin' => ['ViewHelper'], // Load only for admin routes
        'api' => [], // No helpers for API routes
    ]
]
```

## 🎯 Opsi Konfigurasi

| Setting | Type | Description | Default |
|---------|------|-------------|---------|
| `autoload` | array | Helper yang selalu di-load | `[]` |
| `load_all` | bool | Load semua helper di directory | `false` |
| `path` | string | Path ke folder helpers | `/helpers/` |
| `load_functions` | bool | Load global functions | `false` |
| `aliases` | array | Alias untuk class helper | `[]` |
| `conditional` | array | Loading berdasarkan route | `[]` |

## 🔄 Flow Loading Helper

```
1. Application starts
   ↓
2. Router determines current route
   ↓  
3. HelperManager.loadHelpersForRoute()
   ↓
4. Check conditional config for route context
   ↓
5. Load specific helpers OR autoload helpers
   ↓
6. Load global functions (if enabled)
   ↓
7. Setup aliases
   ↓
8. Helpers ready for use in Controller/Views
```

## 💡 Cara Penggunaan

### 1️⃣ **Full Namespace (Selalu Work)**

```php
<!-- Di View -->
<td><?= \App\Helpers\HtmlHelper::currency($harga) ?></td>
<td><?= \App\Helpers\ViewHelper::roomStatusBadge($status) ?></td>

// Di Controller
$price = \App\Helpers\HtmlHelper::currency(150000);
```

### 2️⃣ **Global Functions (Jika load_functions = true)**

```php
<!-- Di View -->
<td><?= currency($harga) ?></td>
<td><?= status_badge($status) ?></td>
<td><?= room_status_badge($status) ?></td>

// Di Controller
$price = currency(150000);
```

### 3️⃣ **Helper Function dengan Method Call**

```php
<!-- Di View -->
<td><?= html('currency', $harga) ?></td>
<td><?= view_helper('roomStatusBadge', $status) ?></td>

// Di Controller
$price = html('currency', 150000);
```

### 4️⃣ **Load On Demand**

```php
// Di Controller
$this->loadSpecificHelpers(['CustomHelper']);

// Anywhere
load_helper('CustomHelper');

// Check if loaded
if (is_helper_loaded('ViewHelper')) {
    // Use the helper
}
```

## 🎛️ Helper Manager Methods

```php
$helperManager = \App\Core\HelperManager::getInstance();

// Load helpers
$helperManager->loadHelpers();
$helperManager->loadSpecificHelpers(['HtmlHelper']);
$helperManager->loadAllHelpers();

// Check status
$helperManager->isHelperLoaded('ViewHelper'); // bool
$helperManager->getLoadedHelpers(); // array
$helperManager->getAliases(); // array

// Route-based loading
$helperManager->loadHelpersForRoute('/admin/dashboard');
```

## 📁 File Structure

```
app/
├── core/
│   ├── HelperManager.php     ← New helper manager class
│   ├── Controller.php        ← Updated to use HelperManager
│   ├── Router.php           ← Updated to integrate with helpers
│   └── Autoloader.php       ← Updated to include helpers namespace
├── helpers/
│   ├── HtmlHelper.php       ← Existing helper
│   ├── ViewHelper.php       ← Existing helper
│   └── functions.php        ← New global functions
└── ...

config/
└── config.php               ← Updated with helpers config

examples/
└── helper-usage-examples.php ← Usage examples
```

## 🚀 Skenario Penggunaan

### 🎯 **Skenario 1: Performance Optimized**

```php
'helpers' => [
    'autoload' => ['HtmlHelper'], // Basic HTML helper always loaded
    'conditional' => [
        'admin' => ['ViewHelper'], // Complex helpers only for admin
        'api' => [], // No UI helpers for API
    ]
]
```

**Hasil:**
- Route `/` → Load HtmlHelper saja
- Route `/admin/dashboard` → Load HtmlHelper + ViewHelper  
- Route `/api/data` → Load tidak ada helper

### 🎯 **Skenario 2: Development Mode**

```php
'helpers' => [
    'load_all' => true,
    'load_functions' => true,
]
```

**Hasil:**
- Semua helper di-load
- Global functions tersedia
- Easy development experience

### 🎯 **Skenario 3: Production Optimized**

```php
'helpers' => [
    'autoload' => ['HtmlHelper', 'ViewHelper'],
    'load_functions' => true,
    'conditional' => [
        'api' => [], // No helpers for API endpoints
    ]
]
```

## 📊 Perbandingan Performance

| Metric | Old System | New System |
|--------|------------|------------|
| **Memory Usage** | Load all helpers | Load only needed |
| **Load Time** | Fixed overhead | Conditional loading |
| **API Routes** | Load UI helpers | Load nothing |
| **Flexibility** | No configuration | Full configuration |
| **Maintainability** | Manual includes | Automatic management |

## 🔧 Global Functions Available

```php
// Currency formatting
currency($amount) // → \App\Helpers\HtmlHelper::currency()

// Badge generation  
badge($text, $type) // → \App\Helpers\HtmlHelper::badge()
status_badge($status) // → \App\Helpers\HtmlHelper::statusBadge()

// Date formatting
format_date($date, $format) // → \App\Helpers\HtmlHelper::date()

// Boarding house specific
room_status_badge($status) // → \App\Helpers\ViewHelper::roomStatusBadge()
payment_status_badge($status) // → \App\Helpers\ViewHelper::paymentStatusBadge()

// Helper management
load_helper($name) // Load specific helper
is_helper_loaded($name) // Check if loaded
helper_manager() // Get HelperManager instance

// Dynamic helper calls
html($method, ...$args) // Call HtmlHelper methods
view_helper($method, ...$args) // Call ViewHelper methods
```

## 🐛 Debugging

### Check Loaded Helpers

```php
// In development mode
if ($config->get('debug')) {
    $helperManager = helper_manager();
    echo '<pre>';
    echo 'Loaded Helpers: ' . implode(', ', $helperManager->getLoadedHelpers());
    echo "\nAliases: " . print_r($helperManager->getAliases(), true);
    echo '</pre>';
}
```

### Error Handling

```php
try {
    echo view_helper('nonExistentMethod', $data);
} catch (BadMethodCallException $e) {
    error_log('Helper method not found: ' . $e->getMessage());
    echo 'Data not available'; // Fallback
}
```

## ✅ Migration Guide

### From Old System to New System

1. **Update config.php** - Add helpers configuration
2. **No view changes needed** - Existing code still works
3. **Optional optimization** - Use global functions for cleaner code
4. **Test conditional loading** - Configure based on your routes

### Backward Compatibility

✅ **Existing code tetap berjalan** - Full namespace calls masih work  
✅ **No breaking changes** - Semua helper method masih tersedia  
✅ **Gradual migration** - Bisa pindah bertahap ke global functions  

## 🏆 Best Practices

1. **Use conditional loading** untuk performance
2. **Enable global functions** untuk development 
3. **Use full namespace** untuk production critical code
4. **Monitor loaded helpers** dengan debugging
5. **Load on demand** untuk helper yang jarang dipakai

## 🎉 Benefits

- 🚀 **Better Performance** - Load only what you need
- 🎛️ **Full Control** - Configure loading behavior
- 🛠️ **Multiple Access Methods** - Choose what works best
- 📊 **Debugging Support** - Monitor and optimize
- 🔄 **Backward Compatible** - No breaking changes
- 🎯 **Route-Aware** - Smart conditional loading