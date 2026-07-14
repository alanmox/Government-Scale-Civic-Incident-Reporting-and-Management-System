<?php
/**
 * Sidebar Partial — renders menu items based on session permissions
 * @var \App\Core\SessionManager $session
 */
$permissions  = $session->get('permissions', []);
$currentPath  = $_SERVER['REQUEST_URI'] ?? '/';
$roleSlug     = $session->get('user_role', '');
$isAdmin      = $roleSlug === 'super_admin' || in_array('*', $permissions, true);
$isCitizen    = $roleSlug === 'citizen';

$can = fn(string $p): bool => in_array('*', $permissions, true) || in_array($p, $permissions, true);

$active = fn(string $path): string => str_starts_with($currentPath, $path) ? 'active' : '';
?>

<aside class="sidebar" id="sidebar">
    <nav>

        <!-- ── DASHBOARD (Everyone) ── -->
        <div class="sidebar-section-label">Overview</div>

        <a href="<?= url('dashboard') ?>" class="sidebar-item <?= $active('/dashboard') ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="<?= url('notifications') ?>" class="sidebar-item <?= $active('/notifications') ?>">
            <i class="bi bi-bell"></i> Notifications
            <?php $unread = $session->get('unread_notifications', 0); ?>
            <?php if ($unread > 0): ?>
                <span class="sidebar-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
            <?php endif; ?>
        </a>

        <!-- ── CITIZEN SECTION ── -->
        <?php if ($isCitizen || $can('incident.create')): ?>
        <div class="sidebar-section-label">My Reports</div>

        <a href="<?= url('incidents/create') ?>" class="sidebar-item <?= $active('/incidents/create') ?>">
            <i class="bi bi-plus-circle"></i> Report an Issue
        </a>

        <a href="<?= url('incidents/my') ?>" class="sidebar-item <?= $active('/incidents/my') ?>">
            <i class="bi bi-file-text"></i> My Reports
        </a>
        <?php endif; ?>

        <!-- ── ADMIN SECTION ── -->
        <?php if ($isAdmin): ?>
        <div class="sidebar-section-label">Management</div>

        <a href="<?= url('incidents') ?>" class="sidebar-item <?= $active('/incidents') ?>">
            <i class="bi bi-exclamation-triangle"></i> All Incidents
        </a>

        <a href="<?= url('incidents/create') ?>" class="sidebar-item <?= $active('/incidents/create') ?>">
            <i class="bi bi-plus-circle"></i> Create Incident
        </a>

        <a href="<?= url('verification') ?>" class="sidebar-item <?= $active('/verification') ?>">
            <i class="bi bi-patch-check"></i> Verify Reports
        </a>

        <a href="<?= url('assignments') ?>" class="sidebar-item <?= $active('/assignments') ?>">
            <i class="bi bi-person-check"></i> Assign Reports
        </a>

        <a href="<?= url('work-orders') ?>" class="sidebar-item <?= $active('/work-orders') ?>">
            <i class="bi bi-tools"></i> Work Orders
        </a>

        <div class="sidebar-section-label">Tracking & Analytics</div>

        <a href="<?= url('analytics') ?>" class="sidebar-item <?= $active('/analytics') ?>">
            <i class="bi bi-bar-chart-line"></i> Incident Trends
        </a>

        <a href="<?= url('analytics/sla-compliance') ?>" class="sidebar-item <?= $active('/analytics/sla-compliance') ?>">
            <i class="bi bi-clock-check"></i> SLA Compliance
        </a>

        <a href="<?= url('analytics/response-times') ?>" class="sidebar-item <?= $active('/analytics/response-times') ?>">
            <i class="bi bi-stopwatch"></i> Response Times
        </a>

        <a href="<?= url('analytics/performance') ?>" class="sidebar-item <?= $active('/analytics/performance') ?>">
            <i class="bi bi-graph-up-arrow"></i> Performance Metrics
        </a>

        <a href="<?= url('map') ?>" class="sidebar-item <?= $active('/map') ?>">
            <i class="bi bi-geo-alt"></i> Incident Map
        </a>

        <a href="<?= url('escalations') ?>" class="sidebar-item <?= $active('/escalations') ?>">
            <i class="bi bi-exclamation-octagon"></i> Escalations
        </a>

        <div class="sidebar-section-label">Administration</div>

        <a href="<?= url('admin/users') ?>" class="sidebar-item <?= $active('/admin/users') ?>">
            <i class="bi bi-people"></i> Manage Users
        </a>

        <a href="<?= url('admin/roles') ?>" class="sidebar-item <?= $active('/admin/roles') ?>">
            <i class="bi bi-shield-lock"></i> Roles & Permissions
        </a>

        <a href="<?= url('admin/categories') ?>" class="sidebar-item <?= $active('/admin/categories') ?>">
            <i class="bi bi-tags"></i> Incident Categories
        </a>

        <a href="<?= url('admin/sla') ?>" class="sidebar-item <?= $active('/admin/sla') ?>">
            <i class="bi bi-clock-history"></i> SLA Rules
        </a>

        <a href="<?= url('admin/workflow') ?>" class="sidebar-item <?= $active('/admin/workflow') ?>">
            <i class="bi bi-diagram-3"></i> Workflow
        </a>

        <a href="<?= url('admin/settings') ?>" class="sidebar-item <?= $active('/admin/settings') ?>">
            <i class="bi bi-sliders"></i> System Settings
        </a>

        <a href="<?= url('admin/audit-logs') ?>" class="sidebar-item <?= $active('/admin/audit-logs') ?>">
            <i class="bi bi-journal-text"></i> Audit Logs
        </a>

        <a href="<?= url('reports') ?>" class="sidebar-item <?= $active('/reports') ?>">
            <i class="bi bi-file-earmark-bar-graph"></i> Reports & Export
        </a>
        <?php endif; ?>

    </nav>

    <div class="sidebar-footer" style="padding:.75rem 1rem;margin-top:auto;border-top:1px solid rgba(255,255,255,.08);font-size:.72rem;color:rgba(255,255,255,.4);">
        Press <kbd style="background:rgba(255,255,255,.15);color:#fff;padding:1px 5px;border-radius:3px;">Shift+?</kbd> for shortcuts
    </div>
</aside>

<script>
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});
document.addEventListener('click', (e) => {
    const sidebar = document.getElementById('sidebar');
    const toggle  = document.getElementById('sidebar-toggle');
    if (sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});
document.addEventListener('keydown', (e) => {
    if (e.shiftKey && e.key === '?') {
        const m = document.getElementById('keyboard-shortcuts-modal');
        if (m) bootstrap.Modal.getOrCreateInstance(m).show();
    }
    if (e.key === 'n' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
        window.location.href = '<?= url('incidents/create') ?>';
    }
});
function pollNotifications() {
    fetch('<?= url('api/v1/notifications/unread-count') ?>', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notif-badge');
        if (badge) {
            if (data?.data?.count > 0) {
                badge.classList.remove('d-none');
                badge.classList.add('badge-dot');
            } else {
                badge.classList.add('d-none');
            }
        }
    })
    .catch(() => {});
}
setInterval(pollNotifications, 60000);
</script>

<div class="modal fade" id="keyboard-shortcuts-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-keyboard me-2"></i> Keyboard Shortcuts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Key</th><th>Action</th></tr></thead>
                    <tbody>
                        <tr><td><kbd>N</kbd></td><td>New Incident Report</td></tr>
                        <tr><td><kbd>Shift+?</kbd></td><td>Show keyboard shortcuts</td></tr>
                        <tr><td><kbd>Esc</kbd></td><td>Close any modal</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
