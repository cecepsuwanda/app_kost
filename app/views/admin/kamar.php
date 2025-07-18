<?php 
ob_start(); 
$showSidebar = true;

use App\Helpers\HtmlHelper as Html;
use App\Helpers\ViewHelper as View;
include APP_PATH . '/views/components/data_table.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-door-open"></i>
        Kelola Kamar
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKamarModal">
        <i class="bi bi-plus-circle"></i>
        Tambah Kamar
    </button>
</div>

<!-- Kamar Table -->
<?php
// Prepare table data
$tableData = [];
foreach ($kamar as $k) {
    $tableData[] = [
        View::buildingBadge($k['gedung']),
        '<strong>' . htmlspecialchars($k['nomor']) . '</strong>',
        Html::currency($k['harga']),
        renderStatusBadge($k['status']),
        $k['nama_penghuni'] ? View::occupantList($k['penghuni_list'] ?? []) : '<span class="text-muted">-</span>',
        $k['nama_penghuni'] ? View::belongingsList($k['penghuni_list'] ?? []) : '<span class="text-muted">-</span>',
        View::roomActionButtons($k)
    ];
}

echo renderDataTable([
    'title' => 'Daftar Kamar',
    'headers' => ['Gedung', 'Nomor Kamar', 'Harga Sewa', 'Status', 'Penghuni', 'Barang Bawaan', 'Aksi'],
    'data' => $tableData]);
?>

<!-- Add Kamar Modal -->
<?php
$addModalBody = '<input type="hidden" name="action" value="create">' .
    Html::formGroup('Nomor Gedung', Html::input('number', 'gedung', [
        'placeholder' => '1, 2, 3, dll',
        'required' => true,
        'min' => '1'
    ]), ['help' => 'Nomor gedung tempat kamar berada']) .
    Html::formGroup('Nomor Kamar', Html::input('text', 'nomor', [
        'placeholder' => 'Contoh: 101, A1, dll',
        'required' => true
    ]), ['help' => 'Nomor kamar harus unik dan mudah diingat']) .
    '<div class="mb-3">
        <label class="form-label">Harga Sewa per Bulan</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" class="form-control" name="harga" placeholder="500000" min="0" required>
        </div>
        <div class="form-text">Masukkan harga sewa bulanan dalam rupiah</div>
    </div>';

$addModalFooter = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>' .
                  '<button type="submit" class="btn btn-primary">Simpan</button>';

echo '<form method="POST" action="/admin/kamar">';
echo Html::modal('addKamarModal', 'Tambah Kamar Baru', $addModalBody, $addModalFooter);
echo '</form>';
?>

<!-- Edit Kamar Modal -->
<div class="modal fade" id="editKamarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kamar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/admin/kamar">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor Gedung</label>
                        <input type="number" class="form-control" name="gedung" id="edit_gedung" min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor Kamar</label>
                        <input type="text" class="form-control" name="nomor" id="edit_nomor" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Harga Sewa per Bulan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="harga" id="edit_harga" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editKamar(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_gedung').value = data.gedung;
    document.getElementById('edit_nomor').value = data.nomor;
    document.getElementById('edit_harga').value = data.harga;
    
    new bootstrap.Modal(document.getElementById('editKamarModal')).show();
}

function deleteKamar(id, nomor) {
    if (confirm(`Apakah Anda yakin ingin menghapus kamar ${nomor}? Tindakan ini tidak dapat dibatalkan.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $baseUrl ?>/admin/kamar';
        
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
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