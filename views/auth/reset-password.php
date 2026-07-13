<?php
$pageTitle = __('auth.reset_password');
$errors = $session->getFlash('errors')[0] ?? [];
?>

<div class="auth-card mx-auto" style="max-width: 420px; margin-top: 10vh;">
    <div class="auth-logo">
        <div style="width:56px;height:56px;background:rgba(26,58,107,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
            <i class="bi bi-shield-lock text-primary" style="font-size:1.8rem;"></i>
        </div>
        <h1><?= e(__('auth.reset_password')) ?></h1>
        <p>Create a new password for your account</p>
    </div>

    <form action="<?= url('reset-password') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="mb-3">
            <label class="form-label" for="password">New Password</label>
            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                   id="password" name="password" required autofocus>
            <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback"><?= e($errors['password'][0]) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-circle me-2"></i><?= e(__('auth.reset_password')) ?>
        </button>
    </form>
</div>
