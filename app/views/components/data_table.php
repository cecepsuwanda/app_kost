<?php
/**
 * Reusable Data Table Component
 */

function renderDataTable($config) {
    $title = $config['title'] ?? '';
    $headers = $config['headers'] ?? [];
    $data = $config['data'] ?? [];
    $actions = $config['actions'] ?? [];
    $emptyMessage = $config['emptyMessage'] ?? 'Tidak ada data';
    $responsive = $config['responsive'] ?? true;
    $striped = $config['striped'] ?? true;
    
    ob_start();
    ?>
    
    <div class="card">
        <?php if ($title): ?>
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= $title ?></h5>
                <?php if (!empty($actions)): ?>
                    <div class="btn-group">
                        <?php foreach ($actions as $action): ?>
                            <button type="button" 
                                    class="btn <?= $action['class'] ?? 'btn-primary' ?>"
                                    <?= isset($action['modal']) ? 'data-bs-toggle="modal" data-bs-target="#' . $action['modal'] . '"' : '' ?>
                                    <?= isset($action['onclick']) ? 'onclick="' . $action['onclick'] . '"' : '' ?>>
                                <?= $action['icon'] ?? '' ?>
                                <?= $action['text'] ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="card-body p-0">
            <?php if (empty($data)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h5 class="text-muted mt-3"><?= $emptyMessage ?></h5>
                </div>
            <?php else: ?>
                <?php if ($responsive): ?><div class="table-responsive"><?php endif; ?>
                
                <table class="table mb-0<?= $striped ? ' table-striped' : '' ?>">
                    <thead>
                        <tr>
                            <?php foreach ($headers as $header): ?>
                                <th><?= $header ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?= $cell ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($responsive): ?></div><?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

function renderStatusBadge($status, $mapping = []) {
    $defaultMapping = [
        'active' => ['text' => 'Aktif', 'class' => 'bg-success'],
        'inactive' => ['text' => 'Tidak Aktif', 'class' => 'bg-danger'],
        'kosong' => ['text' => 'Kosong', 'class' => 'bg-success'],
        'terisi' => ['text' => 'Terisi', 'class' => 'bg-info'],
        'penuh' => ['text' => 'Penuh', 'class' => 'bg-warning text-dark'],
        'lunas' => ['text' => 'Lunas', 'class' => 'bg-success'],
        'terlambat' => ['text' => 'Terlambat', 'class' => 'bg-danger'],
        'mendekati' => ['text' => 'Mendekati', 'class' => 'bg-warning text-dark'],
        'normal' => ['text' => 'Normal', 'class' => 'bg-secondary']
    ];
    
    $config = array_merge($defaultMapping, $mapping)[$status] ?? ['text' => $status, 'class' => 'bg-secondary'];
    
    return "<span class=\"badge {$config['class']}\">{$config['text']}</span>";
}

function renderActionButtons($buttons, $size = 'sm') {
    if (empty($buttons)) return '';
    
    $html = "<div class=\"btn-group btn-group-{$size}\">";
    
    foreach ($buttons as $button) {
        $class = $button['class'] ?? 'btn-outline-primary';
        $title = isset($button['title']) ? " title=\"{$button['title']}\"" : '';
        $onclick = isset($button['onclick']) ? " onclick=\"{$button['onclick']}\"" : '';
        $disabled = isset($button['disabled']) && $button['disabled'] ? ' disabled' : '';
        
        $html .= "<button type=\"button\" class=\"btn {$class}\"{$title}{$onclick}{$disabled}>";
        $html .= $button['icon'] ?? '';
        $html .= isset($button['text']) ? ' ' . $button['text'] : '';
        $html .= '</button>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>