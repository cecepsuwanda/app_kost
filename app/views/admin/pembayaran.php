<?php 
ob_start(); 
$showSidebar = true;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-credit-card"></i>
        Kelola Pembayaran
    </h2>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPembayaranModal">
            <i class="bi bi-plus-circle"></i>
            Catat Pembayaran
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

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Laporan Pembayaran -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Laporan Pembayaran - <?= date('F Y', strtotime($bulan . '-01')) ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($laporan)): ?>
            <div class="text-center py-5">
                <i class="bi bi-credit-card text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Belum ada data pembayaran untuk bulan ini</h5>
                <p class="text-muted">Data akan muncul setelah ada tagihan dan pembayaran.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Penghuni</th>
                            <th>Kamar</th>
                            <th>Total Tagihan</th>
                            <th>Total Dibayar</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan as $l): ?>
                            <?php
                            $sisa = $l['jml_tagihan'] - $l['total_bayar'];
                            $statusBadge = [
                                'Lunas' => 'bg-success',
                                'Cicil' => 'bg-warning text-dark',
                                'Belum Bayar' => 'bg-danger'
                            ];
                            ?>
                            <tr>
                                <td><?= date('M Y', mktime(0, 0, 0, $l['bulan'], 1, $l['tahun'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($l['nama_penghuni']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        Kamar <?= htmlspecialchars($l['nomor_kamar']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong>Rp <?= number_format($l['jml_tagihan'], 0, ',', '.') ?></strong>
                                </td>
                                <td>
                                    Rp <?= number_format($l['total_bayar'], 0, ',', '.') ?>
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
                                    <span class="badge status-badge <?= $statusBadge[$l['status_bayar']] ?>">
                                        <?= $l['status_bayar'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($l['status_bayar'] !== 'Lunas'): ?>
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="bayarTagihan('<?= $l['id'] ?>', '<?= htmlspecialchars($l['nama_penghuni']) ?>', '<?= $l['nomor_kamar'] ?>', <?= $sisa ?>)">
                                                <i class="bi bi-credit-card"></i>
                                                Bayar
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="viewHistory('<?= $l['id'] ?>')">
                                            <i class="bi bi-clock-history"></i>
                                            Riwayat
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <?php
            $totalTagihan = array_sum(array_column($laporan, 'jml_tagihan'));
            $totalDibayar = array_sum(array_column($laporan, 'total_bayar'));
            $totalSisa = $totalTagihan - $totalDibayar;
            $lunas = count(array_filter($laporan, fn($l) => $l['status_bayar'] === 'Lunas'));
            $cicil = count(array_filter($laporan, fn($l) => $l['status_bayar'] === 'Cicil'));
            $belumBayar = count(array_filter($laporan, fn($l) => $l['status_bayar'] === 'Belum Bayar'));
            ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="text-center mb-3">Ringkasan Pembayaran</h6>
                            <div class="row text-center">
                                <div class="col-md-2">
                                    <h6>Total Tagihan</h6>
                                    <h5 class="text-primary">Rp <?= number_format($totalTagihan, 0, ',', '.') ?></h5>
                                </div>
                                <div class="col-md-2">
                                    <h6>Total Dibayar</h6>
                                    <h5 class="text-success">Rp <?= number_format($totalDibayar, 0, ',', '.') ?></h5>
                                </div>
                                <div class="col-md-2">
                                    <h6>Total Sisa</h6>
                                    <h5 class="text-danger">Rp <?= number_format($totalSisa, 0, ',', '.') ?></h5>
                                </div>
                                <div class="col-md-2">
                                    <h6>Lunas</h6>
                                    <h5 class="text-success"><?= $lunas ?></h5>
                                </div>
                                <div class="col-md-2">
                                    <h6>Cicilan</h6>
                                    <h5 class="text-warning"><?= $cicil ?></h5>
                                </div>
                                <div class="col-md-2">
                                    <h6>Belum Bayar</h6>
                                    <h5 class="text-danger"><?= $belumBayar ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Pembayaran Modal -->
<div class="modal fade" id="addPembayaranModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="bayar">
                    <div class="mb-3">
                        <label for="id_tagihan" class="form-label">Pilih Tagihan</label>
                        <select class="form-select" id="id_tagihan" name="id_tagihan" required>
                            <option value="">-- Pilih Tagihan --</option>
                            <?php if (!empty($tagihan)): ?>
                                <?php foreach ($tagihan as $t): ?>
                                    <?php if ($t['status_bayar'] !== 'Lunas'): ?>
                                        <option value="<?= $t['id'] ?>" 
                                                data-sisa="<?= $t['jml_tagihan'] - $t['jml_dibayar'] ?>">
                                            <?= htmlspecialchars($t['nama_penghuni']) ?> - 
                                            Kamar <?= htmlspecialchars($t['nomor_kamar']) ?> - 
                                            <?= date('M Y', mktime(0, 0, 0, $t['bulan'], 1, $t['tahun'])) ?> 
                                            (Sisa: Rp <?= number_format($t['jml_tagihan'] - $t['jml_dibayar'], 0, ',', '.') ?>)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="jml_bayar" class="form-label">Jumlah Bayar</label>
                        <input type="number" class="form-control" id="jml_bayar" name="jml_bayar" 
                               min="1" step="1000" required>
                        <div class="form-text">Masukkan jumlah pembayaran</div>
                    </div>
                    <div id="sisaInfo" class="alert alert-info" style="display: none;">
                        <i class="bi bi-info-circle"></i>
                        <span id="sisaText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-credit-card"></i>
                        Catat Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="historyContent">
                    <!-- Will be loaded via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle tagihan selection
document.getElementById('id_tagihan').addEventListener('change', function() {
    const option = this.selectedOptions[0];
    const sisaInfo = document.getElementById('sisaInfo');
    const sisaText = document.getElementById('sisaText');
    const jmlBayarInput = document.getElementById('jml_bayar');
    
    if (option.value) {
        const sisa = parseInt(option.dataset.sisa);
        sisaText.textContent = `Sisa tagihan: Rp ${sisa.toLocaleString('id-ID')}`;
        sisaInfo.style.display = 'block';
        jmlBayarInput.max = sisa;
        jmlBayarInput.value = sisa; // Auto-fill with remaining amount
    } else {
        sisaInfo.style.display = 'none';
        jmlBayarInput.max = '';
        jmlBayarInput.value = '';
    }
});

function bayarTagihan(id, nama, kamar, sisa) {
    const modal = new bootstrap.Modal(document.getElementById('addPembayaranModal'));
    const select = document.getElementById('id_tagihan');
    const jmlBayarInput = document.getElementById('jml_bayar');
    const sisaInfo = document.getElementById('sisaInfo');
    const sisaText = document.getElementById('sisaText');
    
    // Set the tagihan
    select.value = id;
    
    // Update info
    sisaText.textContent = `Sisa tagihan: Rp ${sisa.toLocaleString('id-ID')}`;
    sisaInfo.style.display = 'block';
    jmlBayarInput.max = sisa;
    jmlBayarInput.value = sisa;
    
    modal.show();
}

function viewHistory(tagihanId) {
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    const content = document.getElementById('historyContent');
    
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    
    // Here you would typically make an AJAX call to get payment history
    // For now, we'll show a placeholder
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Fitur riwayat pembayaran akan segera tersedia.
                <br>ID Tagihan: ${tagihanId}
            </div>
        `;
    }, 500);
    
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>