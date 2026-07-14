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
    $router->get('/incidents/{id}/receipt', [App\Controllers\ReportController::class, 'downloadReceipt']);
});
