<?php
$layout = 'base';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-patch-check"></i> Verification Queue</span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Category</th>
                    <th>Reported By</th>
                    <th>Date Reported</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size: 2rem;"></i>
                            No incidents awaiting verification.
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
                            <td><?= e($incident->getCitizenName() ?? 'Anonymous') ?></td>
                            <td><?= e(date('M d, Y H:i', strtotime($incident->getCreatedAt()))) ?></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#verifyModal<?= $incident->getUuid() ?>">
                                    Review
                                </button>
                            </td>
                        </tr>

                        <!-- Verification Modal -->
                        <div class="modal fade" id="verifyModal<?= $incident->getUuid() ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Verify Incident: <?= e($incident->getReferenceNumber()) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?= url('verification/process') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="incident_id" value="<?= e($incident->getUuid()) ?>">
                                        <div class="modal-body">
                                            <p><strong>Title:</strong> <?= e($incident->getTitle()) ?></p>
                                            <p><strong>Description:</strong> <?= e($incident->getDescription()) ?></p>
                                            
                                            <div class="mb-3">
                                                <label for="comments<?= $incident->getUuid() ?>" class="form-label">Comments (Required if rejecting)</label>
                                                <textarea class="form-control" id="comments<?= $incident->getUuid() ?>" name="comments" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="action" value="reject" class="btn btn-danger">Reject</button>
                                            <button type="submit" name="action" value="approve" class="btn btn-success">Approve & Route</button>
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
