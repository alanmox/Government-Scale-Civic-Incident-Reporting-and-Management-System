<?php
$layout = 'base';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person-check"></i> Incident Assignments</span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>SLA Due</th>
                    <th class="text-end">Assign</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                            No verified incidents awaiting assignment.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($incidents as $incData): 
                        $incident = new \App\Models\Incident($incData);
                        $isBreached = $incident->isSlaBreached();
                    ?>
                        <tr>
                            <td class="fw-600">
                                <a href="<?= url("incidents/{$incident->getUuid()}") ?>" style="text-decoration: none;">
                                    <?= e($incident->getReferenceNumber()) ?>
                                </a>
                            </td>
                            <td><?= e(strlen($incident->getTitle()) > 30 ? substr($incident->getTitle(), 0, 30) . '...' : $incident->getTitle()) ?></td>
                            <td><?= e($incident->getCategoryName() ?? 'Unknown') ?></td>
                            <td>
                                <span class="<?= $incident->getStatusBadgeClass() ?>">
                                    <?= e(ucwords(str_replace('_', ' ', $incident->getStatus()))) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($incident->getAttributes()['sla_due_at']): ?>
                                    <span class="<?= $isBreached ? 'text-danger fw-bold' : '' ?>">
                                        <?= e(date('M d, H:i', strtotime($incident->getAttributes()['sla_due_at']))) ?>
                                        <?= $isBreached ? ' <i class="bi bi-exclamation-circle"></i>' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal<?= $incident->getUuid() ?>">
                                    <?= $incident->getStatus() === 'assigned' ? 'Reassign' : 'Assign' ?>
                                </button>
                            </td>
                        </tr>

                        <!-- Assignment Modal -->
                        <div class="modal fade" id="assignModal<?= $incident->getUuid() ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Assign Incident: <?= e($incident->getReferenceNumber()) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= url('assignments/assign') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="incident_id" value="<?= e($incident->getUuid()) ?>">
                                        
                                        <div class="modal-body">
                                            <div class="mb-3 border rounded p-3 bg-light">
                                                <strong><?= e($incident->getTitle()) ?></strong><br>
                                                <span class="text-muted small">Current Status: <?= e(ucwords(str_replace('_', ' ', $incident->getStatus()))) ?></span>
                                                <?php if ($incident->getAssignedOfficerName()): ?>
                                                    <br><span class="text-muted small">Currently assigned to: <?= e($incident->getAssignedOfficerName()) ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label for="officer_id<?= $incident->getUuid() ?>" class="form-label">Select Officer <span class="text-danger">*</span></label>
                                                <select class="form-select" id="officer_id<?= $incident->getUuid() ?>" name="officer_id" required>
                                                    <option value="">-- Choose an Officer --</option>
                                                    <?php foreach ($officers as $officer): ?>
                                                        <option value="<?= e($officer['uuid_str']) ?>"><?= e($officer['full_name']) ?> (<?= e($officer['username']) ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="comments<?= $incident->getUuid() ?>" class="form-label">Assignment Notes (Optional)</label>
                                                <textarea class="form-control" id="comments<?= $incident->getUuid() ?>" name="comments" rows="2" placeholder="Instructions for the officer..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Confirm Assignment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
