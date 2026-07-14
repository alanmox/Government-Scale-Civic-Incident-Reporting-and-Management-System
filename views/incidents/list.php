<?php $layout = 'base'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0 text-primary"><?= e(__('nav.incidents')) ?></h4>
    <a href="<?= url('incidents/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> <?= e(__('incident.report')) ?>
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list"></i> All Incidents</span>
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="submitted" <?= $status === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Verified</option>
                <option value="assigned" <?= $status === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
                <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Citizen</th>
                    <th>Category</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
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
                            <td><?= e($incident->getCitizenName() ?? 'Unknown') ?></td>
                            <td><?= e($incident->getCategoryName() ?? 'Unknown') ?></td>
                            <td>
                                <?= e(strlen($incident->getTitle()) > 40 ? substr($incident->getTitle(), 0, 40) . '...' : $incident->getTitle()) ?>
                            </td>
                            <td><?= e(date('M d, Y', strtotime($incident->getCreatedAt()))) ?></td>
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
