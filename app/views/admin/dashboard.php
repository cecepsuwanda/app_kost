<?php 
ob_start(); 
$showSidebar = true;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-speedometer2"></i>
        Dashboard Admin
    </h2>
    <div>
        <span class="text-muted">Terakhir diperbarui: <?= date('d M Y H:i') ?></span>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="bi bi-door-open" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $stats['total_kamar'] ?></h4>
                <small>Total Kamar</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="bi bi-door-closed" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $stats['kamar_terisi'] ?></h4>
                <small>Kamar Terisi</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="bi bi-door-open" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $stats['kamar_kosong'] ?></h4>
                <small>Kamar Kosong</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <i class="bi bi-people" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $stats['total_penghuni'] ?></h4>
                <small>Total Penghuni</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <i class="bi bi-clock" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $stats['mendekati_jatuh_tempo'] ?></h4>
                <small>Jatuh Tempo</small>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $stats['tagihan_terlambat'] ?></h4>
                <small>Terlambat</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning"></i>
                    Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="<?= APP_URL ?>/admin/penghuni" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person-plus"></i><br>
                            Tambah Penghuni
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= APP_URL ?>/admin/kamar" class="btn btn-outline-success w-100">
                            <i class="bi bi-door-open"></i><br>
                            Kelola Kamar
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <form method="POST" action="<?= APP_URL ?>/admin/tagihan" class="w-100">
                            <input type="hidden" name="action" value="generate">
                            <input type="hidden" name="bulan" value="<?= date('Y-m') ?>">
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="bi bi-receipt"></i><br>
                                Generate Tagihan
                            </button>
                        </form>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= APP_URL ?>/admin/pembayaran" class="btn btn-outline-info w-100">
                            <i class="bi bi-credit-card"></i><br>
                            Catat Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Available Rooms -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="bi bi-door-open"></i>
                    Kamar Tersedia (<?= count($kamarKosong) ?>)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($kamarKosong)): ?>
                    <div class="text-center py-3">
                        <i class="bi bi-info-circle text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">Semua kamar terisi</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kamar</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($kamarKosong, 0, 5) as $kamar): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($kamar['nomor']) ?></strong></td>
                                        <td>Rp <?= number_format($kamar['harga'], 0, ',', '.') ?></td>
                                        <td><span class="badge bg-success">Kosong</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($kamarKosong) > 5): ?>
                        <div class="text-center">
                            <a href="<?= APP_URL ?>/admin/kamar" class="btn btn-sm btn-outline-success">
                                Lihat Semua (<?= count($kamarKosong) ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Approaching Due Date -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="bi bi-clock"></i>
                    Mendekati Jatuh Tempo (<?= count($kamarMendekatiJatuhTempo) ?>)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($kamarMendekatiJatuhTempo)): ?>
                    <div class="text-center py-3">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">Tidak ada yang jatuh tempo</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kamar</th>
                                    <th>Penghuni</th>
                                    <th>Sisa Hari</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($kamarMendekatiJatuhTempo, 0, 5) as $item): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($item['nomor_kamar']) ?></strong></td>
                                        <td><?= htmlspecialchars($item['nama_penghuni']) ?></td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <?= $item['hari_tersisa'] ?> hari
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($kamarMendekatiJatuhTempo) > 5): ?>
                        <div class="text-center">
                            <a href="<?= APP_URL ?>/admin/tagihan" class="btn btn-sm btn-outline-warning">
                                Lihat Semua (<?= count($kamarMendekatiJatuhTempo) ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Payments -->
<?php if (!empty($tagihanTerlambat)): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    Tagihan Terlambat (<?= count($tagihanTerlambat) ?>)
                </h6>
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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($tagihanTerlambat, 0, 10) as $tagihan): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($tagihan['nomor_kamar']) ?></strong></td>
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
                                    <td>
                                        <a href="<?= APP_URL ?>/admin/pembayaran?tagihan=<?= $tagihan['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-credit-card"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($tagihanTerlambat) > 10): ?>
                    <div class="text-center mt-3">
                        <a href="<?= APP_URL ?>/admin/pembayaran" class="btn btn-outline-danger">
                            Lihat Semua Tagihan Terlambat (<?= count($tagihanTerlambat) ?>)
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>