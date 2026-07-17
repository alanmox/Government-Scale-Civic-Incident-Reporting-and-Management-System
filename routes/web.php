<?php

declare(strict_types=1);

/**
 * Web Routes
 *
 * Registered on the $router variable provided by Router::loadRoutes().
 * All handler classes follow [ControllerClass::class, 'methodName'] syntax.
 *
 * Middleware applied here:
 *   'guest' = App\Middleware\GuestMiddleware
 *   'auth'  = App\Middleware\AuthMiddleware
 *   'csrf'  = App\Middleware\CsrfMiddleware
 *
 * Phase 0: Only root and placeholder routes.
 * Modules added in Phase 1+ as controllers are built.
 */

use App\Controllers\AuthController;
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

    // Dashboard — role-specific view served from one controller
    $router->get('/dashboard', [DashboardController::class, 'index']);

    // [Phase 1+] Additional routes added here as modules are built:
    // Incidents, Profile, Admin, Reports, etc.
    
    // Incidents
    $router->get('/incidents', [IncidentController::class, 'index']);
    $router->get('/incidents/create', [IncidentController::class, 'create']);
    $router->post('/incidents', [IncidentController::class, 'store'], [CsrfMiddleware::class]);
    $router->get('/incidents/my', [IncidentController::class, 'indexMy']);
    $router->get('/incidents/{id}', [IncidentController::class, 'show']);

    // Notifications
    $router->get('/notifications', [NotificationController::class, 'index']);

    // Verification Queue (Officers)
    $router->get('/verification', [App\Controllers\VerificationController::class, 'queue']);
    $router->post('/verification/process', [App\Controllers\VerificationController::class, 'process'], [CsrfMiddleware::class]);

    // Assignment (Supervisors/Admins)
    $router->get('/assignments', [App\Controllers\AssignmentController::class, 'index']);
    $router->post('/assignments/assign', [App\Controllers\AssignmentController::class, 'assign'], [CsrfMiddleware::class]);

    // Work Orders (Officers)
    $router->get('/work-orders', [App\Controllers\WorkOrderController::class, 'index']);
    $router->get('/work-orders/{id}', [App\Controllers\WorkOrderController::class, 'show']);
    $router->post('/work-orders/{id}/progress', [App\Controllers\WorkOrderController::class, 'updateProgress'], [CsrfMiddleware::class]);

    // Secure Attachments
    $router->get('/attachments/{id}', [App\Controllers\AttachmentController::class, 'download']);

    // Reports and Exports
    $router->get('/reports/export-incidents', [App\Controllers\ReportController::class, 'exportCsv']);
    $router->get('/incidents/{id}/receipt',   [App\Controllers\ReportController::class, 'downloadReceipt']);

    // ── SPRINT 1: New Navigation Routes ───────────────────────────────────────

    // Analytics & KPIs
    $router->get('/analytics', [App\Controllers\AnalyticsController::class, 'index']);

    // Escalations
    $router->get('/escalations', [App\Controllers\EscalationController::class, 'index']);

    // National Incident Map
    $router->get('/map', [App\Controllers\MapController::class, 'index']);

    // Admin Audit Logs (live)
    $router->get('/admin/audit-logs', [App\Controllers\AuditLogController::class, 'index']);

    // Admin SLA Management
    $router->get('/admin/sla', [App\Controllers\AdminController::class, 'sla']);
    $router->post('/admin/sla', [App\Controllers\AdminController::class, 'storeSla'], [CsrfMiddleware::class]);
    $router->post('/admin/sla/delete', [App\Controllers\AdminController::class, 'deleteSla'], [CsrfMiddleware::class]);

    // Admin System Backup
    $router->get('/admin/backup', [App\Controllers\AdminController::class, 'backup']);
    $router->post('/admin/backup/create', [App\Controllers\AdminController::class, 'createBackup'], [CsrfMiddleware::class]);
    $router->get('/admin/backup/download', [App\Controllers\AdminController::class, 'downloadBackup']);

    // Citizen — Incident Drafts (stub)
    $router->get('/incidents/drafts', [App\Controllers\IncidentController::class, 'drafts']);
});

// ── Locale Switcher (public — no auth needed) ──────────────────────────────────
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
