<?php
/**
 * Top Navigation Partial
 * @var \App\Core\SessionManager $session
 */
$userId = $session->userId();
$userInfo = $session->get('user_info', []);
$unreadNotifications = $session->get('unread_notifications', 0);
?>
<nav class="topnav" id="topnav">

    <!-- Sidebar Toggle (mobile) -->
    <button class="topnav-btn me-2 d-lg-none" id="sidebar-toggle" aria-label="Toggle menu">
        <i class="bi bi-list"></i>
    </button>

    <!-- Brand -->
    <a href="<?= url('dashboard') ?>" class="topnav-brand">
        <div style="width:32px;height:32px;background:rgba(255,255,255,.15);border-radius:6px;display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-shield-check" style="font-size:1.1rem;color:#fff;"></i>
        </div>
        <span class="d-none d-md-inline"><?= e(__('app_name')) ?></span>
    </a>

    <!-- Global Search -->
    <div class="topnav-search d-none d-md-block">
        <form action="<?= url('incidents') ?>" method="GET">
            <?= csrf_field() ?>
            <input type="search" name="q"
                   placeholder="<?= e(__('search')) ?> incidents, reports..."
                   value="<?= e($_GET['q'] ?? '') ?>"
                   autocomplete="off">
        </form>
    </div>

    <!-- Actions -->
    <div class="topnav-actions">

        <!-- Accessibility / High Contrast Toggle -->
        <button class="topnav-btn" id="btn-high-contrast" aria-label="Toggle High Contrast" title="Toggle High Contrast Mode">
            <i class="bi bi-circle-half"></i>
        </button>

        <!-- Language Toggle -->
        <div class="dropdown">
            <button class="topnav-btn" data-bs-toggle="dropdown" aria-label="Language" title="Switch Language">
                <i class="bi bi-translate"></i>
                <span class="d-none d-md-inline" style="font-size:.75rem;font-weight:600;"><?= strtoupper($_SESSION['locale'] ?? 'EN') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:140px;font-size:.85rem;">
                <li><a class="dropdown-item <?= ($_SESSION['locale'] ?? 'en') === 'en' ? 'active' : '' ?>" href="<?= url('locale/en') ?>">
                    🇬🇧 &nbsp;English
                </a></li>
                <li><a class="dropdown-item <?= ($_SESSION['locale'] ?? 'en') === 'sw' ? 'active' : '' ?>" href="<?= url('locale/sw') ?>">
                    🇹🇿 &nbsp;Kiswahili
                </a></li>
            </ul>
        </div>

        <!-- Notifications -->
        <div class="dropdown">
            <button class="topnav-btn" data-bs-toggle="dropdown" aria-label="Notifications" id="notif-btn">
                <i class="bi bi-bell"></i>
                <span id="notif-badge" class="<?= $unreadNotifications > 0 ? 'badge-dot' : 'd-none' ?>"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0 shadow-lg">
                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                    <strong style="font-size:.85rem;"><?= e(__('nav.notifications')) ?></strong>
                    <?php if ($unreadNotifications > 0): ?>
                        <a href="<?= url('notifications/mark-all-read') ?>" class="text-primary" style="font-size:.75rem;">Mark all read</a>
                    <?php endif; ?>
                </div>
                <div id="notification-list">
                    <div class="text-center text-muted py-4" style="font-size:.82rem;">
                        <i class="bi bi-bell-slash d-block mb-2" style="font-size:1.5rem;"></i>
                        No new notifications
                    </div>
                </div>
                <div class="text-center border-top p-2">
                    <a href="<?= url('notifications') ?>" class="text-primary" style="font-size:.8rem;">View all notifications</a>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <?php if ($userId):
            $name = $userInfo['name'] ?? 'User';
            $parts = explode(' ', $name);
            $initials = strtoupper(($parts[0][0] ?? 'U') . (isset($parts[1]) ? $parts[1][0] : ''));
        ?>
        <div class="dropdown">
            <button class="d-flex align-items-center gap-2 bg-transparent border-0 text-white ps-1"
                    data-bs-toggle="dropdown">
                <div class="topnav-avatar" style="background:var(--accent-light);color:#fff;font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,.3);"><?= e($initials) ?></div>
                <span class="d-none d-lg-inline" style="font-size:.82rem;font-weight:500;">
                    <?= e($name) ?>
                </span>
                <i class="bi bi-chevron-down d-none d-lg-inline" style="font-size:.7rem;opacity:.7;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:200px;font-size:.85rem;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-600"><?= e($userInfo['name'] ?? 'User') ?></div>
                    <div class="text-muted" style="font-size:.75rem;"><?= e($userInfo['role'] ?? '') ?></div>
                </li>
                <li><a class="dropdown-item" href="<?= url('profile') ?>">
                    <i class="bi bi-person me-2"></i><?= e(__('nav.profile')) ?></a></li>
                <li><a class="dropdown-item" href="<?= url('profile/settings') ?>">
                    <i class="bi bi-gear me-2"></i><?= e(__('nav.settings')) ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="<?= url('logout') ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i><?= e(__('nav.logout')) ?>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        <?php endif; ?>

    </div>
</nav>
