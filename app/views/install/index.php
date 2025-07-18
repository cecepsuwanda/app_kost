<?php 
ob_start(); 
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-download"></i>
                        Instalasi <?= $appName ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Penting!</strong> Proses instalasi akan membuat database dan tabel-tabel yang diperlukan untuk aplikasi ini.
                    </div>

                    <h5>Yang akan dilakukan selama instalasi:</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Membuat database <code><?= $config->database('name') ?></code>
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Membuat tabel <code>tb_penghuni</code> untuk data penghuni
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Membuat tabel <code>tb_kamar</code> untuk data kamar
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Membuat tabel <code>tb_barang</code> untuk data barang
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Membuat tabel <code>tb_kmr_penghuni</code> untuk relasi kamar-penghuni
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Membuat tabel <code>tb_brng_bawaan</code> untuk barang bawaan penghuni
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Membuat tabel <code>tb_tagihan</code> untuk data tagihan
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Membuat tabel <code>tb_bayar</code> untuk data pembayaran
                        </li>
                        <li class="list-group-item">
                            <i class="bi bi-check-circle text-success"></i>
                            Menambahkan data contoh untuk testing
                        </li>
                    </ul>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Peringatan:</strong> Pastikan konfigurasi database di <code>config/config.php</code> sudah benar sebelum melanjutkan.
                    </div>

                    <h5>Konfigurasi Database Saat Ini:</h5>
                    <table class="table table-sm">
                        <tr>
                            <th width="150">Host:</th>
                            <td><?= $config->database('host') ?></td>
                        </tr>
                        <tr>
                            <th>Database:</th>
                            <td><?= $config->database('name') ?></td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td><?= $config->database('user') ?></td>
                        </tr>
                        <tr>
                            <th>Charset:</th>
                            <td><?= $config->database('charset') ?></td>
                        </tr>
                    </table>

                    <div class="d-grid gap-2 mt-4">
                        <a href="<?= $baseUrl ?>/install/run" class="btn btn-primary btn-lg">
                            <i class="bi bi-play-circle"></i>
                            Mulai Instalasi
                        </a>
                        <a href="<?= $baseUrl ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i>
                            Kembali ke Beranda
                        </a>
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