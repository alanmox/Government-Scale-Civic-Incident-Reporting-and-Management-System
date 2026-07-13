<?php $layout = 'base'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3 text-primary">Officer Workspace</h4>
        <p class="text-muted">Manage your assigned work orders and pending SLAs.</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-briefcase"></i></div>
            <div>
                <div class="stat-label">Total Assigned WOs</div>
                <div class="stat-value"><?= $stats['total_wo'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-tools"></i></div>
            <div>
                <div class="stat-label">Active Work Orders</div>
                <div class="stat-value"><?= $stats['active_wo'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <div class="stat-label">SLA Breaches</div>
                <div class="stat-value text-danger"><?= $stats['sla_breaches'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-check2-all"></i></div>
            <div>
                <div class="stat-label">Completed WOs</div>
                <div class="stat-value"><?= $stats['completed_wo'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up"></i> Work Order Completion Trend</div>
            <div class="card-body">
                <canvas id="officerChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Map Placeholder -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-geo-alt"></i> Active WOs Location</div>
            <div class="card-body p-0">
                <div id="dashboard-map" style="height: 100%; min-height: 250px; border-bottom-left-radius: .375rem; border-bottom-right-radius: .375rem;"></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history"></i> Recent Work Orders</span>
        <a href="<?= url('work-orders') ?>" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>WO Reference</th>
                    <th>Incident</th>
                    <th>Status</th>
                    <th>Assigned</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stats['recent_wo'])): ?>
                    <tr><td colspan="4" class="text-center py-3 text-muted">No active work orders.</td></tr>
                <?php else: ?>
                    <?php foreach ($stats['recent_wo'] as $woData): 
                        $wo = new \App\Models\WorkOrder($woData);
                    ?>
                        <tr>
                            <td><a href="<?= url("work-orders/{$wo->getUuid()}") ?>" class="fw-600 text-decoration-none"><?= e($wo->getReferenceNumber()) ?></a></td>
                            <td><span class="text-muted small"><?= e($woData['incident_ref']) ?></span></td>
                            <td><span class="<?= $wo->getStatusBadgeClass() ?>"><?= e(ucwords(str_replace('_', ' ', $wo->getStatus()))) ?></span></td>
                            <td><?= e(date('M d', strtotime($wo->getCreatedAt()))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart
    const ctx = document.getElementById('officerChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Completed WOs',
                data: [0, 2, 1, <?= $stats['completed_wo'] ?>],
                borderColor: 'rgba(40, 167, 69, 1)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Map - Centered on Tanzania
    const map = L.map('dashboard-map').setView([-6.369028, 34.888822], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);
});
</script>
<?php $extraJs = ob_get_clean(); ?>
