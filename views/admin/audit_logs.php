<?php $layout = 'base'; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-text"></i> System Audit Logs</span>
        <span class="text-muted small">Page <?= $page ?> of <?= $totalPages ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Incident</th>
                    <th>From → To</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No audit entries yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-muted small text-nowrap"><?= e(time_ago($log['created_at'])) ?></td>
                            <td><?= e($log['actor_name'] ?? 'System') ?></td>
                            <td><code><?= e($log['action']) ?></code></td>
                            <td>
                                <a href="<?= url("incidents/{$log['incident_uuid']}") ?>" class="text-decoration-none fw-bold">
                                    <?= e($log['reference_number']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($log['from_status']): ?>
                                    <?= badge_for_status($log['from_status']) ?>
                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                    <?= badge_for_status($log['to_status']) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= e(str_limit($log['comments'] ?? '', 50)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn-sm btn-outline-secondary">← Previous</a>
        <?php else: ?>
            <span></span>
        <?php endif; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-sm btn-outline-secondary">Next →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
