<?php
$layout = 'base';
$pageSubtitle = 'Status: ' . ucwords(str_replace('_', ' ', $workOrder->getStatus()));

ob_start();
?>
<a href="<?= url('work-orders') ?>" class="btn btn-outline-secondary me-2">
    <i class="bi bi-arrow-left me-1"></i> Back
</a>
<?php if ($workOrder->getStatus() !== 'completed' && $workOrder->getStatus() !== 'cancelled'): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateModal">
        <i class="bi bi-plus-circle me-1"></i> Add Update
    </button>
<?php endif; ?>
<?php
$pageActions = ob_get_clean();
?>

<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-info-circle"></i> Work Order Details</div>
            <div class="card-body">
                <h5 class="text-primary mb-3"><?= e($workOrder->getTitle()) ?></h5>
                <p class="text-dark" style="white-space: pre-wrap;"><?= e($workOrder->getAttributes()['description']) ?></p>
                
                <hr>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Priority</span>
                        <span class="badge bg-danger"><?= e(ucfirst($workOrder->getAttributes()['priority'])) ?></span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Started At</span>
                        <span class="fw-600"><?= $workOrder->getAttributes()['started_at'] ? date('M d, Y H:i', strtotime($workOrder->getAttributes()['started_at'])) : 'Not started' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-journal-text"></i> Progress Updates</div>
            <div class="card-body">
                <?php if (empty($updates)): ?>
                    <p class="text-muted text-center py-3 mb-0">No progress updates recorded yet.</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($updates as $update): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot primary"></div>
                                <div class="timeline-time"><?= date('M d, H:i', strtotime($update['created_at'])) ?></div>
                                <div class="timeline-title d-flex justify-content-between align-items-center">
                                    <span><?= e($update['officer_name']) ?> <span class="badge bg-secondary ms-2"><?= $update['progress_percent'] ?>%</span></span>
                                    <?php if ($update['is_internal']): ?>
                                        <span class="badge bg-warning text-dark" style="font-size: .65rem;">Internal Only</span>
                                    <?php endif; ?>
                                </div>
                                <div class="timeline-body mt-2 p-3 bg-light rounded border">
                                    <?= e($update['notes']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="card mb-4 bg-light">
            <div class="card-header bg-light"><i class="bi bi-link-45deg"></i> Related Incident</div>
            <div class="card-body">
                <!-- In a full implementation, we'd pass the incident details here or fetch via AJAX -->
                <p class="mb-0">This work order is linked to a civic incident.</p>
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-primary w-100" disabled>View Original Incident (Phase 6)</button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header"><i class="bi bi-cash"></i> Cost Tracking</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Estimated Cost:</span>
                    <span class="fw-600"><?= $workOrder->getAttributes()['estimated_cost'] ? 'TZS ' . number_format((float)$workOrder->getAttributes()['estimated_cost'], 2) : 'N/A' ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Actual Cost:</span>
                    <span class="fw-600"><?= $workOrder->getAttributes()['actual_cost'] ? 'TZS ' . number_format((float)$workOrder->getAttributes()['actual_cost'], 2) : 'Pending' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Progress Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Progress Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url("work-orders/{$workOrder->getUuid()}/progress") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="progress_percent" class="form-label">Completion Percentage</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" class="form-range flex-grow-1" id="progress_percent" name="progress_percent" min="0" max="100" step="10" value="0" oninput="document.getElementById('pct-val').innerText = this.value + '%'">
                            <span id="pct-val" class="fw-bold" style="min-width: 45px;">0%</span>
                        </div>
                        <div class="form-text text-danger">Setting to 100% will mark this work order as Completed and resolve the linked incident.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Update Notes <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" required placeholder="Describe the work done..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="actual_cost" class="form-label">Actual Cost Incurred (TZS)</label>
                        <input type="number" step="0.01" class="form-control" id="actual_cost" name="actual_cost" placeholder="Optional. Required if completing.">
                    </div>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_internal" name="is_internal" value="1" checked>
                        <label class="form-check-label" for="is_internal">
                            Internal Note (Hidden from citizen)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
