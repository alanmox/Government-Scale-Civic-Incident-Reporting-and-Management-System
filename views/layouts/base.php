<!DOCTYPE html>
<html lang="<?= e(config('app.locale', 'en')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="description" content="<?= e(__('app_tagline')) ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= e(__('app_name')) ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <!-- App CSS -->
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">

    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>

<!-- Top Navigation -->
<?php require VIEWS_PATH . '/partials/topnav.php'; ?>

<div class="app-wrapper">
    <!-- Main Content (left) -->
    <main class="main-content" id="main-content">

        <!-- Breadcrumbs -->
        <?php require VIEWS_PATH . '/partials/breadcrumbs.php'; ?>

        <!-- Breadcrumb + Page Header -->
        <?php if (isset($pageTitle)): ?>
        <div class="page-header">
            <div>
                <h1 class="page-title text-black"><?= e($pageTitle) ?></h1>
                <?php if (isset($pageSubtitle)): ?>
                    <p class="page-subtitle"><?= e($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <?php if (isset($pageActions)): ?>
                <div class="page-header-actions"><?= $pageActions ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php require VIEWS_PATH . '/partials/alerts.php'; ?>

        <!-- Page Content -->
        <?= $content ?? '' ?>

    </main>

    <!-- Sidebar (right) -->
    <?php require VIEWS_PATH . '/partials/sidebar.php'; ?>
</div>

<!-- Footer -->
<?php require VIEWS_PATH . '/partials/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<!-- App JS -->
<script src="<?= asset('js/app.js') ?>"></script>

<?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>

</body>
</html>
