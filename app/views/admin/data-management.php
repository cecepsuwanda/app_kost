<?php 
ob_start(); 
$showSidebar = true;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-database"></i>
        Kelola Data
    </h2>
    <div>
        <span class="text-muted">Terakhir diperbarui: <?= date('d M Y H:i') ?></span>
    </div>
</div>

<!-- Alert Messages -->
<?php if (isset($message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Data Management Cards -->
<div class="row">
    <!-- Export Data Card -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-download"></i>
                    Export Data ke SQL
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Ekspor seluruh data dari semua tabel dalam database ke file SQL. 
                    File hasil ekspor dapat digunakan untuk backup atau transfer data ke sistem lain.
                </p>
                
                <div class="mb-3">
                    <h6 class="text-muted">Data yang akan diekspor:</h6>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-check-circle text-success"></i> Data Pengguna (users)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Data Penghuni (tb_penghuni)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Data Kamar (tb_kamar)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Data Barang (tb_barang)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Data Hunian (tb_kmr_penghuni)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Detail Hunian (tb_detail_kmr_penghuni)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Barang Bawaan (tb_brng_bawaan)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Data Tagihan (tb_tagihan)</li>
                        <li><i class="bi bi-check-circle text-success"></i> Data Pembayaran (tb_bayar)</li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <small>File akan didownload dengan nama: <code>kos_data_export_YYYY-MM-DD_HH-mm-ss.sql</code></small>
                </div>
            </div>
            <div class="card-footer">
                <a href="<?= $config->appConfig('url') ?>/admin/export-sql" 
                   class="btn btn-primary w-100" 
                   onclick="return confirm('Apakah Anda yakin ingin mengekspor seluruh data? Proses ini mungkin memakan waktu beberapa saat.')">
                    <i class="bi bi-download"></i>
                    Download File SQL
                </a>
            </div>
        </div>
    </div>

    <!-- Import Data Card -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-upload"></i>
                    Import Data dari SQL
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    Impor data dari file SQL ke dalam database. File SQL harus berisi perintah CREATE TABLE dan INSERT 
                    yang valid untuk struktur database sistem kos.
                </p>
                
                <form action="<?= $config->appConfig('url') ?>/admin/import-sql" method="post" enctype="multipart/form-data" id="importForm">
                    <div class="mb-3">
                        <label for="sql_file" class="form-label">Pilih File SQL</label>
                        <input type="file" class="form-control" id="sql_file" name="sql_file" accept=".sql" required>
                        <div class="form-text">
                            File harus berformat .sql dan ukuran maksimal 50MB
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Peringatan:</strong> Proses import akan mengganti data yang sudah ada. 
                        Pastikan Anda telah membuat backup sebelum melakukan import.
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Persyaratan file SQL:</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check-circle text-success"></i> Format file: .sql</li>
                            <li><i class="bi bi-check-circle text-success"></i> Struktur tabel sesuai dengan sistem</li>
                            <li><i class="bi bi-check-circle text-success"></i> Perintah SQL yang valid</li>
                            <li><i class="bi bi-check-circle text-success"></i> Encoding UTF-8</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <button type="submit" form="importForm" class="btn btn-success w-100" 
                        onclick="return confirmImport()">
                    <i class="bi bi-upload"></i>
                    Import Data SQL
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Additional Information -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i>
                    Informasi Penting
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Tentang Export Data:</h6>
                        <ul>
                            <li>Export akan menyertakan struktur tabel (CREATE TABLE) dan data (INSERT)</li>
                            <li>File hasil export dapat digunakan untuk backup atau migrasi</li>
                            <li>Format file menggunakan encoding UTF-8</li>
                            <li>Relasi antar tabel akan dipertahankan</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Tentang Import Data:</h6>
                        <ul>
                            <li>Import akan menghapus data yang sudah ada (jika file berisi DROP TABLE)</li>
                            <li>Pastikan file SQL sesuai dengan struktur database sistem</li>
                            <li>Proses import akan menampilkan jumlah statement yang berhasil dieksekusi</li>
                            <li>Jika ada error, proses akan dilanjutkan untuk statement berikutnya</li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-primary mt-3">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Tips:</strong> 
                    Lakukan export data secara berkala sebagai backup. Sebelum melakukan import, 
                    pastikan Anda telah membuat backup data yang ada saat ini.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmImport() {
    const fileInput = document.getElementById('sql_file');
    if (!fileInput.files.length) {
        alert('Silakan pilih file SQL terlebih dahulu.');
        return false;
    }
    
    const file = fileInput.files[0];
    const fileName = file.name;
    const fileSize = (file.size / 1024 / 1024).toFixed(2); // MB
    
    const message = `Apakah Anda yakin ingin mengimpor data dari file "${fileName}" (${fileSize} MB)?\n\n` +
                   `PERINGATAN: Proses ini akan mengganti data yang sudah ada!\n\n` +
                   `Pastikan Anda telah membuat backup sebelum melanjutkan.`;
    
    return confirm(message);
}

// File validation
document.getElementById('sql_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const maxSize = 50 * 1024 * 1024; // 50MB
    const allowedExtensions = ['sql'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!allowedExtensions.includes(fileExtension)) {
        alert('File harus berformat .sql');
        e.target.value = '';
        return;
    }
    
    if (file.size > maxSize) {
        alert('Ukuran file tidak boleh lebih dari 50MB');
        e.target.value = '';
        return;
    }
    
    // Show file info
    const fileSize = (file.size / 1024 / 1024).toFixed(2);
    console.log(`File selected: ${file.name} (${fileSize} MB)`);
});
</script>

<?php 
$content = ob_get_clean(); 
include APP_PATH . '/views/layouts/main.php'; 
?>