<?php $layout = 'auth'; $pageTitle = __('error.500'); ?>
<div class="auth-card text-center" style="max-width:480px;">
    <div style="font-size:4rem;color:var(--warning);margin-bottom:1rem;"><i class="bi bi-exclamation-triangle"></i></div>
    <h1 style="font-size:1.4rem;font-weight:700;"><?= e(__('error.500')) ?></h1>
    <p class="text-muted mb-4"><?= e(__('error.500_message')) ?></p>
    <a href="<?= url('dashboard') ?>" class="btn btn-primary"><i class="bi bi-house me-2"></i>Back to Dashboard</a>
</div>
