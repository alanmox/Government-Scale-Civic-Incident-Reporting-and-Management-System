<?php
$pageTitle = __('auth.register');
$old = $session->getFlash('old')[0] ?? [];
$errors = $session->getFlash('errors')[0] ?? [];
?>

<div class="auth-card mx-auto" style="max-width: 500px; margin-top: 5vh; margin-bottom: 5vh;">
    <div class="auth-logo">
        <div style="width:56px;height:56px;background:rgba(26,58,107,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
            <i class="bi bi-person-plus text-primary" style="font-size:1.8rem;"></i>
        </div>
        <h1><?= e(__('app_name')) ?></h1>
        <p>Create a Citizen Account</p>
    </div>

    <form action="<?= url('register') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
            <input type="text" class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                   id="full_name" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" required>
            <?php if (isset($errors['full_name'])): ?>
                <div class="invalid-feedback"><?= e($errors['full_name'][0]) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label" for="email"><?= e(__('auth.email')) ?> <span class="required">*</span></label>
            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                   id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?= e($errors['email'][0]) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label" for="phone">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?= e($old['phone'] ?? '') ?>">
            <div class="form-text" style="font-size: .75rem;">Optional. Used for SMS updates.</div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="password"><?= e(__('auth.password')) ?> <span class="required">*</span></label>
                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                       id="password" name="password" required>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= e($errors['password'][0]) ?></div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6 mb-4">
                <label class="form-label" for="password_confirmation"><?= e(__('auth.confirm_password')) ?> <span class="required">*</span></label>
                <input type="password" class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" 
                       id="password_confirmation" name="password_confirmation" required>
                <?php if (isset($errors['password_confirmation'])): ?>
                    <div class="invalid-feedback"><?= e($errors['password_confirmation'][0]) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-person-check me-2"></i><?= e(__('auth.register')) ?>
        </button>

        <div class="text-center text-muted" style="font-size:.85rem;">
            Already have an account? <a href="<?= url('login') ?>" class="fw-600" style="text-decoration:none;">Log In</a>
        </div>
    </form>
</div>
