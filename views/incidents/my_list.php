<?php
$layout = 'base';
ob_start();
?>
<a href="<?= url('incidents/create') ?>" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i> Report Incident
</a>
<?php
$pageActions = ob_get_clean();
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list"></i> Your Reported Incidents</span>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <?= e(__('filter')) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="?status=all">All</a></li>
                <li><a class="dropdown-item" href="?status=submitted">Submitted</a></li>
                <li><a class="dropdown-item" href="?status=in_progress">In Progress</a></li>
                <li><a class="dropdown-item" href="?status=resolved">Resolved</a></li>
            </ul>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Category</th>
                    <th>Title</th>
                    <th>Date Reported</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                            No incidents found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($incidents as $incData): 
                        $incident = new \App\Models\Incident($incData);
                    ?>
                        <tr>
                            <td class="fw-600">
                                <a href="<?= url("incidents/{$incident->getUuid()}") ?>" style="text-decoration: none;">
                                    <?= e($incident->getReferenceNumber()) ?>
                                </a>
                            </td>
                            <td><?= e($incident->getCategoryName() ?? 'Unknown') ?></td>
                            <td>
                                <?= e(strlen($incident->getTitle()) > 40 ? substr($incident->getTitle(), 0, 40) . '...' : $incident->getTitle()) ?>
                            </td>
                            <td><?= e(date('M d, Y H:i', strtotime($incident->getCreatedAt()))) ?></td>
                            <td>
                                <span class="<?= $incident->getStatusBadgeClass() ?>">
                                    <?= e(ucwords(str_replace('_', ' ', $incident->getStatus()))) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url("incidents/{$incident->getUuid()}") ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
