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
        <i class="bi bi-people"></i>
        Kelola Penghuni
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPenghuniModal">
        <i class="bi bi-person-plus"></i>
        Tambah Penghuni
    </button>
</div>

<!-- Penghuni Table -->
<?php
// Prepare table data
$tableData = [];
foreach ($penghuni as $p) {
    // Room badge
    $roomBadge = $p['nomor_kamar'] ? Html::badge($p['nomor_kamar'], 'info') : Html::badge('Belum ada kamar', 'secondary');
    
    // Belongings badges
    $belongings = '<span class="text-muted">-</span>';
    if (!empty($p['barang_bawaan'])) {
        $belongingsBadges = [];
        foreach ($p['barang_bawaan'] as $br) {
            $title = htmlspecialchars($br['nama_barang']) . ' (+' . Html::currency($br['harga_barang']) . ')';
            $belongingsBadges[] = Html::badge(htmlspecialchars($br['nama_barang']), 'warning text-dark', ['title' => $title]);
        }
        $belongings = '<div class="d-flex flex-wrap gap-1">' . implode('', $belongingsBadges) . '</div>';
    }
    
    // Status badge
    $status = $p['tgl_keluar'] ? Html::badge('Keluar', 'danger') : Html::badge('Aktif', 'success');
    
    // Action buttons
    $buttons = [
        ['icon' => '<i class="bi bi-pencil"></i>', 'class' => 'btn-outline-primary', 'onclick' => 'editPenghuni(' . htmlspecialchars(json_encode($p)) . ')']
    ];
    
    if (!$p['tgl_keluar']) {
        $buttons[] = ['icon' => '<i class="bi bi-arrow-repeat"></i>', 'class' => 'btn-outline-warning', 'onclick' => "pindahKamar({$p['id']}, '" . htmlspecialchars($p['nama']) . "')"];
        $buttons[] = ['icon' => '<i class="bi bi-box-arrow-right"></i>', 'class' => 'btn-outline-danger', 'onclick' => "checkoutPenghuni({$p['id']}, '" . htmlspecialchars($p['nama']) . "')"];
    }
    
    $buttons[] = ['icon' => '<i class="bi bi-trash"></i>', 'class' => 'btn-outline-danger', 'onclick' => "deletePenghuni({$p['id']}, '" . htmlspecialchars($p['nama']) . "')"];
    
    $tableData[] = [
        '<strong>' . htmlspecialchars($p['nama']) . '</strong>',
        $p['no_ktp'] ? htmlspecialchars($p['no_ktp']) : '<span class="text-muted">-</span>',
        $p['no_hp'] ? htmlspecialchars($p['no_hp']) : '<span class="text-muted">-</span>',
        $roomBadge,
        $belongings,
        Html::date($p['tgl_masuk']),
        $status,
        renderActionButtons($buttons)
    ];
}

echo renderDataTable([
    'title' => 'Daftar Penghuni',
    'headers' => ['Nama', 'No. KTP', 'No. HP', 'Kamar', 'Barang Bawaan', 'Tgl Masuk', 'Status', 'Aksi'],
    'data' => $tableData,
    'emptyMessage' => 'Belum ada penghuni. Klik tombol "Tambah Penghuni" untuk menambahkan penghuni baru.',
    'actions' => [
        ['text' => 'Tambah Penghuni', 'modal' => 'addPenghuniModal', 'icon' => '<i class="bi bi-person-plus"></i>', 'class' => 'btn-primary']
    ]
]);
?>

<!-- Add Penghuni Modal -->
<?php
// Prepare room options
$roomOptions = ['' => '-- Belum pilih kamar --'];
foreach ($kamarTersedia as $kamar) {
    $roomOptions[$kamar['id']] = "Kamar {$kamar['nomor']} - " . Html::currency($kamar['harga']) . " ({$kamar['slot_tersedia']} slot tersedia)";
}

$addModalBody = '<input type="hidden" name="action" value="create">' .
    '<div class="row">' .
        Html::formGroup('Nama Lengkap', Html::input('text', 'nama', ['required' => true]), ['col' => 'col-md-6 mb-3']) .
        Html::formGroup('No. KTP (Opsional)', Html::input('text', 'no_ktp', ['placeholder' => 'Masukkan No. KTP jika ada']), ['col' => 'col-md-6 mb-3']) .
    '</div>' .
    '<div class="row">' .
        Html::formGroup('No. HP (Opsional)', Html::input('text', 'no_hp', ['placeholder' => 'Masukkan No. HP jika ada']), ['col' => 'col-md-6 mb-3']) .
        Html::formGroup('Tanggal Masuk', Html::input('date', 'tgl_masuk', ['value' => date('Y-m-d'), 'required' => true]), ['col' => 'col-md-6 mb-3']) .
    '</div>' .
    Html::formGroup('Pilih Kamar (Opsional)', Html::select('id_kamar', $roomOptions)) .
    Html::formGroup('Barang Bawaan (Opsional)', View::belongingsCheckboxes($barang));

