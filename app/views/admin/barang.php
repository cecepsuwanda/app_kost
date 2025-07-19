<?php 
ob_start(); 
$showSidebar = true;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-box-seam"></i>
        Kelola Barang
    </h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBarangModal">
        <i class="bi bi-plus-circle"></i>
        Tambah Barang
    </button>
</div>

<!-- Barang Table -->
<?php
// Prepare table data
$tableData = [];
$no = 1;
foreach ($barang as $b) {
    $buttons = [
        ['icon' => '<i class="bi bi-pencil"></i>', 'class' => 'btn-outline-primary', 'onclick' => 'editBarang(' . htmlspecialchars(json_encode($b)) . ')'],
        ['icon' => '<i class="bi bi-trash"></i>', 'class' => 'btn-outline-danger', 'onclick' => "deleteBarang({$b['id']}, '" . htmlspecialchars($b['nama']) . "')"]
    ];
    
    // Generate action buttons HTML
    $actionButtonsHtml = '<div class="btn-group btn-group-sm">';
    foreach ($buttons as $button) {
        $class = $button['class'] ?? 'btn-outline-primary';
        $title = isset($button['title']) ? ' title="' . htmlspecialchars($button['title']) . '"' : '';
        $onclick = isset($button['onclick']) ? ' onclick="' . $button['onclick'] . '"' : '';
        $disabled = isset($button['disabled']) && $button['disabled'] ? ' disabled' : '';
        
        $actionButtonsHtml .= "<button type=\"button\" class=\"btn {$class}\"{$title}{$onclick}{$disabled}>";
        $actionButtonsHtml .= $button['icon'] ?? '';
        $actionButtonsHtml .= isset($button['text']) ? ' ' . $button['text'] : '';
        $actionButtonsHtml .= '</button>';
    }
    $actionButtonsHtml .= '</div>';
    
    $tableData[] = [
        $no++,
        '<strong>' . htmlspecialchars($b['nama']) . '</strong>',
        '<span class="badge bg-success">Rp ' . number_format($b['harga'], 0, ',', '.') . '</span>',
        $actionButtonsHtml
    ];
}

// Render data table directly
$headers = ['No', 'Nama Barang', 'Harga', 'Aksi'];
$emptyMessage = 'Belum ada barang. Klik tombol "Tambah Barang" untuk menambahkan barang baru.';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Barang</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($tableData)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3"><?= $emptyMessage ?></h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <?php foreach ($headers as $header): ?>
                                <th><?= $header ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tableData as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= $cell ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Barang Modal -->
<div class="modal fade" id="addBarangModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Barang Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="harga" name="harga" min="0" step="1000" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Barang Modal -->
<div class="modal fade" id="editBarangModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_harga" class="form-label">Harga</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit_harga" name="harga" min="0" step="1000" required>
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

<!-- Delete Barang Modal -->
<div class="modal fade" id="deleteBarangModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Apakah Anda yakin ingin menghapus barang <strong id="delete_nama"></strong>?
                        <br><small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit modal
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editBarangModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            const harga = button.getAttribute('data-harga');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_harga').value = harga;
        });
    }
    
    // Handle delete modal
    const deleteModal = document.getElementById('deleteBarangModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nama = button.getAttribute('data-nama');
            
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nama').textContent = nama;
        });
    }
});
</script>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>