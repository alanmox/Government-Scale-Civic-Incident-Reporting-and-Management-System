<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Core\Request;
use App\Core\Response;

/**
 * Middleware Interface
 *
 * All middleware must implement this contract.
 */
interface MiddlewareInterface
{
    /**
     * Handles the incoming request.
     *
     * Call $next() to pass control to the next stage.
     * Do not call $next() to halt execution (e.g., redirect).
     */
    public function handle(Request $request, Response $response, callable $next): void;
}
