<?php $layout = 'base'; ?>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-cloud-arrow-down"></i> System Backup</div>
            <div class="card-body">
                <p class="text-muted">Trigger a database dump and download it securely. Full automation available in Phase 9.</p>
                <div class="alert alert-warning">
                    <i class="bi bi-shield-exclamation me-2"></i>
                    <strong>Production Warning:</strong> Only System Administrators should trigger backup operations.
                </div>
                <form method="POST" action="#">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary" disabled>
                        <i class="bi bi-cloud-arrow-down me-2"></i> Download Database Backup (.sql)
                    </button>
                    <span class="text-muted small ms-3">Enabled in Phase 9</span>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle"></i> Storage Usage</div>
            <div class="card-body">
                <?php
                $uploadPath = STORAGE_PATH . '/uploads';
                $totalBytes = 0;
                if (is_dir($uploadPath)) {
                    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadPath));
                    foreach ($it as $file) {
                        if ($file->isFile()) $totalBytes += $file->getSize();
                    }
                }
                ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Upload Storage Used</span>
                    <strong><?= file_size_format($totalBytes) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>
