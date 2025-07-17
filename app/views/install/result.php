<?php 
ob_start(); 
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header <?= $success ? 'bg-success' : 'bg-danger' ?> text-white">
                    <h4 class="mb-0">
                        <i class="bi <?= $success ? 'bi-check-circle' : 'bi-x-circle' ?>"></i>
                        Hasil Instalasi
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <strong>Berhasil!</strong> <?= htmlspecialchars($message) ?>
                        </div>

                        <h5>Instalasi Selesai</h5>
                        <p>Database dan tabel-tabel telah berhasil dibuat. Aplikasi siap digunakan.</p>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Data Contoh:</strong> Beberapa data contoh telah ditambahkan untuk memudahkan testing:
                            <ul class="mt-2 mb-0">
                                <li>5 kamar dengan nomor 101-102-103-201-202</li>
                                <li>5 jenis barang (Kulkas, AC, TV, Mesin Cuci, Kompor Gas)</li>
                                <li>2 penghuni contoh (Ahmad Santoso, Siti Aminah)</li>
                            </ul>
                        </div>

                        <h5>Langkah Selanjutnya:</h5>
                        <ol>
                            <li>Mulai menggunakan aplikasi dengan mengakses halaman admin</li>
                            <li>Tambahkan data kamar dan barang sesuai kebutuhan</li>
                            <li>Daftarkan penghuni baru</li>
                            <li>Generate tagihan bulanan</li>
                            <li>Kelola pembayaran</li>
                        </ol>

                        <div class="d-grid gap-2 mt-4">
                            <a href="<?= \App\Core\Config::app('url') ?>/admin" class="btn btn-primary btn-lg">
                                <i class="bi bi-speedometer2"></i>
                                Masuk ke Dashboard Admin
                            </a>
                            <a href="<?= \App\Core\Config::app('url') ?>" class="btn btn-success">
                                <i class="bi bi-house"></i>
                                Ke Halaman Utama
                            </a>
                        </div>

                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle-fill"></i>
                            <strong>Gagal!</strong> <?= htmlspecialchars($message) ?>
                        </div>

                        <h5>Instalasi Gagal</h5>
                        <p>Terjadi kesalahan saat melakukan instalasi. Silakan periksa konfigurasi database dan coba lagi.</p>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Solusi yang dapat dicoba:</strong>
                            <ul class="mt-2 mb-0">
                                <li>Pastikan MySQL/MariaDB sudah berjalan</li>
                                <li>Periksa kredensial database di <code>config/config.php</code></li>
                                <li>Pastikan user database memiliki privilege untuk membuat database</li>
                                <li>Periksa log error web server untuk detail lebih lanjut</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <a href="<?= \App\Core\Config::app('url') ?>/install" class="btn btn-warning btn-lg">
                                <i class="bi bi-arrow-repeat"></i>
                                Coba Instalasi Lagi
                            </a>
                            <a href="<?= \App\Core\Config::app('url') ?>" class="btn btn-secondary">
                                <i class="bi bi-house"></i>
                                Kembali ke Beranda
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>