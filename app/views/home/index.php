<?php 
ob_start(); 
?>

<div class="container">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="jumbotron bg-primary text-white p-5 rounded">
                <h1 class="display-4">Selamat Datang di <?= $config->appConfig('name') ?></h1>
                <p class="lead">Sistem manajemen kos yang memudahkan pengelolaan penghuni, kamar, tagihan, dan pembayaran.</p>
                <hr class="my-4 border-white">
                <p>Kelola kos Anda dengan lebih efisien dan terorganisir.</p>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="text-center">
                        <i class="bi bi-door-open text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title text-success mt-2">Kamar Tersedia</h5>
                        <h2 class="text-success"><?= count($kamarKosong ?? []) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="text-center">
                        <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                        <h5 class="card-title text-warning mt-2">Segera Jatuh Tempo</h5>
                        <h2 class="text-warning"><?= count($kamarMendekatiJatuhTempo ?? []) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="text-center">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                        <h5 class="card-title text-danger mt-2">Terlambat Bayar</h5>
                        <h2 class="text-danger"><?= count($tagihanTerlambat ?? []) ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Building Statistics -->
    <?php if (!empty($kamarKosong ?? [])): ?>
        <?php
        // Calculate statistics per building for available rooms
        $statistikGedung = [];
        foreach ($kamarKosong as $kamar) {
            $gedung = $kamar['gedung'];
            if (!isset($statistikGedung[$gedung])) {
                $statistikGedung[$gedung] = [
                    'jumlah_tersedia' => 0,
                    'harga_terendah' => $kamar['harga'],
                    'harga_tertinggi' => $kamar['harga']
                ];
            }
            $statistikGedung[$gedung]['jumlah_tersedia']++;
            $statistikGedung[$gedung]['harga_terendah'] = min($statistikGedung[$gedung]['harga_terendah'], $kamar['harga']);
            $statistikGedung[$gedung]['harga_tertinggi'] = max($statistikGedung[$gedung]['harga_tertinggi'], $kamar['harga']);
        }
        ksort($statistikGedung);
        ?>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i>
                            Ringkasan Per Gedung
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($statistikGedung as $gedung => $data): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border-info h-100">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="bi bi-building"></i>
                                                Gedung <?= $gedung ?>
                                            </h6>
                                            <div class="row text-center">
                                                <div class="col-12 mb-2">
                                                    <span class="badge bg-success" style="font-size: 1rem;">
                                                        <?= $data['jumlah_tersedia'] ?> Kamar Tersedia
                                                    </span>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-muted">
                                                        <strong>Harga:</strong><br>
                                                        Rp <?= number_format($data['harga_terendah'], 0, ',', '.') ?>
                                                        <?php if ($data['harga_terendah'] != $data['harga_tertinggi']): ?>
                                                            - Rp <?= number_format($data['harga_tertinggi'], 0, ',', '.') ?>
                                                        <?php endif; ?>
                                                    </small>
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
                    <?php if (empty($kamarKosong ?? [])): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Tidak ada kamar yang tersedia saat ini.</p>
                        </div>
                    <?php else: ?>
                        <?php
                        // Group rooms by building
                        $kamarPerGedung = [];
                        foreach ($kamarKosong as $kamar) {
                            $gedung = $kamar['gedung'];
                            if (!isset($kamarPerGedung[$gedung])) {
                                $kamarPerGedung[$gedung] = [];
                            }
                            $kamarPerGedung[$gedung][] = $kamar;
                        }
                        ksort($kamarPerGedung); // Sort by building number
                        ?>
                        
                        <?php foreach ($kamarPerGedung as $gedung => $kamarList): ?>
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-primary me-2" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                        <i class="bi bi-building"></i>
                                        Gedung <?= $gedung ?>
                                    </span>
                                    <span class="text-muted">
                                        (<?= count($kamarList) ?> kamar tersedia)
                                    </span>
                                </div>
                                
                                <div class="row">
                                    <?php foreach ($kamarList as $kamar): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card border-success h-100">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        <i class="bi bi-door-closed"></i>
                                                        Kamar <?= htmlspecialchars($kamar['nomor']) ?>
                                                    </h6>
                                                    <p class="card-text">
                                                        <strong>Harga:</strong> Rp <?= number_format($kamar['harga'], 0, ',', '.') ?>/bulan
                                                    </p>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="badge bg-success">Tersedia</span>
                                                        <small class="text-muted">
                                                            <i class="bi bi-building"></i>
                                                            Gedung <?= $gedung ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <?php if ($gedung !== array_key_last($kamarPerGedung)): ?>
                                <hr class="my-4">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Approaching Due Date Section -->
            <?php if (!empty($kamarMendekatiJatuhTempo ?? [])): ?>
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
                                    <th>Barang Bawaan</th>
                                    <th>Hari Tersisa</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($kamarMendekatiJatuhTempo ?? []) as $kamar): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-door-closed"></i>
                                            <?= htmlspecialchars($kamar['nomor_kamar']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($kamar['nama_penghuni']) ?></td>
                                        <td>
                                            <?php if (!empty($kamar['barang_bawaan'])): ?>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($kamar['barang_bawaan'] as $barang): ?>
                                                        <span class="badge bg-warning text-dark" style="font-size: 0.75rem;" title="<?= htmlspecialchars($barang['nama_barang']) ?> (+Rp <?= number_format($barang['harga_barang'], 0, ',', '.') ?>)">
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
          <?php if (!empty($tagihanTerlambat ?? [])): ?>
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
                                    <th>Gedung</th>
                                    <th>Kamar</th>
                                    <th>Penghuni</th>
                                    <th>Bulan</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Tagihan</th>
                                    <th>Dibayar</th>
                                    <th>Sisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($tagihanTerlambat ?? []) as $tagihan): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">Gedung <?= $tagihan['gedung'] ?></span>
                                        </td>
                                        <td>
                                            <i class="bi bi-door-closed"></i>
                                            <?= htmlspecialchars($tagihan['nomor_kamar']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($tagihan['nama_penghuni']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= date('M Y', mktime(0, 0, 0, $tagihan['bulan'], 1, $tagihan['tahun'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                <?= date('d/m/Y', strtotime($tagihan['tanggal'])) ?>
                                                <br><small>Terlambat <?= abs($tagihan['selisih_dari_tgl_masuk_kamar_penghuni']) ?> hari dari tanggal masuk kamar</small>
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

    <?php if ($isLoggedIn && isset($user)): ?>   
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
                            <a href="<?= $baseUrl ?>/admin" class="btn btn-outline-primary w-100">
                                <i class="bi bi-speedometer2"></i><br>
                                Dashboard Admin
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= $baseUrl ?>/admin/penghuni" class="btn btn-outline-success w-100">
                                <i class="bi bi-people"></i><br>
                                Kelola Penghuni
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= $baseUrl ?>/admin/tagihan" class="btn btn-outline-warning w-100">
                                <i class="bi bi-receipt"></i><br>
                                Kelola Tagihan
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="<?= $baseUrl ?>/admin/pembayaran" class="btn btn-outline-info w-100">
                                <i class="bi bi-credit-card"></i><br>
                                Pembayaran
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>