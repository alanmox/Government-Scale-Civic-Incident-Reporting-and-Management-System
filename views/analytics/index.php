<?php $layout = 'base'; ?>

<!-- KPI Tiles -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon danger"><i class="bi bi-exclamation-octagon"></i></div>
            <div>
                <div class="stat-label">SLA Breaches</div>
                <div class="stat-value text-danger"><?= $slaBreaches ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-bar-chart"></i></div>
            <div>
                <div class="stat-label">Categories Tracked</div>
                <div class="stat-value"><?= count($byCategory) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-building"></i></div>
            <div>
                <div class="stat-label">Agencies Rated</div>
                <div class="stat-value"><?= count($agencyPerf) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="stat-label">30-Day Trend Points</div>
                <div class="stat-value"><?= count($trend) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- 30-Day Trend Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-graph-up"></i> Incident Volume — Last 30 Days</div>
            <div class="card-body"><canvas id="trendChart" height="100"></canvas></div>
        </div>
    </div>
    <!-- Category Donut -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart"></i> By Category</div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="categoryChart" style="max-height:260px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Agency Performance -->
    <div class="col-lg-7 mb-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-trophy"></i> Agency Performance (Avg. Resolution Hours)</div>
            <div class="table-responsive">
                <table class="table table-hover table-gcirms mb-0">
                    <thead><tr><th>#</th><th>Agency</th><th>Avg Hours</th><th>Total Resolved</th></tr></thead>
                    <tbody>
                        <?php if (empty($agencyPerf)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No resolution data yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($agencyPerf as $i => $row): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= e($row['agency']) ?></td>
                                    <td>
                                        <span class="fw-bold <?= (float)$row['avg_hours'] > 72 ? 'text-danger' : 'text-success' ?>">
                                            <?= e($row['avg_hours']) ?>h
                                        </span>
                                    </td>
                                    <td><?= e($row['total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="col-lg-5 mb-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-list-check"></i> Status Breakdown</div>
            <div class="card-body">
                <?php foreach ($byStatus as $row): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?= badge_for_status($row['status']) ?></span>
                        <span class="fw-bold"><?= num_format($row['total']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendData = <?= json_encode($trend) ?>;
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(r => r.day),
            datasets: [{
                label: 'New Incidents',
                data: trendData.map(r => r.count),
                borderColor: '#1B4D3E',
                backgroundColor: 'rgba(27,77,62,.1)',
                fill: true, tension: 0.4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Category Donut
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const catData = <?= json_encode($byCategory) ?>;
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catData.map(r => r.name),
            datasets: [{ data: catData.map(r => r.total),
                backgroundColor: ['#1B4D3E','#28a745','#F5A623','#dc3545','#17a2b8','#6c757d','#ffc107','#6f42c1','#20c997','#e83e8c']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
    });
});
</script>
<?php $extraJs = ob_get_clean(); ?>
