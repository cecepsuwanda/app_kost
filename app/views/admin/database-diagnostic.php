<?php 
ob_start(); 
$showSidebar = true;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-database-gear"></i>
        Database Diagnostics
    </h2>
    <div>
        <button type="button" class="btn btn-primary" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>
</div>

<!-- Overall Status Banner -->
<?php 
$overallStatus = 'success';
foreach ($diagnostics as $section => $data) {
    if (isset($data['status']) && $data['status'] === 'error') {
        $overallStatus = 'error';
        break;
    } elseif (isset($data['status']) && $data['status'] === 'warning') {
        $overallStatus = 'warning';
    }
}
?>
<div class="alert alert-<?= $overallStatus === 'success' ? 'success' : ($overallStatus === 'warning' ? 'warning' : 'danger') ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?= $overallStatus === 'success' ? 'check-circle' : ($overallStatus === 'warning' ? 'exclamation-triangle' : 'x-circle') ?>"></i>
    <strong>Database Status: </strong>
    <?php if ($overallStatus === 'success'): ?>
        All systems operational. Database is functioning normally.
    <?php elseif ($overallStatus === 'warning'): ?>
        Some issues detected. Please review the warnings below.
    <?php else: ?>
        Critical issues detected. Immediate attention required.
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<!-- Success/Error Messages -->
<?php if ($session->sessionFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i>
    <strong>Success!</strong> <?= htmlspecialchars($session->sessionFlash('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($session->sessionFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle"></i>
    <strong>Error!</strong> <?= htmlspecialchars($session->sessionFlash('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Connection Status -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-<?= $diagnostics['connection']['status'] === 'success' ? 'success' : 'danger' ?> text-white">
                <h5 class="mb-0">
                    <i class="bi bi-plugin"></i>
                    Database Connection
                </h5>
            </div>
            <div class="card-body">
                <?php if ($diagnostics['connection']['status'] === 'success'): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        <?= htmlspecialchars($diagnostics['connection']['message']) ?>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Host:</strong><br>
                            <?= htmlspecialchars($diagnostics['connection']['details']['host']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Database:</strong><br>
                            <?= htmlspecialchars($diagnostics['connection']['details']['database']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Charset:</strong><br>
                            <?= htmlspecialchars($diagnostics['connection']['details']['charset']) ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Connection ID:</strong><br>
                            <?= htmlspecialchars($diagnostics['connection']['details']['connection_id']) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i>
                        <strong><?= htmlspecialchars($diagnostics['connection']['message']) ?></strong>
                        <br><small><?= htmlspecialchars($diagnostics['connection']['error']) ?></small>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Host:</strong> <?= htmlspecialchars($diagnostics['connection']['details']['host']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Database:</strong> <?= htmlspecialchars($diagnostics['connection']['details']['database']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Charset:</strong> <?= htmlspecialchars($diagnostics['connection']['details']['charset']) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Database Information -->
<?php if ($diagnostics['database_info']['status'] === 'success'): ?>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i>
                    Database Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Version:</strong></td>
                        <td><?= htmlspecialchars($diagnostics['database_info']['version']) ?></td>
                    </tr>
                    <?php foreach ($diagnostics['database_info']['variables'] as $key => $value): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($key) ?>:</strong></td>
                        <td><?= htmlspecialchars($value) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-activity"></i>
                    Server Status
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <?php foreach ($diagnostics['database_info']['status_info'] as $key => $value): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($key) ?>:</strong></td>
                        <td><?= htmlspecialchars($value) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Storage Information -->
<?php if ($diagnostics['storage']['status'] === 'success'): ?>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-hdd"></i>
                    Storage Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary"><?= $diagnostics['storage']['database_size_mb'] ?> MB</h4>
                        <small class="text-muted">Database Size</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success"><?= $diagnostics['storage']['table_count'] ?></h4>
                        <small class="text-muted">Tables</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-speedometer2"></i>
                    Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12">
                        <h4 class="text-<?= $diagnostics['performance']['slow_queries'] > 0 ? 'warning' : 'success' ?>">
                            <?= $diagnostics['performance']['slow_queries'] ?>
                        </h4>
                        <small class="text-muted">Slow Queries</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tables Status -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-<?= $diagnostics['tables']['status'] === 'success' ? 'success' : 'warning' ?> text-white">
                <h5 class="mb-0">
                    <i class="bi bi-table"></i>
                    Tables Status
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($diagnostics['tables']['missing_tables'])): ?>
                <div class="alert alert-danger">
                    <strong>Missing Tables:</strong>
                    <ul class="mb-0">
                        <?php foreach ($diagnostics['tables']['missing_tables'] as $table): ?>
                        <li><?= htmlspecialchars($table) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($diagnostics['tables']['extra_tables'])): ?>
                <div class="alert alert-info">
                    <strong>Extra Tables (not part of schema):</strong>
                    <ul class="mb-0">
                        <?php foreach ($diagnostics['tables']['extra_tables'] as $table): ?>
                        <li><?= htmlspecialchars($table) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Status</th>
                                <th>Engine</th>
                                <th>Rows</th>
                                <th>Data Size</th>
                                <th>Index Size</th>
                                <th>Collation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnostics['tables']['table_status'] as $tableName => $status): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($tableName) ?></code></td>
                                <td>
                                    <?php if ($status['exists']): ?>
                                        <span class="badge bg-success">Exists</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Missing</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($status['exists']): ?>
                                <td><?= htmlspecialchars($status['engine']) ?></td>
                                <td><?= number_format($status['rows']) ?></td>
                                <td><?= number_format($status['data_length'] / 1024, 1) ?> KB</td>
                                <td><?= number_format($status['index_length'] / 1024, 1) ?> KB</td>
                                <td><?= htmlspecialchars($status['collation']) ?></td>
                                <?php else: ?>
                                <td colspan="5" class="text-muted">Table not found</td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table Structure Issues -->
<?php if ($diagnostics['table_structure']['status'] !== 'success' && !empty($diagnostics['table_structure']['issues'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    Table Structure Issues
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($diagnostics['table_structure']['issues'] as $table => $issue): ?>
                <div class="alert alert-warning">
                    <strong>Table: <?= htmlspecialchars($table) ?></strong><br>
                    <?php if ($issue['type'] === 'missing_columns'): ?>
                        Missing columns: <code><?= implode(', ', $issue['missing']) ?></code>
                    <?php elseif ($issue['type'] === 'table_not_accessible'): ?>
                        Error: <?= htmlspecialchars($issue['error']) ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Data Integrity Issues -->
<?php if ($diagnostics['data_integrity']['status'] !== 'success' && !empty($diagnostics['data_integrity']['issues'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-shield-exclamation"></i>
                    Data Integrity Issues
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($diagnostics['data_integrity']['issues'] as $issue): ?>
                <div class="alert alert-<?= $issue['type'] === 'orphaned_records' ? 'danger' : 'warning' ?>">
                    <strong><?= htmlspecialchars($issue['description']) ?></strong><br>
                    <?php if ($issue['type'] === 'orphaned_records'): ?>
                        Found <?= $issue['count'] ?> orphaned record(s)
                    <?php elseif ($issue['type'] === 'check_failed'): ?>
                        Check failed: <?= htmlspecialchars($issue['error']) ?>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Performance Details -->
<?php if ($diagnostics['performance']['status'] === 'success' && !empty($diagnostics['performance']['table_sizes'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i>
                    Table Sizes
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Rows</th>
                                <th>Size (MB)</th>
                                <th>Relative Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $maxSize = max(array_column($diagnostics['performance']['table_sizes'], 'size_mb'));
                            foreach ($diagnostics['performance']['table_sizes'] as $table): 
                            ?>
                            <tr>
                                <td><code><?= htmlspecialchars($table['table_name']) ?></code></td>
                                <td><?= number_format($table['table_rows']) ?></td>
                                <td><?= $table['size_mb'] ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <?php $percentage = $maxSize > 0 ? ($table['size_mb'] / $maxSize) * 100 : 0; ?>
                                        <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%">
                                            <?= round($percentage, 1) ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Database Errors -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-bug"></i>
                    Recent Database Errors
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($diagnostics['logs']['recent_errors'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    No recent database errors found.
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    Found <?= count($diagnostics['logs']['recent_errors']) ?> recent database-related error(s):
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Error Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diagnostics['logs']['recent_errors'] as $index => $error): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><small><code><?= htmlspecialchars($error) ?></code></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <small class="text-muted">
                        Log file: <?= $diagnostics['logs']['log_file_exists'] ? '✓ Available' : '✗ Not found' ?>
                        <?php if ($diagnostics['logs']['log_file_exists']): ?>
                        (<?= number_format($diagnostics['logs']['log_file_size'] / 1024, 1) ?> KB)
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Maintenance Mode Control -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-<?= $config->isMaintenanceMode() ? 'danger' : 'success' ?> text-white">
                <h5 class="mb-0">
                    <i class="bi bi-gear-fill"></i>
                    Maintenance Mode Control
                </h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-2">Current Status: 
                            <span class="badge bg-<?= $config->isMaintenanceMode() ? 'danger' : 'success' ?>">
                                <?= $config->isMaintenanceMode() ? '🔴 ENABLED' : '🟢 DISABLED' ?>
                            </span>
                        </h6>
                        <p class="mb-0 text-muted">
                            <?php if ($config->isMaintenanceMode()): ?>
                                All users will see the maintenance page. Only superadmin can access this page.
                            <?php else: ?>
                                Application is running normally. Users can access all features.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <form method="POST" action="<?= $baseUrl ?>/database-diagnostic/toggleMaintenance" style="display: inline;">
                            <?php if ($config->isMaintenanceMode()): ?>
                                <input type="hidden" name="maintenance_action" value="disable">
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to disable maintenance mode?')">
                                    <i class="bi bi-play-circle"></i>
                                    Enable Application
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="maintenance_action" value="enable">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to enable maintenance mode? All users will be unable to access the application.')">
                                    <i class="bi bi-pause-circle"></i>
                                    Enable Maintenance
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-tools"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="<?= $baseUrl ?>/install" class="btn btn-outline-warning w-100">
                            <i class="bi bi-arrow-clockwise"></i><br>
                            Reinstall Database
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button onclick="exportDiagnostics()" class="btn btn-outline-info w-100">
                            <i class="bi bi-download"></i><br>
                            Export Report
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button onclick="clearLogs()" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-trash"></i><br>
                            Clear Logs
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="<?= $baseUrl ?>/admin" class="btn btn-outline-primary w-100">
                            <i class="bi bi-arrow-left"></i><br>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportDiagnostics() {
    // Create downloadable diagnostic report
    const diagnosticsData = <?= json_encode($diagnostics) ?>;
    const blob = new Blob([JSON.stringify(diagnosticsData, null, 2)], {type: 'application/json'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'database-diagnostics-' + new Date().toISOString().split('T')[0] + '.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function clearLogs() {
    if (confirm('Are you sure you want to clear the error logs? This action cannot be undone.')) {
        window.location.href = '<?= $baseUrl ?>/database-diagnostic/clearLogs';
    }
}

// Auto-refresh every 30 seconds
setTimeout(function() {
    location.reload();
}, 30000);
</script>

<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>