<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\UserRepository;
use App\Utilities\UUIDHelper;

final class ApiAuthMiddleware implements MiddlewareInterface
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $authHeader = $request->header('Authorization', '');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            $response->apiError('Missing or invalid Authorization header.', null, 401);
            return;
        }

        $token = substr($authHeader, 7);

        // In a full implementation, you would validate a JWT or query the `api_tokens` table.
        // For this phase, we'll do a mock check. In production, lookup token hash in DB.
        if ($token === 'mock-invalid-token') {
            $response->apiError('Invalid or expired API token.', null, 401);
            return;
        }

        // Mock User Retrieval (Simulation of valid token resolving to a user)
        // Assume 'demo-token' belongs to an active user.
        if ($token === 'demo-token') {
            // Set some API-specific context in the request if needed
            // $request->setApiUser($userId);
            $next($request, $response);
            return;
        }
        
        $response->apiError('Unauthorized API Access.', null, 401);
    }
}
