<?php 
ob_start(); 
$showSidebar = true;
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
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Kamar</h5>
    </div>
    <div class="card-body">
        <?php if (empty($kamar)): ?>
            <div class="text-center py-5">
                <i class="bi bi-door-open text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Belum ada kamar</h5>
                <p class="text-muted">Klik tombol "Tambah Kamar" untuk menambahkan kamar baru.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Gedung</th>
                            <th>Nomor Kamar</th>
                            <th>Harga Sewa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kamar as $k): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary">Gedung <?= $k['gedung'] ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($k['nomor']) ?></strong>
                                </td>
                                <td>Rp <?= number_format($k['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($k['status'] == 'kosong'): ?>
                                        <span class="badge bg-success">Kosong</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Terisi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="editKamar(<?= htmlspecialchars(json_encode($k)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($k['status'] == 'kosong'): ?>
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="deleteKamar(<?= $k['id'] ?>, '<?= htmlspecialchars($k['nomor']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary" disabled title="Kamar sedang terisi">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Kamar Modal -->
<div class="modal fade" id="addKamarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kamar Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/admin/kamar">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor Gedung</label>
                        <input type="number" class="form-control" name="gedung" placeholder="1, 2, 3, dll" min="1" required>
                        <div class="form-text">Nomor gedung tempat kamar berada</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor Kamar</label>
                        <input type="text" class="form-control" name="nomor" placeholder="Contoh: 101, A1, dll" required>
                        <div class="form-text">Nomor kamar harus unik dan mudah diingat</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Harga Sewa per Bulan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" name="harga" placeholder="500000" min="0" required>
                        </div>
                        <div class="form-text">Masukkan harga sewa bulanan dalam rupiah</div>
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