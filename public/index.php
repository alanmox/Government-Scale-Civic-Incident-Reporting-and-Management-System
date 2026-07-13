<?php

/**
 * GCIRMS — Front Controller
 *
 * This is the sole entry point for all HTTP requests.
 * Apache rewrites every request here via .htaccess.
 *
 * Flow: Bootstrap → Application → Router → Middleware → Controller → View
 */

declare(strict_types=1);

// ── Security: Prevent direct DOCUMENT_ROOT access in production ──────────────
define('GCIRMS_LOADED', true);

// ── Path constants ────────────────────────────────────────────────────────────
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEWS_PATH', BASE_PATH . '/views');
define('RESOURCES_PATH', BASE_PATH . '/resources');
define('PUBLIC_PATH', __DIR__);

// ── Autoloader ────────────────────────────────────────────────────────────────
$autoloader = BASE_PATH . '/vendor/autoload.php';

if (!file_exists($autoloader)) {
    http_response_code(503);
    echo '<h1>Service Unavailable</h1>';
    echo '<p>Dependencies not installed. Run <code>composer install</code>.</p>';
    exit(1);
}

require_once $autoloader;

// ── Bootstrap Application ─────────────────────────────────────────────────────
$app = require_once BASE_PATH . '/bootstrap/app.php';

// ── Run ───────────────────────────────────────────────────────────────────────
$app->run();
