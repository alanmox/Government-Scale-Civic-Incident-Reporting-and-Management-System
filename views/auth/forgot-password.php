<?php
$pageTitle = __('auth.forgot_password');
?>

<div class="auth-card mx-auto" style="max-width: 420px; margin-top: 10vh;">
    <div class="auth-logo">
        <div style="width:56px;height:56px;background:rgba(26,58,107,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
            <i class="bi bi-key text-primary" style="font-size:1.8rem;"></i>
        </div>
        <h1><?= e(__('auth.forgot_password')) ?></h1>
        <p>Enter your email to receive a reset link</p>
    </div>

    <form action="<?= url('forgot-password') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-4">
            <label class="form-label" for="email"><?= e(__('auth.email')) ?></label>
            <input type="email" class="form-control" id="email" name="email" required autofocus>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-envelope-paper me-2"></i>Send Reset Link
        </button>

        <div class="text-center">
            <a href="<?= url('login') ?>" class="text-muted" style="font-size:.85rem;text-decoration:none;">
                <i class="bi bi-arrow-left me-1"></i>Back to login
            </a>
        </div>
    </form>
</div>
