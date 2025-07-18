<?php 
ob_start(); 
$showSidebar = true;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-receipt"></i>
        Kelola Tagihan
    </h2>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateTagihanModal">
            <i class="bi bi-plus-circle"></i>
            Generate Tagihan
        </button>
    </div>
</div>

<!-- Month Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="bulan" class="form-label">Filter Bulan</label>
                <input type="month" class="form-control" id="bulan" name="bulan" value="<?= $bulan ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                    Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Message Alert -->
<?php if (isset($message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i>
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tagihan List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Tagihan - <?= date('F Y', strtotime($bulan . '-01')) ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($tagihan)): ?>
            <div class="text-center py-5">
                <i class="bi bi-receipt text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Belum ada tagihan untuk bulan ini</h5>
                <p class="text-muted">Klik tombol "Generate Tagihan" untuk membuat tagihan bulanan.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Penghuni</th>
                            <th>Kamar</th>
                            <th>Jumlah Tagihan</th>
                            <th>Jumlah Dibayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tagihan as $t): ?>
                            <?php
                            $sisa = $t['jml_tagihan'] - $t['jml_dibayar'];
                            $statusBadge = [
                                'Lunas' => 'bg-success',
                                'Cicil' => 'bg-warning text-dark',
                                'Belum Bayar' => 'bg-danger'
                            ];
                            ?>
                            <tr>
                                <td><?= date('M Y', mktime(0, 0, 0, $t['bulan'], 1, $t['tahun'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($t['nama_penghuni']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= htmlspecialchars($t['no_hp']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        Kamar <?= htmlspecialchars($t['nomor_kamar']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong>Rp <?= number_format($t['jml_tagihan'], 0, ',', '.') ?></strong>
                                </td>
                                <td>
                                    Rp <?= number_format($t['jml_dibayar'], 0, ',', '.') ?>
                                </td>
                                <td>
                                    <?php if ($sisa > 0): ?>
                                        <span class="text-danger">
                                            Rp <?= number_format($sisa, 0, ',', '.') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-success">Rp 0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge status-badge <?= $statusBadge[$t['status_bayar']] ?>">
                                        <?= $t['status_bayar'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="viewDetail(<?= $t['id'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($t['status_bayar'] !== 'Lunas'): ?>
                                            <a href="<?= $baseUrl ?>/admin/pembayaran?tagihan=<?= $t['id'] ?>" 
                                               class="btn btn-outline-success">
                                                <i class="bi bi-credit-card"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <?php
            $totalTagihan = array_sum(array_column($tagihan, 'jml_tagihan'));
            $totalDibayar = array_sum(array_column($tagihan, 'jml_dibayar'));
            $totalSisa = $totalTagihan - $totalDibayar;
            ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h6>Total Tagihan</h6>
                                    <h4 class="text-primary">Rp <?= number_format($totalTagihan, 0, ',', '.') ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <h6>Total Dibayar</h6>
                                    <h4 class="text-success">Rp <?= number_format($totalDibayar, 0, ',', '.') ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <h6>Total Sisa</h6>
                                    <h4 class="text-danger">Rp <?= number_format($totalSisa, 0, ',', '.') ?></h4>
                                </div>
                                <div class="col-md-3">
                                    <h6>Persentase Terbayar</h6>
                                    <h4 class="text-info">
                                        <?= $totalTagihan > 0 ? round(($totalDibayar / $totalTagihan) * 100, 1) : 0 ?>%
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Generate Tagihan Modal -->
<div class="modal fade" id="generateTagihanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Tagihan Bulanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/admin/tagihan">
                <div class="modal-body">
                    <input type="hidden" name="action" value="generate">
                    <div class="mb-3">
                        <label for="bulan_generate" class="form-label">Bulan Tagihan</label>
                        <input type="month" class="form-control" id="bulan_generate" name="bulan" required 
                               value="<?= date('Y-m') ?>">
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informasi:</strong> Sistem akan generate tagihan untuk semua penghuni aktif berdasarkan:
                        <ul class="mb-0 mt-2">
                            <li>Harga sewa kamar</li>
                            <li>Biaya barang bawaan (jika ada)</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i>
                        Generate Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewDetail(id) {
    // Redirect to detail view or show modal with tagihan details
    window.location.href = '<?= $baseUrl ?>/admin/pembayaran?tagihan=' + id;
}
</script>

<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>