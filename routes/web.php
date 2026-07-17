<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AnalyticsController;
use App\Controllers\AuthController;
use App\Controllers\CitizenController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\IncidentController;
use App\Controllers\NotificationController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\GuestMiddleware;

// ── Public ─────────────────────────────────────────────────────────────────────
$router->get('/', [HomeController::class, 'index']);

// ── Authentication (Guest only) ────────────────────────────────────────────────
$router->group(['middleware' => [GuestMiddleware::class]], function ($router): void {
    $router->get('/login',    [AuthController::class, 'showLogin']);
    $router->post('/login',   [AuthController::class, 'login'],    [CsrfMiddleware::class]);
    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register',[AuthController::class, 'register'], [CsrfMiddleware::class]);

    $router->get('/forgot-password',         [AuthController::class, 'showForgotPassword']);
    $router->post('/forgot-password',        [AuthController::class, 'sendResetLink'],  [CsrfMiddleware::class]);
    $router->get('/reset-password/{token}',  [AuthController::class, 'showResetPassword']);
    $router->post('/reset-password',         [AuthController::class, 'resetPassword'],  [CsrfMiddleware::class]);
});

// ── Authenticated Routes ───────────────────────────────────────────────────────
$router->group(['middleware' => [AuthMiddleware::class]], function ($router): void {
    $router->post('/logout', [AuthController::class, 'logout'], [CsrfMiddleware::class]);

    // Dashboard
    $router->get('/dashboard', [DashboardController::class, 'index']);

    // Incidents
    $router->get('/incidents', [IncidentController::class, 'index']);
    $router->get('/incidents/create', [IncidentController::class, 'create']);
    $router->post('/incidents', [IncidentController::class, 'store'], [CsrfMiddleware::class]);
    $router->get('/incidents/my', [IncidentController::class, 'indexMy']);
    $router->get('/incidents/drafts', [IncidentController::class, 'drafts']);
    $router->get('/incidents/{id}', [IncidentController::class, 'show']);

    // Citizen Features
    $router->get('/my-impact', [CitizenController::class, 'impact']);
    $router->get('/updates', [CitizenController::class, 'updates']);

    // Notification Settings
    $router->get('/notification-settings', [CitizenController::class, 'notificationSettings']);
    $router->post('/notification-settings/save', [CitizenController::class, 'saveNotificationSettings'], [CsrfMiddleware::class]);

    // Notifications
    $router->get('/notifications', [NotificationController::class, 'index']);
    $router->post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'], [CsrfMiddleware::class]);

    // Notification Unread Count (AJAX)
    $router->get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Profile
    $router->get('/profile', function () {
        \App\Core\SessionManager::getInstance()->flash('info', 'Profile page coming soon.');
        header('Location: /dashboard');
        exit;
    });
    $router->get('/profile/settings', function () {
        \App\Core\SessionManager::getInstance()->flash('info', 'Profile settings coming soon.');
        header('Location: /dashboard');
        exit;
    });

    // Verification Queue
    $router->get('/verification', [App\Controllers\VerificationController::class, 'queue']);
    $router->post('/verification/process', [App\Controllers\VerificationController::class, 'process'], [CsrfMiddleware::class]);

    // Assignments
    $router->get('/assignments', [App\Controllers\AssignmentController::class, 'index']);
    $router->post('/assignments/assign', [App\Controllers\AssignmentController::class, 'assign'], [CsrfMiddleware::class]);

    // Work Orders
    $router->get('/work-orders', [App\Controllers\WorkOrderController::class, 'index']);
    $router->get('/work-orders/{id}', [App\Controllers\WorkOrderController::class, 'show']);
    $router->post('/work-orders/{id}/progress', [App\Controllers\WorkOrderController::class, 'updateProgress'], [CsrfMiddleware::class]);

    // Secure Attachments
    $router->get('/attachments/{id}', [App\Controllers\AttachmentController::class, 'download']);

    // Reports & Exports
    $router->get('/reports', [App\Controllers\ReportController::class, 'index']);
    $router->get('/reports/export-incidents', [App\Controllers\ReportController::class, 'exportCsv']);
    $router->get('/incidents/{id}/receipt',   [App\Controllers\ReportController::class, 'downloadReceipt']);

    // Analytics
    $router->get('/analytics', [AnalyticsController::class, 'index']);
    $router->get('/analytics/sla-compliance', [AnalyticsController::class, 'index']);
    $router->get('/analytics/response-times', [AnalyticsController::class, 'index']);
    $router->get('/analytics/performance', [AnalyticsController::class, 'index']);

    // Escalations
    $router->get('/escalations', [App\Controllers\EscalationController::class, 'index']);

    // National Incident Map
    $router->get('/map', [App\Controllers\MapController::class, 'index']);

    // Help
    $router->get('/help', function () {
        \App\Core\SessionManager::getInstance()->flash('info', 'Help documentation coming soon.');
        header('Location: /dashboard');
        exit;
    });

    // ── Admin Routes ───────────────────────────────────────────────────────────
    $router->get('/admin/users', [AdminController::class, 'users']);
    $router->get('/admin/roles', [AdminController::class, 'roles']);
    $router->get('/admin/categories', [AdminController::class, 'categories']);
    $router->get('/admin/workflow', [AdminController::class, 'workflow']);
    $router->get('/admin/settings', [AdminController::class, 'settings']);
    $router->get('/admin/audit-logs', [App\Controllers\AuditLogController::class, 'index']);

    // Admin SLA
    $router->get('/admin/sla', [AdminController::class, 'sla']);
    $router->post('/admin/sla', [AdminController::class, 'storeSla'], [CsrfMiddleware::class]);
    $router->post('/admin/sla/delete', [AdminController::class, 'deleteSla'], [CsrfMiddleware::class]);

    // Admin System Backup
    $router->get('/admin/backup', [AdminController::class, 'backup']);
    $router->post('/admin/backup/create', [AdminController::class, 'createBackup'], [CsrfMiddleware::class]);
    $router->get('/admin/backup/download', [AdminController::class, 'downloadBackup']);
});

// ── Locale Switcher (public) ──────────────────────────────────────────────────
$router->get('/locale/{code}', function () {
    $code = $_SERVER['REQUEST_URI'] ?? '';
    preg_match('/\/locale\/([a-z]{2})/', $code, $m);
    $allowed = ['en', 'sw'];
    if (isset($m[1]) && in_array($m[1], $allowed, true)) {
        $_SESSION['locale'] = $m[1];
    }
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    header('Location: ' . $referer);
    exit;
});

// ── Locations API ────────────────────────────────────────────────────────────
$router->get('/api/v1/locations/regions', [\App\Controllers\Api\ApiLocationController::class, 'regions']);
$router->get('/api/v1/locations/districts', [\App\Controllers\Api\ApiLocationController::class, 'districts']);
$router->get('/api/v1/locations/wards', [\App\Controllers\Api\ApiLocationController::class, 'wards']);
$router->get('/api/v1/locations/villages', [\App\Controllers\Api\ApiLocationController::class, 'villages']);

$router->get('/migrate-locations', function() {
    require_once __DIR__ . '/../migrate_locations.php';
});
