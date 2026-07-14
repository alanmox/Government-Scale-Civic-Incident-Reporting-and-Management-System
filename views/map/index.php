<?php $layout = 'base'; ?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-geo-alt"></i> National Incident Map</span>
        <div class="d-flex gap-2 align-items-center" style="font-size:.8rem;">
            <span class="badge bg-danger">Critical</span>
            <span class="badge bg-warning text-dark">High</span>
            <span class="badge bg-warning text-dark">Medium</span>
            <span class="badge bg-primary">Low</span>
        </div>
    </div>
    <div id="incident-map" style="height:65vh; width:100%;"></div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-list-ul"></i> Mapped Incidents (<?= count($incidents) ?>)</div>
    <div class="table-responsive">
        <table class="table table-sm table-gcirms mb-0">
            <thead>
                <tr><th>Reference</th><th>Title</th><th>Category</th><th>Status</th><th>Priority</th></tr>
            </thead>
            <tbody>
                <?php foreach ($incidents as $row): ?>
                    <tr>
                        <td><a href="<?= url("incidents/{$row['uuid_str']}") ?>" class="fw-bold text-decoration-none"><?= e($row['reference_number']) ?></a></td>
                        <td><?= e(str_limit($row['title'], 35)) ?></td>
                        <td><?= e($row['category_name'] ?? '—') ?></td>
                        <td><?= badge_for_status($row['status']) ?></td>
                        <td><?= badge_for_status($row['priority']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($incidents)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">No geolocated incidents found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('incident-map').setView([-6.369028, 34.888822], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>'
    }).addTo(map);

    const incidents = <?= json_encode($incidents) ?>;
    const priorityColors = { critical: '#dc3545', high: '#F5A623', medium: '#F5A623', low: '#00A86B' };

    incidents.forEach(inc => {
        if (!inc.latitude || !inc.longitude) return;
        const color = priorityColors[inc.priority] || '#6c757d';
        const marker = L.circleMarker([parseFloat(inc.latitude), parseFloat(inc.longitude)], {
            radius: 8,
            fillColor: color,
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.85
        }).addTo(map);

        marker.bindPopup(`
            <strong>${inc.reference_number}</strong><br>
            ${inc.title}<br>
            <span style="text-transform:capitalize">${inc.status.replace('_', ' ')}</span>
            &nbsp;|&nbsp; ${inc.category_name || 'Unknown'}<br>
            <a href="/incidents/${inc.uuid_str}" class="text-primary" style="font-size:.8rem;">View Details →</a>
        `);
    });
});
</script>
<?php $extraJs = ob_get_clean(); ?>
