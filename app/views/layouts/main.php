<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
        }
        .content {
            min-height: calc(100vh - 56px);
        }
        .status-badge {
            font-size: 0.75rem;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="bi bi-house-fill"></i>
                <?= \App\Core\Config::get('app.name') ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $config->appConfig('url') ?>">
                            <i class="bi bi-house"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= $config->appConfig('url') ?>/admin"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $config->appConfig('url') ?>/admin/penghuni"><i class="bi bi-people"></i> Kelola Penghuni</a></li>
<li><a class="dropdown-item" href="<?= $config->appConfig('url') ?>/admin/kamar"><i class="bi bi-door-open"></i> Kelola Kamar</a></li>
<li><a class="dropdown-item" href="<?= $config->appConfig('url') ?>/admin/barang"><i class="bi bi-box"></i> Kelola Barang</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="<?= $config->appConfig('url') ?>/admin/tagihan"><i class="bi bi-receipt"></i> Kelola Tagihan</a></li>
<li><a class="dropdown-item" href="<?= $config->appConfig('url') ?>/admin/pembayaran"><i class="bi bi-credit-card"></i> Pembayaran</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nama']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text"><small>Login sebagai: <?= htmlspecialchars($user['username']) ?></small></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= $config->appConfig('url') ?>/logout">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $config->appConfig('url') ?>/login">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <?php if (isset($showSidebar) && $showSidebar): ?>
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <h6 class="text-muted mb-3">MENU ADMIN</h6>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mb-1">
                        <a href="<?= $config->appConfig('url') ?>/admin" class="nav-link <?= $request->requestUri() == '/admin' ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= $config->appConfig('url') ?>/admin/penghuni" class="nav-link <?= $request->requestUri() == '/admin/penghuni' ? 'active' : '' ?>">
                            <i class="bi bi-people"></i> Kelola Penghuni
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= $config->appConfig('url') ?>/admin/kamar" class="nav-link <?= $request->requestUri() == '/admin/kamar' ? 'active' : '' ?>">
                            <i class="bi bi-door-open"></i> Kelola Kamar
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= $config->appConfig('url') ?>/admin/barang" class="nav-link <?= $request->requestUri() == '/admin/barang' ? 'active' : '' ?>">
                            <i class="bi bi-box"></i> Kelola Barang
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= $config->appConfig('url') ?>/admin/tagihan" class="nav-link <?= $request->requestUri() == '/admin/tagihan' ? 'active' : '' ?>">
                            <i class="bi bi-receipt"></i> Kelola Tagihan
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="<?= $config->appConfig('url') ?>/admin/pembayaran" class="nav-link <?= $request->requestUri() == '/admin/pembayaran' ? 'active' : '' ?>">
                            <i class="bi bi-credit-card"></i> Pembayaran
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Content -->
            <div class="col-md-9 col-lg-10 content p-4">
                <?php echo $content; ?>
            </div>
            <?php else: ?>
            <!-- Full Width Content -->
            <div class="col-12 content p-4">
                <?php echo $content; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto hide alerts after 5 seconds
        //setTimeout(function() {
        //    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        //    alerts.forEach(function(alert) {
        //        const bsAlert = new bootstrap.Alert(alert);
        //        bsAlert.close();
        //    });
        //}, 5000);

        // Format currency inputs
        function formatCurrency(input) {
            let value = input.value.replace(/[^\d]/g, '');
            input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Format date inputs
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID');
        }
    </script>
</body>
</html>