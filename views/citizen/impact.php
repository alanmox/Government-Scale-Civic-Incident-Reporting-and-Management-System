<?php $layout = 'base'; ?>

<!-- Impact Score Card -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#1a3a6b,#2d5a9e); color:#fff;">
            <div class="stat-icon" style="background:rgba(255,255,255,.2);"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div class="stat-label" style="color:rgba(255,255,255,.8);">Total Reports</div>
                <div class="stat-value"><?= $stats['total_reports'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="stat-label">Resolved</div>
                <div class="stat-value text-success"><?= $stats['resolved'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-clock"></i></div>
            <div>
                <div class="stat-label">Active Reports</div>
                <div class="stat-value"><?= $stats['active'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-trophy"></i></div>
            <div>
                <div class="stat-label">Resolution Rate</div>
                <div class="stat-value"><?= $stats['resolution_rate'] ?>%</div>
            </div>
        </div>
    </div>
</div>

<!-- Achievement Badges -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-award"></i> Community Achievements</div>
    <div class="card-body">
        <div class="row g-3">
            <?php
            $badges = [];
            if ($stats['total_reports'] >= 1)  $badges[] = ['icon' => 'bi-send-check', 'label' => 'First Reporter',  'color' => 'text-primary'];
            if ($stats['total_reports'] >= 5)  $badges[] = ['icon' => 'bi-people',      'label' => 'Active Citizen',  'color' => 'text-success'];
            if ($stats['resolved']      >= 1)  $badges[] = ['icon' => 'bi-patch-check', 'label' => 'Issue Resolved',  'color' => 'text-warning'];
            if ($stats['resolved']      >= 5)  $badges[] = ['icon' => 'bi-trophy',       'label' => 'Change Maker',    'color' => 'text-danger'];
            if ($stats['resolution_rate'] >= 80) $badges[] = ['icon' => 'bi-star-fill', 'label' => 'High Impact',     'color' => 'text-warning'];
            if (empty($badges)) $badges[] = ['icon' => 'bi-hourglass', 'label' => 'Getting Started', 'color' => 'text-muted'];
            ?>
            <?php foreach ($badges as $badge): ?>
                <div class="col-6 col-md-3 text-center">
                    <div class="p-3 border rounded">
                        <i class="bi <?= $badge['icon'] ?> <?= $badge['color'] ?>" style="font-size:2.5rem;"></i>
                        <div class="fw-bold mt-2 small"><?= e($badge['label']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Resolution Progress Bar -->
<div class="card">
    <div class="card-header"><i class="bi bi-bar-chart-steps"></i> Your Resolution Progress</div>
    <div class="card-body">
        <p class="text-muted small mb-2">Overall resolution rate for your submitted reports</p>
        <div class="progress" style="height:24px; border-radius:12px;">
            <div class="progress-bar bg-success" role="progressbar"
                 style="width:<?= $stats['resolution_rate'] ?>%; border-radius:12px;"
                 aria-valuenow="<?= $stats['resolution_rate'] ?>"
                 aria-valuemin="0" aria-valuemax="100">
                <?= $stats['resolution_rate'] ?>%
            </div>
        </div>
        <div class="d-flex justify-content-between mt-1 text-muted small">
            <span><?= $stats['resolved'] ?> resolved</span>
            <span><?= $stats['active'] ?> active</span>
            <span><?= $stats['rejected'] ?> rejected</span>
        </div>
    </div>
</div>
