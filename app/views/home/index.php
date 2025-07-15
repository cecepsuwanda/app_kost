<?php 
ob_start(); 
?>

<div class="container">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="jumbotron bg-primary text-white p-5 rounded">
                <h1 class="display-4">Selamat Datang di <?= APP_NAME ?></h1>
                <p class="lead">Sistem manajemen kos yang memudahkan pengelolaan penghuni, kamar, tagihan, dan pembayaran.</p>
                <hr class="my-4 border-white">
                <p>Kelola kos Anda dengan lebih efisien dan terorganisir.</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <i class="bi bi-door-open text-success" style="font-size: 2rem;"></i>
                    <h5 class="card-title text-success mt-2">Kamar Tersedia</h5>
                    <h2 class="text-success"><?= count($kamarKosong) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                    <h5 class="card-title text-warning mt-2">Segera Jatuh Tempo</h5>
                    <h2 class="text-warning"><?= count($kamarMendekatiJatuhTempo) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                    <h5 class="card-title text-danger mt-2">Terlambat Bayar</h5>
                    <h2 class="text-danger"><?= count($tagihanTerlambat) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Rooms Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-door-open"></i>
                        Kamar Tersedia
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($kamarKosong)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Tidak ada kamar yang tersedia saat ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($kamarKosong as $kamar): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-door-closed"></i>
                                                Kamar <?= htmlspecialchars($kamar['nomor']) ?>
                                            </h6>
                                            <p class="card-text">
                                                <strong>Harga:</strong> Rp <?= number_format($kamar['harga'], 0, ',', '.') ?>/bulan
                                            </p>
                                            <span class="badge bg-success">Tersedia</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Approaching Due Date Section -->
    <?php if (!empty($kamarMendekatiJatuhTempo)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-clock"></i>
                        Kamar Mendekati Jatuh Tempo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kamar</th>
                                    <th>Penghuni</th>
                                    <th>Hari Tersisa</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kamarMendekatiJatuhTempo as $kamar): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-door-closed"></i>
                                            <?= htmlspecialchars($kamar['nomor_kamar']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($kamar['nama_penghuni']) ?></td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <?= $kamar['hari_tersisa'] ?> hari
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">Aktif</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Overdue Payments Section -->
    <?php if (!empty($tagihanTerlambat)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Tagihan Terlambat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kamar</th>
                                    <th>Penghuni</th>
                                    <th>Bulan</th>
                                    <th>Tagihan</th>
                                    <th>Dibayar</th>
                                    <th>Sisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tagihanTerlambat as $tagihan): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-door-closed"></i>
                                            <?= htmlspecialchars($tagihan['nomor_kamar']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($tagihan['nama_penghuni']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= date('M Y', strtotime($tagihan['bulan'] . '-01')) ?>
                                            </span>
                                        </td>
                                        <td>Rp <?= number_format($tagihan['jml_tagihan'], 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($tagihan['jml_dibayar'], 0, ',', '.') ?></td>
                                        <td>
                                            <span class="badge bg-danger">
                                                Rp <?= number_format($tagihan['jml_tagihan'] - $tagihan['jml_dibayar'], 0, ',', '.') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning"></i>
                        Aksi Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="<?= APP_URL ?>/admin" class="btn btn-outline-primary w-100">
                                <i class="bi bi-speedometer2"></i><br>
                                Dashboard Admin
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= APP_URL ?>/admin/penghuni" class="btn btn-outline-success w-100">
                                <i class="bi bi-people"></i><br>
                                Kelola Penghuni
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= APP_URL ?>/admin/tagihan" class="btn btn-outline-warning w-100">
                                <i class="bi bi-receipt"></i><br>
                                Kelola Tagihan
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= APP_URL ?>/admin/pembayaran" class="btn btn-outline-info w-100">
                                <i class="bi bi-credit-card"></i><br>
                                Pembayaran
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>