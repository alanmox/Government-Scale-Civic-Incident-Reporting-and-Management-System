<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\SessionManager;
use App\Interfaces\MiddlewareInterface;

/**
 * CSRF Middleware
 *
 * Validates CSRF token on all state-changing requests (POST, PUT, PATCH, DELETE).
 * Skips validation for API routes (which use Bearer token authentication).
 * Token is compared with constant-time hash_equals to prevent timing attacks.
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    private const EXEMPT_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request, Response $response, callable $next): void
    {
        // API routes use Bearer token auth — no CSRF needed
        if ($request->isApi()) {
            $next();
            return;
        }

        if (in_array($request->getMethod(), self::EXEMPT_METHODS, true)) {
            $next();
            return;
        }

        $token   = $request->csrfToken();
        $session = SessionManager::getInstance();

        if ($token === null || !$session->validateCsrf($token)) {
            // Log the CSRF failure
            error_log(sprintf(
                '[CSRF] Token mismatch — IP: %s, Path: %s, User: %s',
                $request->ip(),
                $request->getPath(),
                $session->userId() ?? 'guest'
            ));

            $session->flash('error', 'Your session has expired. Please try again.');
            $response->redirect('/');
            return;
        }

        $next();
    }
}
