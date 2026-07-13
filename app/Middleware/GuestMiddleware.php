<?php
declare(strict_types=1);
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\SessionManager;
use App\Interfaces\MiddlewareInterface;

/** Ensures only unauthenticated users can access guest-only routes (login, register). */
final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        if (SessionManager::getInstance()->isLoggedIn()) {
            $response->redirect('/dashboard');
            return;
        }
        $next();
    }
}
