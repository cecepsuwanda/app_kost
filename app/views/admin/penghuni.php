<?php 
ob_start(); 
$showSidebar = true;
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

<!-- Penghuni List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Penghuni</h5>
    </div>
    <div class="card-body">
        <?php if (empty($penghuni)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Belum ada penghuni</h5>
                <p class="text-muted">Klik tombol "Tambah Penghuni" untuk menambahkan penghuni baru.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>No. KTP</th>
                            <th>No. HP</th>
                            <th>Kamar</th>
                            <th>Barang Bawaan</th>
                            <th>Tgl Masuk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($penghuni as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($p['nama']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($p['no_ktp']) ?></td>
                                <td><?= htmlspecialchars($p['no_hp']) ?></td>
                                <td>
                                    <?php if ($p['nomor_kamar']): ?>
                                        <span class="badge bg-info">
                                            <?= htmlspecialchars($p['nomor_kamar']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum ada kamar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['barang_bawaan'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($p['barang_bawaan'] as $barang): ?>
                                                <span class="badge bg-warning text-dark" title="<?= htmlspecialchars($barang['nama_barang']) ?> (+Rp <?= number_format($barang['harga_barang'], 0, ',', '.') ?>)">
                                                    <?= htmlspecialchars($barang['nama_barang']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($p['tgl_masuk'])) ?></td>
                                <td>
                                    <?php if ($p['tgl_keluar']): ?>
                                        <span class="badge bg-danger">Keluar</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="editPenghuni(<?= htmlspecialchars(json_encode($p)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if (!$p['tgl_keluar']): ?>
                                            <button type="button" class="btn btn-outline-warning"
                                                    onclick="pindahKamar(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama']) ?>')">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="checkoutPenghuni(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama']) ?>')">
                                                <i class="bi bi-box-arrow-right"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="deletePenghuni(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama']) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Add Penghuni Modal -->
<div class="modal fade" id="addPenghuniModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Penghuni Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $baseUrl ?>/admin/penghuni">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. KTP</label>
                            <input type="text" class="form-control" name="no_ktp" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" class="form-control" name="no_hp" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Masuk</label>
                            <input type="date" class="form-control" name="tgl_masuk" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Kamar (Opsional)</label>
                        <select class="form-select" name="id_kamar">
                            <option value="">-- Belum pilih kamar --</option>
                            <?php foreach ($kamarTersedia as $kamar): ?>
                                <option value="<?= $kamar['id'] ?>">
                                    Kamar <?= htmlspecialchars($kamar['nomor']) ?> - Rp <?= number_format($kamar['harga'], 0, ',', '.') ?> 
                                    (<?= $kamar['slot_tersedia'] ?> slot tersedia)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Barang Bawaan (Opsional)</label>
                        <div class="row">
                            <?php foreach ($barang as $b): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="barang_ids[]" 
                                               value="<?= $b['id'] ?>" id="barang<?= $b['id'] ?>">
                                        <label class="form-check-label" for="barang<?= $b['id'] ?>">
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
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                        <label class="form-label">No. KTP</label>
                        <input type="text" class="form-control" name="no_ktp" id="edit_no_ktp" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control" name="no_hp" id="edit_no_hp" required>
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
    document.getElementById('edit_no_ktp').value = data.no_ktp;
    document.getElementById('edit_no_hp').value = data.no_hp;
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