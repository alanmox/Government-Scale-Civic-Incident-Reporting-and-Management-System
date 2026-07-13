<?php
$pageTitle = __('auth.login');
$old = $session->getFlash('old')[0] ?? [];
?>

<div class="auth-card mx-auto" style="max-width: 420px; margin-top: 10vh;">
    <div class="auth-logo">
        <div style="width:56px;height:56px;background:rgba(26,58,107,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
            <i class="bi bi-shield-lock text-primary" style="font-size:1.8rem;"></i>
        </div>
        <h1><?= e(__('app_name')) ?></h1>
        <p><?= e(__('auth.login')) ?> to your account</p>
    </div>

    <form action="<?= url('login') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label" for="identifier"><?= e(__('auth.email')) ?> or Username</label>
            <input type="text" class="form-control" id="identifier" name="identifier" 
                   value="<?= e($old['identifier'] ?? '') ?>" required autofocus>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0" for="password"><?= e(__('auth.password')) ?></label>
                <a href="<?= url('forgot-password') ?>" tabindex="-1" style="font-size:.8rem;text-decoration:none;"><?= e(__('auth.forgot_password')) ?></a>
            </div>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-box-arrow-in-right me-2"></i><?= e(__('auth.login')) ?>
        </button>

        <div class="text-center text-muted" style="font-size:.85rem;">
            Don't have an account? <a href="<?= url('register') ?>" class="fw-600" style="text-decoration:none;"><?= e(__('auth.register')) ?></a>
        </div>
    </form>
</div>
