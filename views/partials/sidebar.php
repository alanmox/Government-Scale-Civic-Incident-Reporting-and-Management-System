<?php
/**
 * Sidebar Partial — renders menu items based on session permissions
 * @var \App\Core\SessionManager $session
 */
$permissions  = $session->get('permissions', []);
$currentPath  = $_SERVER['REQUEST_URI'] ?? '/';
$roleSlug     = $session->get('user_role', '');

/** Checks if the current user has a given permission. */
$can = fn(string $p): bool => in_array($p, $permissions, true);

/** Returns 'active' class if the current path starts with the given prefix. */
$active = fn(string $path): string => str_starts_with($currentPath, $path) ? 'active' : '';

/** Returns 'show' if any child item in the group is active (keeps group open). */
$groupOpen = function(array $paths) use ($currentPath): string {
    foreach ($paths as $path) {
        if (str_starts_with($currentPath, $path)) return 'show';
    }
    return '';
};
?>

<aside class="sidebar" id="sidebar">
    <nav>

        <!-- ── MAIN (All Users) ── -->
        <div class="sidebar-section-label">Main</div>

        <a href="<?= url('dashboard') ?>" class="sidebar-item <?= $active('/dashboard') ?>">
            <i class="bi bi-speedometer2"></i> <?= e(__('nav.dashboard')) ?>
        </a>

        <a href="<?= url('notifications') ?>" class="sidebar-item <?= $active('/notifications') ?>">
            <i class="bi bi-bell"></i>
            Notifications
            <?php $unread = $session->get('unread_notifications', 0); ?>
            <?php if ($unread > 0): ?>
                <span class="sidebar-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
            <?php endif; ?>
        </a>

        <!-- ── CITIZEN: My Reports ── -->
        <?php if ($roleSlug === 'citizen' || $can('incident.create')): ?>
        <div class="sidebar-section-label">My Reports</div>

        <a href="<?= url('incidents/create') ?>" class="sidebar-item <?= $active('/incidents/create') ?>">
            <i class="bi bi-plus-circle"></i> Report an Issue
        </a>

        <a href="<?= url('incidents/my') ?>" class="sidebar-item <?= $active('/incidents/my') ?>">
            <i class="bi bi-file-text"></i> <?= e(__('nav.my_reports')) ?>
        </a>

        <!-- NEW: Draft Reports -->
        <a href="<?= url('incidents/drafts') ?>" class="sidebar-item <?= $active('/incidents/drafts') ?>">
            <i class="bi bi-pencil-square"></i> My Drafts
        </a>

        <!-- NEW: Incident Map -->
        <a href="<?= url('map') ?>" class="sidebar-item <?= $active('/map') ?>">
            <i class="bi bi-map"></i> Incident Map
        </a>

        <!-- NEW: Bookmarks -->
        <a href="<?= url('bookmarks') ?>" class="sidebar-item <?= $active('/bookmarks') ?>">
            <i class="bi bi-bookmark-star"></i> Bookmarks
        </a>

        <!-- NEW: Updates Inbox -->
        <a href="<?= url('updates') ?>" class="sidebar-item <?= $active('/updates') ?>">
            <i class="bi bi-chat-dots"></i> Updates Inbox
        </a>

        <!-- NEW: My Impact -->
        <a href="<?= url('my-impact') ?>" class="sidebar-item <?= $active('/my-impact') ?>">
            <i class="bi bi-trophy"></i> My Impact
        </a>

        <!-- NEW: Notification Settings -->
        <a href="<?= url('notification-settings') ?>" class="sidebar-item <?= $active('/notification-settings') ?>">
            <i class="bi bi-sliders"></i> Alert Settings
        </a>
        <?php endif; ?>

        <!-- ── OPERATIONS (Officers/Supervisors) ── -->
        <?php if ($can('incident.verify') || $can('incident.assign') || $can('workorder.manage')): ?>
        <div class="sidebar-section-label">Operations</div>

            <?php if ($can('incident.verify')): ?>
            <a href="<?= url('verification') ?>" class="sidebar-item <?= $active('/verification') ?>">
                <i class="bi bi-patch-check"></i> Verification Queue
            </a>
            <?php endif; ?>

            <?php if ($can('incident.assign')): ?>
            <a href="<?= url('assignments') ?>" class="sidebar-item <?= $active('/assignments') ?>">
                <i class="bi bi-person-check"></i> Assignments
            </a>
            <?php endif; ?>

            <?php if ($can('workorder.manage')): ?>
            <a href="<?= url('work-orders') ?>" class="sidebar-item <?= $active('/work-orders') ?>">
                <i class="bi bi-tools"></i> Work Orders
            </a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ── TRACKING & ANALYTICS ── -->
        <?php if ($can('analytics.view') || $can('report.generate') || $can('incident.assign')): ?>
        <div class="sidebar-section-label">Tracking & Analytics</div>

            <?php if ($can('analytics.view')): ?>
            <a href="<?= url('analytics') ?>" class="sidebar-item <?= $active('/analytics') ?>">
                <i class="bi bi-bar-chart-line"></i> Incident Trends
            </a>

            <a href="<?= url('analytics/sla-compliance') ?>" class="sidebar-item <?= $active('/analytics/sla-compliance') ?>">
                <i class="bi bi-clock-check"></i> SLA Compliance
            </a>

            <a href="<?= url('analytics/response-times') ?>" class="sidebar-item <?= $active('/analytics/response-times') ?>">
                <i class="bi bi-stopwatch"></i> Response Times
            </a>

            <a href="<?= url('map') ?>" class="sidebar-item <?= $active('/map') ?>">
                <i class="bi bi-geo-alt"></i> Geo Analytics
            </a>

            <a href="<?= url('analytics/performance') ?>" class="sidebar-item <?= $active('/analytics/performance') ?>">
                <i class="bi bi-graph-up-arrow"></i> Performance Metrics
            </a>
            <?php endif; ?>

            <?php if ($can('incident.assign') || $can('analytics.view')): ?>
            <a href="<?= url('escalations') ?>" class="sidebar-item <?= $active('/escalations') ?>">
                <i class="bi bi-exclamation-octagon"></i> Escalation Tracker
            </a>

            <a href="<?= url('analytics/workload') ?>" class="sidebar-item <?= $active('/analytics/workload') ?>">
                <i class="bi bi-people"></i> Team Workload
            </a>
            <?php endif; ?>

            <?php if ($can('report.generate')): ?>
            <a href="<?= url('reports') ?>" class="sidebar-item <?= $active('/reports') ?>">
                <i class="bi bi-file-earmark-bar-graph"></i> Report Builder
            </a>
            <a href="<?= url('reports/export-incidents') ?>" class="sidebar-item <?= $active('/reports/export-incidents') ?>">
                <i class="bi bi-download"></i> Export Data
            </a>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ── ADMINISTRATION ── -->
        <?php if ($can('user.manage') || $can('system.configure') || $can('role.manage') || $can('audit.view')): ?>
        <div class="sidebar-section-label">Administration</div>

            <?php if ($can('user.manage')): ?>
            <a href="<?= url('admin/users') ?>" class="sidebar-item <?= $active('/admin/users') ?>">
                <i class="bi bi-people"></i> User Management
            </a>
            <a href="<?= url('admin/users/activity') ?>" class="sidebar-item <?= $active('/admin/users/activity') ?>">
                <i class="bi bi-activity"></i> User Activity Log
            </a>
            <?php endif; ?>

            <?php if ($can('role.manage')): ?>
            <a href="<?= url('admin/roles') ?>" class="sidebar-item <?= $active('/admin/roles') ?>">
                <i class="bi bi-shield-lock"></i> Roles & Permissions
            </a>
            <?php endif; ?>

            <?php if ($can('system.configure')): ?>
            <a href="<?= url('admin/agencies') ?>" class="sidebar-item <?= $active('/admin/agencies') ?>">
                <i class="bi bi-building"></i> Agencies & Departments
            </a>
            <a href="<?= url('admin/categories') ?>" class="sidebar-item <?= $active('/admin/categories') ?>">
                <i class="bi bi-tags"></i> Incident Categories
            </a>

            <a href="<?= url('admin/sla') ?>" class="sidebar-item <?= $active('/admin/sla') ?>">
                <i class="bi bi-clock-history"></i> SLA Configuration
            </a>

            <a href="<?= url('admin/workflow') ?>" class="sidebar-item <?= $active('/admin/workflow') ?>">
                <i class="bi bi-diagram-3"></i> Workflow Engine
            </a>
            <a href="<?= url('admin/routing') ?>" class="sidebar-item <?= $active('/admin/routing') ?>">
                <i class="bi bi-signpost-split"></i> Routing Rules
            </a>

            <a href="<?= url('admin/regions') ?>" class="sidebar-item <?= $active('/admin/regions') ?>">
                <i class="bi bi-map"></i> Regions & Boundaries
            </a>

            <a href="<?= url('admin/integrations') ?>" class="sidebar-item <?= $active('/admin/integrations') ?>">
                <i class="bi bi-plug"></i> API Integrations
            </a>

            <a href="<?= url('admin/settings') ?>" class="sidebar-item <?= $active('/admin/settings') ?>">
                <i class="bi bi-sliders"></i> System Settings
            </a>

            <a href="<?= url('admin/backup') ?>" class="sidebar-item <?= $active('/admin/backup') ?>">
                <i class="bi bi-cloud-arrow-down"></i> Backup & Restore
            </a>
            <?php endif; ?>

            <?php if ($can('audit.view')): ?>
            <a href="<?= url('admin/audit-logs') ?>" class="sidebar-item <?= $active('/admin/audit-logs') ?>">
                <i class="bi bi-journal-text"></i> Audit Trail
            </a>
            <a href="<?= url('admin/system-health') ?>" class="sidebar-item <?= $active('/admin/system-health') ?>">
                <i class="bi bi-heart-pulse"></i> System Health
            </a>
            <?php endif; ?>

        <?php endif; ?>

    </nav>

    <!-- Keyboard Shortcut Hint -->
    <div class="sidebar-footer" style="padding:.75rem 1rem;margin-top:auto;border-top:1px solid rgba(255,255,255,.08);font-size:.72rem;color:rgba(255,255,255,.4);">
        Press <kbd style="background:rgba(255,255,255,.15);color:#fff;padding:1px 5px;border-radius:3px;">Shift+?</kbd> for shortcuts
    </div>
</aside>

<script>
// Mobile sidebar toggle
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});
// Close sidebar on outside click (mobile)
document.addEventListener('click', (e) => {
    const sidebar = document.getElementById('sidebar');
    const toggle  = document.getElementById('sidebar-toggle');
    if (sidebar?.classList.contains('open') && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.shiftKey && e.key === '?') {
        const m = document.getElementById('keyboard-shortcuts-modal');
        if (m) bootstrap.Modal.getOrCreateInstance(m).show();
    }
    if (e.key === 'n' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
        window.location.href = '<?= url('incidents/create') ?>';
    }
});

// AJAX Notification polling every 60 seconds
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
    .catch(() => {}); // Silent fail — never break the UI for a notification error
}
setInterval(pollNotifications, 60000);
</script>

<!-- Keyboard Shortcuts Modal -->
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
                        <tr><td><kbd>Shift+?</kbd></td><td>Show this help dialog</td></tr>
                        <tr><td><kbd>Esc</kbd></td><td>Close any modal</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
