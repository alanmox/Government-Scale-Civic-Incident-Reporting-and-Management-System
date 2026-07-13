<?php

declare(strict_types=1);

namespace App\Core;

use App\Interfaces\MiddlewareInterface;

/**
 * Middleware Pipeline
 *
 * Implements a Chain of Responsibility pattern.
 * Each middleware can inspect/modify the request, call $next to proceed,
 * or halt execution (e.g., redirect on auth failure).
 */
final class Pipeline
{
    /** @var MiddlewareInterface[] */
    private array $stages = [];

    private Request $request;
    private Response $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->stages[] = $middleware;
        return $this;
    }

    /**
     * Executes the pipeline and then calls the final handler.
     */
    public function then(callable $finalHandler): void
    {
        $pipeline = array_reduce(
            array_reverse($this->stages),
            function (callable $carry, MiddlewareInterface $middleware): callable {
                return function () use ($middleware, $carry): void {
                    $middleware->handle($this->request, $this->response, $carry);
                };
            },
            $finalHandler
        );

        $pipeline();
    }
}
