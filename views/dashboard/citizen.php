<?php $layout = 'base'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3 text-black">Welcome back, <?= e($session->get('user_name')) ?></h4>
        <p class="text-muted">Here is an overview of your civic incident reports.</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon info"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div class="stat-label">Total Reports</div>
                <div class="stat-value"><?= $stats['total'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon warning"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="stat-label">Pending Review</div>
                <div class="stat-value"><?= $stats['pending'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="bi bi-tools"></i></div>
            <div>
                <div class="stat-label">In Progress</div>
                <div class="stat-value"><?= $stats['in_progress'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon success"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="stat-label">Resolved</div>
                <div class="stat-value"><?= $stats['resolved'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-bar-chart"></i> Your Activity (Last 6 Months)</div>
            <div class="card-body">
                <canvas id="citizenChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 bg-primary text-white text-center">
            <div class="card-body d-flex flex-column justify-content-center">
                <i class="bi bi-megaphone mb-3" style="font-size: 3rem;"></i>
                <h5>Report a New Issue</h5>
                <p class="small text-white-50">Help keep your community safe and clean.</p>
                <a href="<?= url('incidents/create') ?>" class="btn btn-light text-primary mt-3">Start Report</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list-task"></i> Your Recent Reports</span>
        <a href="<?= url('incidents/my') ?>" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-gcirms mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($stats['recent'])): ?>
                    <tr><td colspan="4" class="text-center py-3 text-muted">No recent reports found.</td></tr>
                <?php else: ?>
                    <?php foreach ($stats['recent'] as $incData): 
                        $incident = new \App\Models\Incident($incData);
                    ?>
                        <tr>
                            <td><a href="<?= url("incidents/{$incident->getUuid()}") ?>" class="fw-600 text-decoration-none"><?= e($incident->getReferenceNumber()) ?></a></td>
                            <td><?= e($incident->getCategoryName()) ?></td>
                            <td><span class="<?= $incident->getStatusBadgeClass() ?>"><?= e(ucwords(str_replace('_', ' ', $incident->getStatus()))) ?></span></td>
                            <td><?= e(date('M d, Y', strtotime($incident->getCreatedAt()))) ?></td>
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
    const ctx = document.getElementById('citizenChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Reports Submitted',
                data: [1, 0, 2, 0, 1, <?= $stats['total'] ?>],
                backgroundColor: 'rgba(26, 58, 107, 0.7)',
                borderColor: 'rgba(26, 58, 107, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>
<?php $extraJs = ob_get_clean(); ?>
