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
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0"><i class="bi bi-door-open text-primary" style="font-size: 2rem;"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mt-2 text-primary"><?= $stats['total_kamar'] ?></h4>
                        <small>Total Kamar</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0"><i class="bi bi-door-closed text-success" style="font-size: 2rem;"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mt-2 text-success"><?= $stats['kamar_terisi'] ?></h4>
                        <small>Kamar Terisi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0"><i class="bi bi-door-open text-info" style="font-size: 2rem;"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mt-2 text-info"><?= $stats['kamar_kosong'] ?></h4>
                        <small>Kamar Kosong</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0"><i class="bi bi-people text-secondary" style="font-size: 2rem;"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mt-2 text-secondary"><?= $stats['total_penghuni'] ?></h4>
                        <small>Total Penghuni</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0"><i class="bi bi-clock text-warning" style="font-size: 2rem;"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mt-2 text-warning"><?= $stats['mendekati_jatuh_tempo'] ?></h4>
                        <small>Jatuh Tempo</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0"><i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i></div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mt-2 text-danger"><?= $stats['tagihan_terlambat'] ?></h4>
                        <small>Terlambat</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Building Statistics -->
<?php if (!empty($statistikPerGedung)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-building"></i>
                    Statistik Per Gedung
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($statistikPerGedung as $stat): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-info h-100">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-building"></i>
                                        Gedung <?= $stat['gedung'] ?>
                                    </h6>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <h5 class="text-primary"><?= $stat['total_kamar'] ?></h5>
                                            <small class="text-muted">Total</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-success"><?= $stat['kamar_terisi'] ?></h5>
                                            <small class="text-muted">Terisi</small>
                                        </div>
                                        <div class="col-4">
                                            <h5 class="text-info"><?= $stat['kamar_kosong'] ?></h5>
                                            <small class="text-muted">Kosong</small>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <small class="text-muted">
                                            <strong>Rentang Harga:</strong><br>
                                            Rp <?= number_format($stat['harga_terendah'], 0, ',', '.') ?>
                                            <?php if ($stat['harga_terendah'] != $stat['harga_tertinggi']): ?>
                                                - Rp <?= number_format($stat['harga_tertinggi'], 0, ',', '.') ?>
                                            <?php endif; ?>
                                            <br>
                                            <small>(Rata-rata: Rp <?= number_format($stat['harga_rata_rata'], 0, ',', '.') ?>)</small>
                                        </small>
                                    </div>
                                    <div class="mt-2">
                                        <div class="progress" style="height: 10px;">
                                            <?php 
                                            $occupancyRate = $stat['total_kamar'] > 0 ? ($stat['kamar_terisi'] / $stat['total_kamar']) * 100 : 0;
                                            $progressColor = $occupancyRate > 80 ? 'bg-success' : ($occupancyRate > 50 ? 'bg-warning' : 'bg-danger');
                                            ?>
                                            <div class="progress-bar <?= $progressColor ?>" role="progressbar" 
                                                 style="width: <?= $occupancyRate ?>%" 
                                                 aria-valuenow="<?= $occupancyRate ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?= round($occupancyRate, 1) ?>%
                                            </div>
                                        </div>
                                        <small class="text-muted">Tingkat Hunian</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning"></i>
                    Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="<?= $baseUrl ?>/admin/penghuni" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person-plus"></i><br>
                            Tambah Penghuni
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= $baseUrl ?>/admin/kamar" class="btn btn-outline-success w-100">
                            <i class="bi bi-door-open"></i><br>
                            Kelola Kamar
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <form method="POST" action="<?= $baseUrl ?>/admin/tagihan" class="w-100">
                            <input type="hidden" name="action" value="generate">
                            <input type="hidden" name="bulan" value="<?= date('Y-m') ?>">
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i class="bi bi-receipt"></i><br>
                                Generate Tagihan
                            </button>
                        </form>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= $baseUrl ?>/admin/pembayaran" class="btn btn-outline-info w-100">
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
                            <a href="<?= $baseUrl ?>/admin/kamar" class="btn btn-sm btn-outline-success">
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
                                    <th>Barang Bawaan</th>
                                    <th>Sisa Hari</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($kamarMendekatiJatuhTempo, 0, 5) as $item): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($item['nomor_kamar']) ?></strong></td>
                                        <td><?= htmlspecialchars($item['nama_penghuni']) ?></td>
                                        <td>
                                            <?php if (!empty($item['barang_bawaan'])): ?>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($item['barang_bawaan'] as $barang): ?>
                                                        <span class="badge bg-warning text-dark" style="font-size: 0.7rem;" title="<?= htmlspecialchars($barang['nama_barang']) ?> (+Rp <?= number_format($barang['harga_barang'], 0, ',', '.') ?>)">
                                                            <?= htmlspecialchars($barang['nama_barang']) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
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
                            <a href="<?= $baseUrl ?>/admin/tagihan" class="btn btn-sm btn-outline-warning">
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
                                <th>Barang Bawaan</th>
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
                                        <?php if (!empty($tagihan['barang_bawaan'])): ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach ($tagihan['barang_bawaan'] as $barang): ?>
                                                    <span class="badge bg-warning text-dark" style="font-size: 0.7rem;" title="<?= htmlspecialchars($barang['nama_barang']) ?> (+Rp <?= number_format($barang['harga_barang'], 0, ',', '.') ?>)">
                                                        <?= htmlspecialchars($barang['nama_barang']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= date('M Y', mktime(0, 0, 0, $tagihan['bulan'], 1, $tagihan['tahun'])) ?>
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
                                        <a href="<?= $baseUrl ?>/admin/pembayaran?tagihan=<?= $tagihan['id'] ?>" class="btn btn-sm btn-primary">
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
                        <a href="<?= $baseUrl ?>/admin/pembayaran" class="btn btn-outline-danger">
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