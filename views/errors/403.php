<?php $layout = 'auth'; $pageTitle = __('error.403'); ?>
<div class="auth-card text-center" style="max-width:480px;">
    <div style="font-size:4rem;color:var(--danger);margin-bottom:1rem;"><i class="bi bi-shield-x"></i></div>
    <h1 style="font-size:1.4rem;font-weight:700;color:var(--danger);"><?= e(__('error.403')) ?></h1>
    <p class="text-muted mb-4"><?= e(__('error.403_message')) ?></p>
    <a href="javascript:history.back()" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left me-1"></i>Go Back</a>
    <a href="<?= url('dashboard') ?>" class="btn btn-primary"><i class="bi bi-house me-2"></i>Dashboard</a>
</div>
