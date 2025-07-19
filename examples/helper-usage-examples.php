<?php

/**
 * Helper Usage Examples
 * Demonstrates different ways to use the new configurable helper system
 */

// ===========================================
// 1. CONFIGURATION EXAMPLES
// ===========================================

// config/config.php
$configExamples = [
    
    // Minimal configuration - only load specific helpers
    'helpers_minimal' => [
        'autoload' => ['HtmlHelper'],
        'load_all' => false,
        'load_functions' => false
    ],
    
    // Full configuration - load everything
    'helpers_full' => [
        'autoload' => ['HtmlHelper', 'ViewHelper'],
        'load_all' => false,
        'load_functions' => true,
        'aliases' => [
            'Html' => 'App\\Helpers\\HtmlHelper',
            'View' => 'App\\Helpers\\ViewHelper'
        ],
        'conditional' => [
            'admin' => ['ViewHelper'],
            'api' => [],
            'public' => ['HtmlHelper']
        ]
    ],
    
    // Performance optimized - conditional loading
    'helpers_optimized' => [
        'autoload' => ['HtmlHelper'], // Always load basic HTML helper
        'load_functions' => true,
        'conditional' => [
            'admin' => ['ViewHelper'], // Only load ViewHelper for admin routes
            'api' => [], // No helpers for API
        ]
    ]
];

// ===========================================
// 2. USAGE IN VIEWS - DIFFERENT APPROACHES
// ===========================================

?>

<!-- OLD WAY (manual HTML) -->
<td>Rp <?= number_format($harga, 0, ',', '.') ?></td>
<td>
    <?php if ($status == 'kosong'): ?>
        <span class="badge bg-success">Kosong</span>
    <?php else: ?>
        <span class="badge bg-info">Terisi</span>
    <?php endif; ?>
</td>

<!-- NEW WAY 1: Full namespace (always works) -->
<td><?= \App\Helpers\HtmlHelper::currency($harga) ?></td>
<td><?= \App\Helpers\HtmlHelper::statusBadge($status) ?></td>

<!-- NEW WAY 2: Global functions (if load_functions = true) -->
<td><?= currency($harga) ?></td>
<td><?= status_badge($status) ?></td>

<!-- NEW WAY 3: Helper function with method call -->
<td><?= html('currency', $harga) ?></td>
<td><?= html('statusBadge', $status) ?></td>

<!-- NEW WAY 4: Mixed approach for complex cases -->
<td><?= currency($harga) ?></td>
<td><?= view_helper('roomStatusBadge', $status) ?></td>

<?php

// ===========================================
// 3. USAGE IN CONTROLLERS
// ===========================================

class ExampleController extends Controller
{
    public function index()
    {
        // Check if specific helper is loaded
        if ($this->isHelperLoaded('ViewHelper')) {
            // Helper is available
        }
        
        // Load additional helpers on demand
        $this->loadSpecificHelpers(['CustomHelper']);
        
        // Use helper in controller (if needed)
        $formattedPrice = \App\Helpers\HtmlHelper::currency(150000);
        
        $data = [
            'price' => $formattedPrice,
            'rooms' => $this->getRooms()
        ];
        
        $this->loadView('example/index', $data);
    }
    
    public function adminPanel()
    {
        // For admin routes, ViewHelper is automatically loaded
        // due to conditional loading configuration
        
        $data = [
            'rooms' => $this->getRoomsWithHelper()
        ];
        
        $this->loadView('admin/panel', $data);
    }
    
    private function getRoomsWithHelper()
    {
        $rooms = $this->loadModel('KamarModel')->findAll();
        
        // Process data with helpers if needed
        foreach ($rooms as &$room) {
            $room['formatted_price'] = \App\Helpers\HtmlHelper::currency($room['harga']);
            $room['status_badge'] = \App\Helpers\ViewHelper::roomStatusBadge($room['status']);
        }
        
        return $rooms;
    }
}

// ===========================================
// 4. ADVANCED USAGE EXAMPLES
// ===========================================

// Load helper on demand in any file
load_helper('CustomHelper');

// Check if helper is loaded
if (is_helper_loaded('ViewHelper')) {
    echo view_helper('roomStatusBadge', 'kosong');
}

// Get helper manager instance for advanced operations
$helperManager = helper_manager();
$loadedHelpers = $helperManager->getLoadedHelpers();
$aliases = $helperManager->getAliases();

// ===========================================
// 5. PERFORMANCE COMPARISON
// ===========================================

/*
OLD SYSTEM:
- Load ALL helpers on every request
- Manual require_once for each file
- No conditional loading
- Memory usage: Higher
- Performance: Lower (loads unused helpers)

NEW SYSTEM:
- Load only needed helpers
- Automatic class loading via autoloader
- Conditional loading based on routes
- Memory usage: Lower
- Performance: Higher (lazy loading)

EXAMPLE SCENARIOS:

1. API Endpoint (/api/data):
   - Old: Loads HtmlHelper + ViewHelper (unnecessary)
   - New: Loads nothing (conditional config: 'api' => [])
   
2. Admin Panel (/admin/dashboard):
   - Old: Loads all helpers
   - New: Loads only ViewHelper (conditional config)
   
3. Public Pages (/):
   - Old: Loads all helpers
   - New: Loads only HtmlHelper (conditional config)
*/

// ===========================================
// 6. REAL WORLD VIEW EXAMPLES
// ===========================================

?>

<!-- Room Management Table -->
<table class="table">
    <thead>
        <tr>
            <th>Nomor</th>
            <th>Harga</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rooms as $room): ?>
        <tr>
            <td><?= htmlspecialchars($room['nomor']) ?></td>
            <td><?= currency($room['harga']) ?></td>
            <td><?= room_status_badge($room['status']) ?></td>
            <td>
                <?php
                // Complex action buttons using ViewHelper
                echo view_helper('roomActionButtons', $room);
                ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Payment Summary -->
<div class="card">
    <div class="card-body">
        <h5>Total Pembayaran: <?= currency($totalPayment) ?></h5>
        <p>Status: <?= payment_status_badge($paymentStatus) ?></p>
        <small>Tanggal: <?= format_date($paymentDate) ?></small>
    </div>
</div>

<?php

// ===========================================
// 7. DEBUGGING AND MONITORING
// ===========================================

// In development, you can check what helpers are loaded
if ($config->get('debug')) {
    echo '<pre>';
    echo 'Loaded Helpers: ' . implode(', ', helper_manager()->getLoadedHelpers()) . "\n";
    echo 'Aliases: ' . print_r(helper_manager()->getAliases(), true);
    echo '</pre>';
}

// Error handling for missing helpers
try {
    echo view_helper('nonExistentMethod', $data);
} catch (BadMethodCallException $e) {
    // Log error or provide fallback
    error_log('Helper method not found: ' . $e->getMessage());
    echo 'Data not available';
}

?>