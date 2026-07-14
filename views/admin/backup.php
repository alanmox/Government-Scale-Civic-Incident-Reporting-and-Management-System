<?php $layout = 'base'; ?>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-cloud-arrow-down"></i> System Backup</div>
            <div class="card-body">
                <p class="text-muted">Trigger a database dump (mysqldump) and download it securely.</p>
                <div class="alert alert-warning">
                    <i class="bi bi-shield-exclamation me-2"></i>
                    <strong>Production Warning:</strong> Only System Administrators should trigger backup operations.
                </div>
                
                <form method="POST" action="<?= url('admin/backup/create') ?>" class="mb-4">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-arrow-down me-2"></i> Take Database Backup
                    </button>
                </form>

                <h6 class="border-bottom pb-2 mb-3">Previous Backups</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-gcirms">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Date Taken</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($backups)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No backups available.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($backups as $b): ?>
                                    <tr>
                                        <td class="fw-bold"><?= e($b['name']) ?></td>
                                        <td><?= file_size_format($b['size']) ?></td>
                                        <td><?= date('Y-m-d H:i:s', $b['date']) ?></td>
                                        <td>
                                            <a href="<?= url('admin/backup/download?file=' . urlencode($b['name'])) ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
