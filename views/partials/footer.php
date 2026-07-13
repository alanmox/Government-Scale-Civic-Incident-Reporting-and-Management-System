<footer class="border-top" style="margin-left:var(--sidebar-width);padding:.85rem 1.75rem;background:#fff;font-size:.78rem;color:var(--text-muted);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span>&copy; <?= date('Y') ?> <?= e(__('app_name')) ?> — Government Civic Incident Reporting & Management System</span>
        <span>Version <?= e(config('app.version', '1.0.0')) ?> &nbsp;|&nbsp; <a href="<?= url('help') ?>" class="text-muted">Help</a></span>
    </div>
</footer>
