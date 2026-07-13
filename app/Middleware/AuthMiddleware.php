<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\SessionManager;
use App\Interfaces\MiddlewareInterface;

/**
 * Auth Middleware
 *
 * Ensures the user is authenticated and their session is valid.
 * Applied to all protected routes.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        $session = SessionManager::getInstance();
        $config  = config('session');

        if (!$session->isLoggedIn()) {
            if ($request->isApi()) {
                $response->apiError('Authentication required.', null, 401);
                return;
            }
            $session->flash('error', __('auth.login_required'));
            $response->redirect('/login');
            return;
        }

        // Enforce session timeouts
        $idleSeconds     = (int) ($config['lifetime']          ?? 1800);
        $absoluteSeconds = (int) ($config['absolute_lifetime'] ?? 28800);

        if (!$session->checkTimeout($idleSeconds, $absoluteSeconds)) {
            if ($request->isApi()) {
                $response->apiError('Session expired.', null, 401);
                return;
            }
            $session->flash('error', __('auth.session_expired'));
            $response->redirect('/login');
            return;
        }

        $next();
    }
}
