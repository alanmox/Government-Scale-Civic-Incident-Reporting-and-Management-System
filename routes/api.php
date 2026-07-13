<?php

declare(strict_types=1);

/**
 * API Routes — /api/v1/
 *
 * All API routes are versioned under /api/v1/.
 * Authentication: Bearer token via ApiAuthMiddleware (Phase 8).
 * Response format: JSON envelope { success, data, message, errors, meta }.
 *
 * Phase 0: Placeholder only. Full endpoints added in Phase 8.
 */

use App\Controllers\Api\ApiHealthController;
use App\Middleware\RateLimitMiddleware;

$router->group([
    'prefix'     => '/api/v1',
    'middleware' => [new RateLimitMiddleware(100, 60)],
], function ($router): void {

    // Health check — publicly accessible
    $router->get('/health', [ApiHealthController::class, 'index']);

    // [Phase 8] Full endpoints:
    // POST   /api/v1/auth/login
    // POST   /api/v1/auth/logout
    // GET    /api/v1/incidents
    // POST   /api/v1/incidents
    // GET    /api/v1/incidents/{id}
    // PATCH  /api/v1/incidents/{id}/status
    // GET    /api/v1/categories
    // GET    /api/v1/locations/regions
    // GET    /api/v1/notifications
    // GET    /api/v1/analytics/summary
});
