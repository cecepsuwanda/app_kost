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

<!-- Kamar Table -->
<?php
// Helper function for occupant list
function renderOccupantList($occupants) {
    if (empty($occupants)) {
        return '<span class="text-muted">-</span>';
    }

    $html = '<div class="penghuni-list">';
    foreach ($occupants as $index => $occupant) {
        $borderClass = $index > 0 ? 'border-top pt-1' : '';
        $html .= "<div class=\"penghuni-item mb-1 {$borderClass}\">";
        $html .= "<strong>" . htmlspecialchars($occupant['nama']) . "</strong>";
        $html .= "<br><small class=\"text-muted\">";
        $html .= "Masuk: " . date('d/m/Y', strtotime($occupant['tgl_masuk']));
        $html .= "</small>";
        if (!empty($occupant['no_ktp'])) {
            $html .= "<br><small class=\"text-muted\">";
            $html .= "KTP: " . htmlspecialchars($occupant['no_ktp']);
            $html .= "</small>";
        }
        $html .= "</div>";
    }
    $html .= '</div>';

    return $html;
}

// Helper function for belongings list
function renderBelongingsList($occupants) {
    $html = '<div class="barang-bawaan-list">';
    $hasItems = false;

    foreach ($occupants as $index => $occupant) {
        if (!empty($occupant['barang_bawaan'])) {
            $hasItems = true;
            $borderClass = $index > 0 ? 'border-top pt-2' : '';
            $html .= "<div class=\"penghuni-barang mb-2 {$borderClass}\">";
            $html .= "<small class=\"text-muted fw-bold\">" . htmlspecialchars($occupant['nama']) . ":</small>";
            $html .= '<div class="d-flex flex-wrap gap-1 mt-1">';
            
            foreach ($occupant['barang_bawaan'] as $item) {
                $title = htmlspecialchars($item['nama_barang']) . " (+Rp " . number_format($item['harga_barang'], 0, ',', '.') . ")";
                $html .= "<span class=\"badge bg-warning text-dark\" style=\"font-size: 0.7rem;\" title=\"{$title}\">";
                $html .= htmlspecialchars($item['nama_barang']);
                $html .= "</span>";
            }
            
            $html .= '</div></div>';
        }
    }

    $html .= '</div>';

    return $hasItems ? $html : '<span class="text-muted">-</span>';
}

// Function to render status badge
function getStatusBadge($status) {
    $statusMapping = [
        'active' => ['text' => 'Aktif', 'class' => 'bg-success'],
        'inactive' => ['text' => 'Tidak Aktif', 'class' => 'bg-danger'],
        'kosong' => ['text' => 'Kosong', 'class' => 'bg-success'],
        'tersedia' => ['text' => 'Tersedia', 'class' => 'bg-info'],
        'terisi' => ['text' => 'Terisi', 'class' => 'bg-info'],
        'penuh' => ['text' => 'Penuh', 'class' => 'bg-warning text-dark'],
        'lunas' => ['text' => 'Lunas', 'class' => 'bg-success'],
        'terlambat' => ['text' => 'Terlambat', 'class' => 'bg-danger'],
        'mendekati' => ['text' => 'Mendekati', 'class' => 'bg-warning text-dark'],
        'normal' => ['text' => 'Normal', 'class' => 'bg-secondary']
    ];
    
    $config = $statusMapping[$status] ?? ['text' => $status, 'class' => 'bg-secondary'];
    
    return "<span class=\"badge {$config['class']}\">{$config['text']}</span>";
}

// Prepare table data
$tableData = [];
foreach ($kamar as $k) {
    // Status badge
    $statusBadge = getStatusBadge($k['status']);
    
    // Action buttons
    $buttons = [
        [
            'icon' => '<i class="bi bi-pencil"></i>',
            'class' => 'btn-outline-primary',
            'onclick' => 'editKamar(' . json_encode([
                'id' => $k['id'],
                'gedung' => $k['gedung'],
                'nomor' => $k['nomor'],
                'harga' => $k['harga']
            ]) . ')',
            'title' => 'Edit Kamar'
        ],
        $k['status'] == 'kosong' ? [
            'icon' => '<i class="bi bi-trash"></i>',
            'class' => 'btn-outline-danger',
            'onclick' => "deleteKamar({$k['id']}, '" . addslashes($k['nomor']) . "')",
            'title' => 'Hapus Kamar'
        ] : [
            'icon' => '<i class="bi bi-lock"></i>',
            'class' => 'btn-outline-secondary',
            'disabled' => true,
            'title' => 'Kamar sedang terisi'
        ]
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
        '<span class="badge bg-primary">Gedung ' . $k['gedung'] . '</span>',
        '<strong>' . htmlspecialchars($k['nomor']) . '</strong>',
        'Rp ' . number_format($k['harga'], 0, ',', '.'),
        $statusBadge,
        $k['nama_penghuni'] ? renderOccupantList($k['penghuni_list'] ?? []) : '<span class="text-muted">-</span>',
        $k['nama_penghuni'] ? renderBelongingsList($k['penghuni_list'] ?? []) : '<span class="text-muted">-</span>',
        $actionButtonsHtml
    ];
}

// Render data table directly
$headers = ['Gedung', 'Nomor Kamar', 'Harga Sewa', 'Status', 'Penghuni', 'Barang Bawaan', 'Aksi'];
$emptyMessage = 'Belum ada kamar. Klik tombol "Tambah Kamar" untuk menambahkan kamar baru.';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Kamar</h5>
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

<!-- Add Kamar Modal -->
<?php
$addModalBody = '<input type="hidden" name="action" value="create">' .
    '<div class="mb-3">' .
        '<label class="form-label">Nomor Gedung</label>' .
        '<input type="number" class="form-control" name="gedung" placeholder="1, 2, 3, dll" required>' .
        '<div class="form-text">Nomor gedung tempat kamar berada</div>' .
    '</div>' .
    '<div class="mb-3">' .
        '<label class="form-label">Nomor Kamar</label>' .
        '<input type="text" class="form-control" name="nomor" placeholder="Contoh: 101, A1, dll" required>' .
        '<div class="form-text">Nomor kamar harus unik dan mudah diingat</div>' .
    '</div>' .
    '<div class="mb-3">' .
        '<label class="form-label">Harga Sewa per Bulan</label>' .
        '<div class="input-group">' .
            '<span class="input-group-text">Rp</span>' .
            '<input type="number" class="form-control" name="harga" placeholder="500000" min="0" required>' .
        '</div>' .
        '<div class="form-text">Masukkan harga sewa bulanan dalam rupiah</div>' .
    '</div>';

$addModalFooter = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>' .
                  '<button type="submit" class="btn btn-primary">Simpan</button>';

echo '<form method="POST" action="/admin/kamar">';
echo '<div class="modal fade" id="addKamarModal" tabindex="-1">' .
     '<div class="modal-dialog">' .
       '<div class="modal-content">' .
         '<div class="modal-header">' .
           '<h5 class="modal-title">Tambah Kamar Baru</h5>' .
           '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' .
         '</div>' .
         '<form method="POST" action="/admin/kamar">' .
           '<div class="modal-body">' .
             '<input type="hidden" name="action" value="create">' .
             $addModalBody .
           '</div>' .
           '<div class="modal-footer">' .
             $addModalFooter .
           '</div>' .
         '</form>' .
       '</div>' .
     '</div>' .
   '</div>';
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
    alert("masuk");
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