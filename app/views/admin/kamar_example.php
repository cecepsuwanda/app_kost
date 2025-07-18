<?php 
ob_start(); 
$showSidebar = true;

use App\Helpers\HtmlHelper as Html;
use App\Helpers\ViewHelper as View;
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

<!-- Kamar List -->
<?php
$content = '';

if (empty($kamar)) {
    $addButton = '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addKamarModal">
                    <i class="bi bi-plus-circle"></i> Tambah Kamar
                  </button>';
    
    $content = View::emptyState(
        'door-open', 
        'Belum ada kamar', 
        'Klik tombol "Tambah Kamar" untuk menambahkan kamar baru.',
        $addButton
    );
} else {
    // Prepare table data
    $headers = ['Gedung', 'Nomor Kamar', 'Harga Sewa', 'Status', 'Penghuni', 'Barang Bawaan', 'Aksi'];
    $rows = [];
    
    foreach ($kamar as $k) {
        $rows[] = [
            View::buildingBadge($k['gedung']),
            '<strong>' . htmlspecialchars($k['nomor']) . '</strong>',
            Html::currency($k['harga']),
            View::roomStatusBadge($k['status']),
            $k['nama_penghuni'] ? View::occupantList($k['penghuni_list'] ?? []) : '<span class="text-muted">-</span>',
            $k['nama_penghuni'] ? View::belongingsList($k['penghuni_list'] ?? []) : '<span class="text-muted">-</span>',
            View::roomActionButtons($k)
        ];
    }
    
    $content = Html::table($headers, $rows);
}

echo Html::card('Daftar Kamar', $content);
?>

<!-- Add Kamar Modal -->
<?php
$addModalBody = '
    <input type="hidden" name="action" value="create">
    
    ' . Html::formGroup(
        'Nomor Gedung',
        Html::input('number', 'gedung', [
            'placeholder' => '1, 2, 3, dll',
            'required' => true,
            'class' => 'form-control'
        ]),
        ['help' => 'Nomor gedung tempat kamar berada']
    ) . '
    
    ' . Html::formGroup(
        'Nomor Kamar', 
        Html::input('text', 'nomor', [
            'placeholder' => 'Contoh: 101, A1, dll',
            'required' => true
        ]),
        ['help' => 'Nomor kamar harus unik dan mudah diingat']
    ) . '
    
    <div class="mb-3">
        <label class="form-label">Harga Sewa per Bulan</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" class="form-control" name="harga" placeholder="500000" min="0" required>
        </div>
        <div class="form-text">Masukkan harga sewa bulanan dalam rupiah</div>
    </div>
';

$addModalFooter = '
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-primary">Simpan</button>
';

echo '<form method="POST" action="/admin/kamar">';
echo Html::modal('addKamarModal', 'Tambah Kamar Baru', $addModalBody, $addModalFooter);
echo '</form>';
?>

<!-- Edit Kamar Modal -->
<?php
$editModalBody = '
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" id="edit_id">
    
    ' . Html::formGroup(
        'Nomor Gedung',
        Html::input('number', 'gedung', ['required' => true, 'class' => 'form-control']),
        ['col' => 'mb-3']
    ) . '
    
    ' . Html::formGroup(
        'Nomor Kamar',
        Html::input('text', 'nomor', ['required' => true]),
        ['col' => 'mb-3']  
    ) . '
    
    <div class="mb-3">
        <label class="form-label">Harga Sewa per Bulan</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" class="form-control" name="harga" id="edit_harga" min="0" required>
        </div>
    </div>
';

$editModalFooter = '
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-primary">Update</button>
';

echo '<form method="POST" action="/admin/kamar">';
echo Html::modal('editKamarModal', 'Edit Kamar', $editModalBody, $editModalFooter);
echo '</form>';
?>

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
        form.action = '/admin/kamar';
        
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