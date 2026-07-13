<?php
/**
 * Sidebar Partial — renders menu items based on session permissions
 * @var \App\Core\SessionManager $session
 */
$permissions  = $session->get('permissions', []);
$roleId       = $session->roleId();
$currentPath  = $_SERVER['REQUEST_URI'] ?? '/';

/**
 * Checks if the current user has a given permission.
 */
$can = fn(string $p): bool => in_array($p, $permissions, true);

/**
 * Returns 'active' class if the current path starts with the given prefix.
 */
$active = fn(string $path): string => str_starts_with($currentPath, $path) ? 'active' : '';
?>

<aside class="sidebar" id="sidebar">

    <!-- Main Navigation -->
    <nav>

        <!-- ── Core (All authenticated users) ── -->
        <div class="sidebar-section-label">Main</div>

        <a href="<?= url('dashboard') ?>" class="sidebar-item <?= $active('/dashboard') ?>">
            <i class="bi bi-speedometer2"></i>
            <?= e(__('nav.dashboard')) ?>
        </a>

        <?php if ($can('incident.create') || $roleId): ?>
        <a href="<?= url('incidents') ?>" class="sidebar-item <?= $active('/incidents') ?>">
            <i class="bi bi-exclamation-triangle"></i>
            <?= e(__('nav.incidents')) ?>
        </a>
        <?php endif; ?>

        <?php if ($can('incident.create')): ?>
        <a href="<?= url('incidents/my') ?>" class="sidebar-item <?= $active('/incidents/my') ?>">
            <i class="bi bi-file-text"></i>
            <?= e(__('nav.my_reports')) ?>
        </a>
        <?php endif; ?>

        <a href="<?= url('notifications') ?>" class="sidebar-item <?= $active('/notifications') ?>">
            <i class="bi bi-bell"></i>
            <?= e(__('nav.notifications')) ?>
            <?php
            $unread = $session->get('unread_notifications', 0);
            if ($unread > 0): ?>
                <span class="sidebar-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
            <?php endif; ?>
        </a>

        <!-- ── Officer Actions ── -->
        <?php if ($can('incident.verify') || $can('incident.assign')): ?>
        <div class="sidebar-section-label">Operations</div>

            <?php if ($can('incident.verify')): ?>
            <a href="<?= url('verification') ?>" class="sidebar-item <?= $active('/verification') ?>">
                <i class="bi bi-patch-check"></i>
                Verification Queue
            </a>
            <?php endif; ?>

            <?php if ($can('incident.assign')): ?>
            <a href="<?= url('assignments') ?>" class="sidebar-item <?= $active('/assignments') ?>">
                <i class="bi bi-person-check"></i>
                Assignments
            </a>
            <?php endif; ?>

            <?php if ($can('workorder.manage')): ?>
            <a href="<?= url('work-orders') ?>" class="sidebar-item <?= $active('/work-orders') ?>">
                <i class="bi bi-tools"></i>
                Work Orders
            </a>
            <?php endif; ?>

            <?php if ($can('analytics.view')): ?>
            <a href="<?= url('analytics') ?>" class="sidebar-item <?= $active('/analytics') ?>">
                <i class="bi bi-bar-chart-line"></i>
                <?= e(__('nav.analytics')) ?>
            </a>
            <?php endif; ?>

            <?php if ($can('report.generate')): ?>
            <a href="<?= url('reports') ?>" class="sidebar-item <?= $active('/reports') ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <?= e(__('nav.reports')) ?>
            </a>
            <?php endif; ?>

        <?php endif; ?>

        <!-- ── Administration ── -->
        <?php if ($can('user.manage') || $can('system.configure') || $can('role.manage')): ?>
        <div class="sidebar-section-label">Administration</div>

            <?php if ($can('user.manage')): ?>
            <a href="<?= url('admin/users') ?>" class="sidebar-item <?= $active('/admin/users') ?>">
                <i class="bi bi-people"></i>
                <?= e(__('nav.users')) ?>
            </a>
            <?php endif; ?>

            <?php if ($can('role.manage')): ?>
            <a href="<?= url('admin/roles') ?>" class="sidebar-item <?= $active('/admin/roles') ?>">
                <i class="bi bi-shield-lock"></i>
                Roles & Permissions
            </a>
            <?php endif; ?>

            <?php if ($can('system.configure')): ?>
            <a href="<?= url('admin/agencies') ?>" class="sidebar-item <?= $active('/admin/agencies') ?>">
                <i class="bi bi-building"></i>
                Agencies
            </a>
            <a href="<?= url('admin/categories') ?>" class="sidebar-item <?= $active('/admin/categories') ?>">
                <i class="bi bi-tags"></i>
                Categories
            </a>
            <a href="<?= url('admin/workflow') ?>" class="sidebar-item <?= $active('/admin/workflow') ?>">
                <i class="bi bi-diagram-3"></i>
                Workflow
            </a>
            <a href="<?= url('admin/routing') ?>" class="sidebar-item <?= $active('/admin/routing') ?>">
                <i class="bi bi-signpost-split"></i>
                Routing Rules
            </a>
            <a href="<?= url('admin/settings') ?>" class="sidebar-item <?= $active('/admin/settings') ?>">
                <i class="bi bi-sliders"></i>
                <?= e(__('nav.settings')) ?>
            </a>
            <?php endif; ?>

            <?php if ($can('audit.view')): ?>
            <a href="<?= url('admin/audit-logs') ?>" class="sidebar-item <?= $active('/admin/audit-logs') ?>">
                <i class="bi bi-journal-text"></i>
                <?= e(__('nav.audit_logs')) ?>
            </a>
            <?php endif; ?>

        <?php endif; ?>

    </nav>

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
</script>
