<?php
/**
 * Breadcrumbs Partial
 *
 * Usage in any view:
 *   $breadcrumbs = [
 *       ['label' => 'Dashboard', 'url' => url('dashboard')],
 *       ['label' => 'Incidents',  'url' => url('incidents')],
 *       ['label' => 'INC-2026-0001'], // last item — no url
 *   ];
 */
if (!isset($breadcrumbs) || empty($breadcrumbs)) {
    return;
}
?>
<nav aria-label="breadcrumb" class="breadcrumb-nav">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="<?= url('dashboard') ?>"><i class="bi bi-house-door"></i></a>
        </li>
        <?php foreach ($breadcrumbs as $crumb): ?>
            <?php if (isset($crumb['url'])): ?>
                <li class="breadcrumb-item">
                    <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= e($crumb['label']) ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
