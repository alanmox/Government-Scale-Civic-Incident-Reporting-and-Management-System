<?php
declare(strict_types=1);
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\SessionManager;
use App\Interfaces\MiddlewareInterface;

/**
 * Authorization Middleware
 *
 * Checks that the current user holds a specific permission.
 * Instantiated with the required permission string.
 */
final class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $permission)
    {
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $session     = SessionManager::getInstance();
        $permissions = $session->get('permissions', []);

        if (!in_array($this->permission, $permissions, true)) {
            if ($request->isApi()) {
                $response->apiError('Forbidden: insufficient permissions.', null, 403);
                return;
            }
            $response->abort(403, "Permission required: [{$this->permission}]");
            return;
        }

        $next();
    }
}