$addModalFooter = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>' .
                  '<button type="submit" class="btn btn-primary">Simpan</button>';

echo '<form method="POST" action="' . $baseUrl . '/admin/penghuni">';
echo Html::modal('addPenghuniModal', 'Tambah Penghuni Baru', $addModalBody, $addModalFooter, ['size' => 'lg']);
echo '</form>';
?>


<!-- Edit Penghuni Modal -->
<div class="modal fade" id="editPenghuniModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Penghuni</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/admin/penghuni" id="editPenghuniForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" id="edit_nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">No. KTP (Opsional)</label>
                        <input type="text" class="form-control" name="no_ktp" id="edit_no_ktp" placeholder="Masukkan No. KTP jika ada">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">No. HP (Opsional)</label>
                        <input type="text" class="form-control" name="no_hp" id="edit_no_hp" placeholder="Masukkan No. HP jika ada">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Masuk</label>
                        <input type="date" class="form-control" name="tgl_masuk" id="edit_tgl_masuk" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Keluar (Opsional)</label>
                        <input type="date" class="form-control" name="tgl_keluar" id="edit_tgl_keluar">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Barang Bawaan (Opsional)</label>
                        <div class="row" id="editBarangBawaanContainer">
                            <?php foreach ($barang as $b): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="barang_ids[]" 
                                               value="<?= $b['id'] ?>" id="edit_barang<?= $b['id'] ?>">
                                        <label class="form-check-label" for="edit_barang<?= $b['id'] ?>">
                                            <?= htmlspecialchars($b['nama']) ?> 
                                            <small class="text-muted">(+Rp <?= number_format($b['harga'], 0, ',', '.') ?>)</small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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

<!-- Pindah Kamar Modal -->
<div class="modal fade" id="pindahKamarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pindah Kamar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
                            <form method="POST" action="<?= $baseUrl ?>/admin/penghuni">
                <div class="modal-body">
                    <input type="hidden" name="action" value="pindah_kamar">
                    <input type="hidden" name="id_penghuni" id="pindah_id_penghuni">
                    
                    <div class="mb-3">
                        <label class="form-label">Penghuni</label>
                        <input type="text" class="form-control" id="pindah_nama_penghuni" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kamar Baru</label>
                        <select class="form-select" name="id_kamar_baru" required>
                            <option value="">-- Pilih kamar baru --</option>
                            <?php foreach ($kamarTersedia as $kamar): ?>
                                <option value="<?= $kamar['id'] ?>">
                                    Kamar <?= htmlspecialchars($kamar['nomor']) ?> - Rp <?= number_format($kamar['harga'], 0, ',', '.') ?> 
                                    (<?= $kamar['slot_tersedia'] ?> slot tersedia)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pindah</label>
                        <input type="date" class="form-control" name="tgl_pindah" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Pindah Kamar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPenghuni(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama').value = data.nama;
    document.getElementById('edit_no_ktp').value = data.no_ktp || '';
    document.getElementById('edit_no_hp').value = data.no_hp || '';
    document.getElementById('edit_tgl_masuk').value = data.tgl_masuk;
    document.getElementById('edit_tgl_keluar').value = data.tgl_keluar || '';
    
    // Reset all barang bawaan checkboxes
    const barangCheckboxes = document.querySelectorAll('#editBarangBawaanContainer input[type="checkbox"]');
    barangCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Check the barang bawaan that this penghuni has
    if (data.barang_bawaan_ids && data.barang_bawaan_ids.length > 0) {
        data.barang_bawaan_ids.forEach(barangId => {
            const checkbox = document.getElementById('edit_barang' + barangId);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    
    new bootstrap.Modal(document.getElementById('editPenghuniModal')).show();
}

function pindahKamar(id, nama) {
    document.getElementById('pindah_id_penghuni').value = id;
    document.getElementById('pindah_nama_penghuni').value = nama;
    
    new bootstrap.Modal(document.getElementById('pindahKamarModal')).show();
}

function checkoutPenghuni(id, nama) {
    if (confirm(`Apakah Anda yakin ingin checkout ${nama} dari kos?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $baseUrl ?>/admin/penghuni';
        
        form.innerHTML = `
            <input type="hidden" name="action" value="checkout">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="tgl_keluar" value="<?= date('Y-m-d') ?>">
        `;
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deletePenghuni(id, nama) {
    if (confirm(`Apakah Anda yakin ingin menghapus data ${nama}? Tindakan ini tidak dapat dibatalkan.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $baseUrl ?>/admin/penghuni';
        
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