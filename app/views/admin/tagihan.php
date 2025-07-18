<?php 
ob_start(); 
$showSidebar = true;

use App\Helpers\HtmlHelper as Html;
use App\Helpers\ViewHelper as View;
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
        <?php if (!empty($tagihan)): ?>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#recalculateAllModal">
                <i class="bi bi-calculator"></i>
                Hitung Ulang Semua
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Month Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="bulan" class="form-label">Filter Bulan</label>
                <input type="month" class="form-control" id="bulan" name="bulan" value="<?= $bulan ?>"
                       min="<?= date('Y-m') ?>"
                       max="<?= date('Y-m', strtotime('+1 month')) ?>">
                <div class="form-text">Hanya bisa melihat bulan ini atau bulan berikutnya</div>
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

<!-- Error Alert -->
<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i>
        <?= htmlspecialchars($error) ?>
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
                            <th>Tanggal Jatuh Tempo</th>
                            <th>Penghuni</th>
                            <th>Kamar</th>
                            <th>Barang Bawaan</th>
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
                            
                            // Determine due date status color
                            $dueDateClass = '';
                            $dueDateIcon = '';
                            $dueDateTooltip = '';
                            if (isset($t['status_waktu'])) {
                                switch ($t['status_waktu']) {
                                    case 'terlambat':
                                        $dueDateClass = 'text-danger fw-bold';
                                        $dueDateIcon = '<i class="bi bi-exclamation-triangle-fill me-1"></i>';
                                        $hariTerlambat = abs($t['selisih_dari_tgl_masuk_kamar_penghuni']);
                                        $dueDateTooltip = 'title="Terlambat ' . $hariTerlambat . ' hari dari tanggal masuk kamar"';
                                        break;
                                    case 'mendekati':
                                        $dueDateClass = 'text-warning fw-bold';
                                        $dueDateIcon = '<i class="bi bi-clock-fill me-1"></i>';
                                        $sisaHari = $t['selisih_dari_tgl_masuk_kamar_penghuni'];
                                        if ($sisaHari == 0) {
                                            $dueDateTooltip = 'title="Jatuh tempo hari ini (sesuai tanggal masuk kamar)"';
                                        } else {
                                            $dueDateTooltip = 'title="Sisa ' . $sisaHari . ' hari (dari tanggal masuk kamar)"';
                                        }
                                        break;
                                    case 'lunas':
                                        $dueDateClass = 'text-success';
                                        $dueDateIcon = '<i class="bi bi-check-circle-fill me-1"></i>';
                                        $dueDateTooltip = 'title="Sudah lunas"';
                                        break;
                                    default:
                                        $dueDateClass = 'text-muted';
                                        $dueDateTooltip = 'title="Masih normal"';
                                }
                            }
                            ?>
                            <tr>
                                <td><?= date('M Y', mktime(0, 0, 0, $t['bulan'], 1, $t['tahun'])) ?></td>
                                <td>
                                    <span class="<?= $dueDateClass ?>" <?= $dueDateTooltip ?>>
                                        <?= $dueDateIcon ?>
                                        <?= date('d/m/Y', strtotime($t['tanggal'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($t['nama_penghuni']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= $t['no_hp'] ? htmlspecialchars($t['no_hp']) : 'No HP tidak tersedia' ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($t['nomor_kamar']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($t['detail_penghuni'])): ?>
                                        <?php foreach ($t['detail_penghuni'] as $idx => $penghuni): ?>
                                            <?php if ($idx > 0): ?><hr class="my-1"><?php endif; ?>
                                            <div class="mb-1">
                                                <small class="text-muted fw-bold"><?= htmlspecialchars($penghuni['nama']) ?>:</small>
                                                <?php if (!empty($penghuni['barang_bawaan'])): ?>
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <?php foreach ($penghuni['barang_bawaan'] as $barang): ?>
                                                            <span class="badge bg-warning text-dark" style="font-size: 0.65rem;" title="<?= htmlspecialchars($barang['nama_barang']) ?> (+Rp <?= number_format($barang['harga_barang'], 0, ',', '.') ?>)">
                                                                <?= htmlspecialchars($barang['nama_barang']) ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <small class="text-muted">Tidak ada barang</small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>

                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
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
                                        <button type="button" class="btn btn-outline-warning" 
                                                onclick="recalculateTagihan(<?= $t['id'] ?>)"
                                                title="Hitung Ulang Tagihan">
                                            <i class="bi bi-calculator"></i>
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
            
            // Group by building for building-based summary
            $tagihanPerGedung = [];
            foreach ($tagihan as $t) {
                $gedung = $t['gedung'];
                if (!isset($tagihanPerGedung[$gedung])) {
                    $tagihanPerGedung[$gedung] = [
                        'total_tagihan' => 0,
                        'total_dibayar' => 0,
                        'jumlah_kamar' => 0
                    ];
                }
                $tagihanPerGedung[$gedung]['total_tagihan'] += $t['jml_tagihan'];
                $tagihanPerGedung[$gedung]['total_dibayar'] += $t['jml_dibayar'];
                $tagihanPerGedung[$gedung]['jumlah_kamar']++;
            }
            ksort($tagihanPerGedung);
            ?>
            
            <!-- Overall Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title text-center mb-3">Ringkasan Total</h5>
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

            <!-- Building-based Summary -->
            <?php if (!empty($tagihanPerGedung)): ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Ringkasan Per Gedung</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($tagihanPerGedung as $gedung => $data): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white text-center">
                                            <h6 class="mb-0">Gedung <?= $gedung ?></h6>
                                            <small><?= $data['jumlah_kamar'] ?> Kamar</small>
                                        </div>
                                        <div class="card-body">
                                            <div class="row text-center">
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Tagihan</small>
                                                    <div class="fw-bold text-primary">
                                                        Rp <?= number_format($data['total_tagihan'], 0, ',', '.') ?>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Dibayar</small>
                                                    <div class="fw-bold text-success">
                                                        Rp <?= number_format($data['total_dibayar'], 0, ',', '.') ?>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted">Sisa</small>
                                                    <div class="fw-bold text-danger">
                                                        Rp <?= number_format($data['total_tagihan'] - $data['total_dibayar'], 0, ',', '.') ?>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted">Progress</small>
                                                    <div class="progress" style="height: 8px;">
                                                        <?php $progress = $data['total_tagihan'] > 0 ? ($data['total_dibayar'] / $data['total_tagihan']) * 100 : 0; ?>
                                                        <div class="progress-bar bg-success" style="width: <?= $progress ?>%"></div>
                                                    </div>
                                                    <small class="text-muted"><?= round($progress, 1) ?>%</small>
                                                </div>
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
                               value="<?= date('Y-m') ?>"
                               min="<?= date('Y-m') ?>"
                               max="<?= date('Y-m', strtotime('+1 month')) ?>">
                        <div class="form-text">Hanya bisa generate untuk bulan ini atau bulan berikutnya</div>
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

<!-- Recalculate All Tagihan Modal -->
<div class="modal fade" id="recalculateAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hitung Ulang Semua Tagihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/admin/tagihan">
                <div class="modal-body">
                    <input type="hidden" name="action" value="recalculate_all">
                    <input type="hidden" name="bulan" value="<?= $bulan ?>">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Peringatan:</strong> Aksi ini akan menghitung ulang semua tagihan untuk bulan 
                        <strong><?= date('F Y', strtotime($bulan . '-01')) ?></strong> berdasarkan:
                        <ul class="mb-0 mt-2">
                            <li>Harga sewa kamar terkini</li>
                            <li>Biaya barang bawaan terkini</li>
                        </ul>
                        <hr>
                        <strong>Catatan:</strong> Tagihan yang sudah dibayar sebagian/lunas tetap akan dihitung ulang jumlah tagihannya, 
                        namun pembayaran yang sudah ada tidak akan berubah.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-calculator"></i>
                        Hitung Ulang Semua
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

function recalculateTagihan(id) {
    if (confirm('Apakah Anda yakin ingin menghitung ulang tagihan ini? Jumlah tagihan akan disesuaikan dengan harga terkini.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $baseUrl ?>/admin/tagihan';
        
        form.innerHTML = `
            <input type="hidden" name="action" value="recalculate">
            <input type="hidden" name="id_tagihan" value="${id}">
        `;
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>