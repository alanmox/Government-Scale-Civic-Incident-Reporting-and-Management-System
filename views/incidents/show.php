<?php
$layout = 'base';
$pageSubtitle = 'Reported on ' . date('M d, Y', strtotime($incident->getCreatedAt()));

ob_start();
?>
<a href="<?= url('incidents/my') ?>" class="btn btn-outline-secondary me-2">
    <i class="bi bi-arrow-left me-1"></i> Back to List
</a>
<?php if ($session->hasPermission('incident.verify') && $incident->getStatus() === 'submitted'): ?>
    <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Verify</button>
<?php endif; ?>
<?php
$pageActions = ob_get_clean();
?>

<div class="row">
    <div class="col-lg-8">
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-info-circle"></i> Incident Details</span>
                <span class="<?= $incident->getStatusBadgeClass() ?>">
                    <?= e(ucwords(str_replace('_', ' ', $incident->getStatus()))) ?>
                </span>
            </div>
            <div class="card-body">
                <h4 class="mb-3 text-primary"><?= e($incident->getTitle()) ?></h4>
                
                <div class="mb-4">
                    <p class="text-dark" style="white-space: pre-wrap; line-height: 1.6;"><?= e($incident->getDescription()) ?></p>
                </div>
                
                <div class="row g-3 bg-light p-3 rounded">
                    <div class="col-md-6">
                        <div class="text-muted" style="font-size: .8rem;">Category</div>
                        <div class="fw-600"><?= e($incident->getCategoryName() ?? 'Unknown') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted" style="font-size: .8rem;">Priority</div>
                        <div>
                            <span class="<?= $incident->getPriorityBadgeClass() ?>">
                                <?= e(ucfirst($incident->getPriority())) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted" style="font-size: .8rem;">Location</div>
                        <div class="fw-600"><?= e($incident->getAttributes()['location_desc'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted" style="font-size: .8rem;">Visibility</div>
                        <div>
                            <?php if ($incident->getAttributes()['is_public']): ?>
                                <span class="badge bg-info"><i class="bi bi-globe me-1"></i>Public</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i>Private</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placeholder for Timeline (Phase 6) -->
        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history"></i> Status Timeline</div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot success"></div>
                        <div class="timeline-time"><?= date('M d, Y H:i', strtotime($incident->getCreatedAt())) ?></div>
                        <div class="timeline-title">Incident Reported</div>
                        <div class="timeline-body">Incident was submitted successfully by the citizen.</div>
                    </div>
                    <!-- Additional timeline items rendered dynamically -->
                </div>
            </div>
        </div>

    </div>
    
    <div class="col-lg-4">
        
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-person"></i> Reporter Info</div>
            <div class="card-body text-center">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary-light); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem;">
                    <?= e(strtoupper(substr($incident->getCitizenName() ?? 'C', 0, 1))) ?>
                </div>
                <h5 class="mb-1"><?= e($incident->getCitizenName() ?? 'Anonymous Citizen') ?></h5>
                <p class="text-muted small">Registered Citizen</p>
                <hr>
                <div class="text-start text-muted small">
                    <div class="mb-2"><i class="bi bi-telephone me-2"></i> Contact info hidden</div>
                </div>
            </div>
        </div>
        
        <?php if ($incident->getAssignedOfficerName()): ?>
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-person-badge"></i> Assigned Officer</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person text-muted"></i>
                    </div>
                    <div>
                        <div class="fw-600"><?= e($incident->getAssignedOfficerName()) ?></div>
                        <div class="text-muted small">Government Officer</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header"><i class="bi bi-paperclip"></i> Attachments</div>
            <div class="card-body">
                <p class="text-muted text-center py-3 mb-0 small">No attachments provided.</p>
            </div>
        </div>

    </div>
</div>
