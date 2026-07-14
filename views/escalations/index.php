<?php $layout = 'base'; ?>

<?php if (!empty($escalated)): ?>
<div class="alert alert-danger d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
    <strong><?= count($escalated) ?> incident(s)</strong>&nbsp;require immediate attention.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-exclamation-octagon"></i> Escalated Incidents</span>
        <a href="<?= url('reports/export-incidents') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>SLA Deadline</th>
                    <th>Reported By</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($escalated)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle d-block mb-2 text-success" style="font-size:2rem;"></i>
                            No escalated incidents — great work!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($escalated as $row): ?>
                        <?php $breached = $row['sla_due_at'] && strtotime($row['sla_due_at']) < time(); ?>
                        <tr class="<?= $breached ? 'table-danger' : '' ?>">
                            <td class="fw-bold">
                                <a href="<?= url("incidents/{$row['uuid_str']}") ?>" class="text-decoration-none">
                                    <?= e($row['reference_number']) ?>
                                </a>
                            </td>
                            <td><?= e(str_limit($row['title'], 40)) ?></td>
                            <td><span class="badge bg-light text-dark"><?= e($row['category_name'] ?? '—') ?></span></td>
                            <td><?= badge_for_status($row['priority']) ?></td>
                            <td><?= badge_for_status($row['status']) ?></td>
                            <td class="<?= $breached ? 'text-danger fw-bold' : '' ?>">
                                <?= $row['sla_due_at'] ? format_date($row['sla_due_at'], 'M d, Y H:i') : '—' ?>
                                <?= $breached ? '<i class="bi bi-clock-fill ms-1"></i>' : '' ?>
                            </td>
                            <td><?= e($row['citizen_name'] ?? 'Unknown') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
