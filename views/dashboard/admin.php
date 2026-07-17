<?php $layout = 'base'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3 text-primary">System Administration</h4>
        <p class="text-muted">National overview of all civic incidents.</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div class="stat-label">Total System Incidents</div>
                <div class="stat-value"><?= $stats['total_incidents'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="stat-label">Total Resolved</div>
                <div class="stat-value"><?= $stats['resolved'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-label">Registered Users</div>
                <div class="stat-value"><?= $stats['total_users'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="stat-label">System Health</div>
                <div class="stat-value text-success">Good</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart"></i> Incident Status Breakdown</div>
            <div class="card-body d-flex justify-content-center">
                <div style="width: 100%; max-width: 300px;">
                    <canvas id="adminPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-gear"></i> Administration Links</div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= url('reports/export-incidents') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-primary fw-bold">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i> Export All Incidents (CSV)
                        <i class="bi bi-download"></i>
                    </a>
                    <a href="<?= url('admin/users') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center mt-2">
                        User Management
                    </a>
                    <a href="<?= url('admin/roles') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Role & Permissions
                    </a>
                    <a href="<?= url('admin/settings') ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        System Configuration
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pie Chart
    const ctx = document.getElementById('adminPieChart').getContext('2d');
    
    // Prepare data safely
    const rawData = <?= json_encode($stats['status_breakdown']) ?>;
    const labels = Object.keys(rawData).map(k => k.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()));
    const data = Object.values(rawData);
    
    // Fallback if no data
    if (data.length === 0) {
        labels.push('No Data');
        data.push(1);
    }

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#00A86B', // primary
                    '#ffc107', // warning
                    '#28a745', // success
                    '#dc3545', // danger
                    '#6c757d', // secondary
                    '#66BB6A'  // accent
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
});
</script>
<?php $extraJs = ob_get_clean(); ?>
