<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Error | <?= $appName ?? 'Sistem Manajemen Kos' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-code {
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            color: #495057;
            margin-bottom: 2rem;
        }
        .error-description {
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        .btn-group-custom {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: transform 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .btn-secondary-custom {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        }
        .diagnostic-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .diagnostic-item:last-child {
            border-bottom: none;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-success { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-unknown { background: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-database-exclamation"></i>
        </div>
        <div class="error-code">Database Error</div>
        <div class="error-message">Database Connection Failed</div>
        <div class="error-description">
            Terjadi masalah saat menghubungkan ke database. Hal ini dapat disebabkan oleh berbagai faktor 
            seperti konfigurasi yang salah, server database yang tidak aktif, atau masalah jaringan.
        </div>

        <!-- Database Diagnostics -->
        <div class="error-details">
            <h6 class="mb-3"><i class="bi bi-gear"></i> Diagnosis Cepat Database</h6>
            
            <div class="diagnostic-item">
                <span>Status Koneksi Database</span>
                <span class="status-badge status-error">
                    <i class="bi bi-x-circle"></i> Error
                </span>
            </div>
            
            <div class="diagnostic-item">
                <span>Host Database</span>
                <span><?= htmlspecialchars($dbHost ?? 'localhost') ?></span>
            </div>
            
            <div class="diagnostic-item">
                <span>Nama Database</span>
                <span><?= htmlspecialchars($dbName ?? 'db_kost') ?></span>
            </div>
            
            <div class="diagnostic-item">
                <span>User Database</span>
                <span><?= htmlspecialchars($dbUser ?? 'tidak diketahui') ?></span>
            </div>
            
            <?php if (isset($errorMessage)): ?>
            <div class="diagnostic-item">
                <span>Pesan Error</span>
                <span class="text-danger"><?= htmlspecialchars($errorMessage) ?></span>
            </div>
            <?php endif; ?>
            
            <div class="diagnostic-item">
                <span>File Log Error</span>
                <span class="status-badge <?= $logFileExists ? 'status-success' : 'status-error' ?>">
                    <?= $logFileExists ? '✓ Tersedia' : '✗ Tidak Ada' ?>
                </span>
            </div>
        </div>

        <!-- Troubleshooting Steps -->
        <div class="error-details">
            <h6 class="mb-3"><i class="bi bi-tools"></i> Langkah Pemecahan Masalah</h6>
            <div class="text-start">
                <ol class="mb-0">
                    <li>Periksa konfigurasi database di file <code>config/config.php</code></li>
                    <li>Pastikan server MySQL/MariaDB sedang berjalan</li>
                    <li>Verifikasi kredensial database (username dan password)</li>
                    <li>Periksa apakah database sudah dibuat</li>
                    <li>Pastikan user database memiliki privilege yang cukup</li>
                    <li>Periksa koneksi jaringan ke server database</li>
                </ol>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="btn-group-custom">
            <a href="<?= $baseUrl ?? '/' ?>" class="btn-custom">
                <i class="bi bi-house-door"></i>
                Kembali ke Beranda
            </a>
            
            <a href="<?= $baseUrl ?? '/' ?>/install" class="btn-custom btn-secondary-custom">
                <i class="bi bi-tools"></i>
                Install Database
            </a>
            
            <?php if ($logFileExists): ?>
            <button onclick="showErrorLog()" class="btn-custom btn-secondary-custom">
                <i class="bi bi-file-text"></i>
                Lihat Log Error
            </button>
            <?php endif; ?>
            
            <button onclick="testConnection()" class="btn-custom">
                <i class="bi bi-arrow-clockwise"></i>
                Test Koneksi
            </button>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <i class="bi bi-clock"></i>
                Jika masalah berlanjut, silakan hubungi administrator sistem.
                <br>
                Waktu Error: <?= date('d M Y H:i:s') ?>
            </small>
        </div>
    </div>

    <!-- Error Log Modal -->
    <div class="modal fade" id="errorLogModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-text"></i>
                        Error Log
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="errorLogContent" style="max-height: 400px; overflow-y: auto;">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testConnection() {
            // Reload page to test connection again
            location.reload();
        }

        function showErrorLog() {
            const modal = new bootstrap.Modal(document.getElementById('errorLogModal'));
            modal.show();
            
            // Load error log content
            fetch('<?= $baseUrl ?? '/' ?>/database-diagnostic/logs')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('errorLogContent').innerHTML = 
                        '<pre style="white-space: pre-wrap; font-size: 0.8rem;">' + data + '</pre>';
                })
                .catch(error => {
                    document.getElementById('errorLogContent').innerHTML = 
                        '<div class="alert alert-danger">Gagal memuat log error: ' + error.message + '</div>';
                });
        }

        // Auto-check connection every 10 seconds
        setInterval(function() {
            // Simple check by making a request to a minimal endpoint
            fetch('<?= $baseUrl ?? '/' ?>/ping', {method: 'HEAD'})
                .then(response => {
                    if (response.ok) {
                        // Connection restored, redirect to main page
                        window.location.href = '<?= $baseUrl ?? '/' ?>';
                    }
                })
                .catch(error => {
                    // Still have connection issues, continue checking
                });
        }, 10000);
    </script>
</body>
</html>