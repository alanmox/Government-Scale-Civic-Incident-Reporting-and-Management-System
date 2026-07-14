<?php $layout = 'base'; ?>

<div class="card">
    <div class="card-header"><i class="bi bi-chat-dots"></i> Officer Updates on My Reports</div>
    <div class="card-body p-0">
        <?php if (empty($updates)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-chat-square-dots d-block mb-3" style="font-size:3rem;"></i>
                <p class="mb-0">No public updates yet. Officers will post here when they begin work.</p>
            </div>
        <?php else: ?>
            <div class="timeline p-4">
                <?php foreach ($updates as $update): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot primary"></div>
                        <div class="timeline-time"><?= e(time_ago($update['created_at'])) ?></div>
                        <div class="timeline-title">
                            <strong><?= e($update['officer_name'] ?? 'Officer') ?></strong>
                            &nbsp;·&nbsp;
                            <a href="<?= url("incidents/{$update['incident_uuid']}") ?>" class="text-primary text-decoration-none small">
                                <?= e($update['reference_number']) ?>
                            </a>
                            <?php if ($update['progress_percent'] > 0): ?>
                                <span class="badge bg-info text-dark ms-2"><?= $update['progress_percent'] ?>% complete</span>
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
