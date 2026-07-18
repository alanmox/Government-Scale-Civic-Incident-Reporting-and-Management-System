<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\AppException;
use App\Interfaces\MiddlewareInterface;

/**
 * Router
 *
 * Manual URL router. Parses registered routes, matches the current request
 * path (including named parameters like {id}), builds the middleware pipeline,
 * and dispatches to the target controller action.
 *
 * Routes are defined in routes/web.php and routes/api.php.
 */
final class Router
{
    /**
     * Route registry: method → [ [pattern, handler, middlewares] ]
     *
     * @var array<string, list<array{pattern: string, handler: array{string, string}, middleware: string[]}>>
     */
    private array $routes = [];

    /** @var string[] Currently active middleware group */
    private array $currentMiddleware = [];

    /** @var string Current route group prefix */
    private string $prefix = '';

    private Request $request;
    private Response $response;

    public function __construct(Request $request)
    {
        $this->request  = $request;
        $this->response = new Response();
    }

    // ── Route Registration ─────────────────────────────────────────────────────
    
    public function get(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function patch(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }
    
    public function delete(string $path, array|callable $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Groups routes under a common prefix and/or middleware set.
     *
     * @param array{prefix?: string, middleware?: string[]} $attributes
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix     = $this->prefix;
        $previousMiddleware = $this->currentMiddleware;

        $this->prefix             = $previousPrefix . ($attributes['prefix'] ?? '');
        $this->currentMiddleware  = array_merge($previousMiddleware, $attributes['middleware'] ?? []);

        $callback($this);

        $this->prefix            = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;
    }

    // ── Dispatch ───────────────────────────────────────────────────────────────

    /**
     * Matches the current request and dispatches to the controller action
     * through the middleware pipeline.
     */
    public function dispatch(): void
    {
        $this->loadRoutes();

        $method = $this->request->getMethod();
        $path   = $this->request->getPath();

        $routeList = $this->routes[$method] ?? [];

        foreach ($routeList as $route) {
            $params = $this->matchRoute($route['pattern'], $path);

            if ($params !== null) {
                $this->request->setRouteParams($params);

                $this->runPipeline($route['middleware'], $route['handler']);
                return;
            }
        }

        // No route matched
        $this->response->abort(404, "No route found for [{$method}] {$path}");
    }

    // ── Private Internals ──────────────────────────────────────────────────────

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware): self
    {
        $this->routes[$method][] = [
            'pattern'    => $this->prefix . $path,
            'handler'    => $handler,
            'middleware' => array_merge($this->currentMiddleware, $middleware),
        ];

        return $this;
    }

    private function loadRoutes(): void
    {
        $router = $this;
        require_once BASE_PATH . '/routes/web.php';
        require_once BASE_PATH . '/routes/api.php';
    }

    /**
     * Checks if the request path matches a route pattern.
     * Returns extracted named parameters or null on no match.
     *
     * @return array<string, string>|null
     */
    private function matchRoute(string $pattern, string $path): ?array
    {
        // Convert {param} placeholders to named regex groups
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        // Return only named (string-keyed) captures
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Builds the middleware pipeline and executes it, terminating with the controller action.
     *
     * @param string[]           $middlewareList
     * @param array{string, string}|callable $handler
     *
     * @throws AppException
     */
    private function runPipeline(array $middlewareList, array|callable $handler): void
    {
        $pipeline = new Pipeline($this->request, $this->response);

        foreach ($middlewareList as $middlewareClass) {
            if (!class_exists($middlewareClass)) {
                throw new AppException("Middleware [{$middlewareClass}] not found.");
            }
            $instance = new $middlewareClass();
            if (!$instance instanceof MiddlewareInterface) {
                throw new AppException("Middleware [{$middlewareClass}] must implement MiddlewareInterface.");
            }
            $pipeline->pipe($instance);
        }

        $pipeline->then(function () use ($handler): void {
            $this->callController($handler);
        });
    }

    /**
     * Instantiates the controller and calls the specified action method, or executes a closure.
     *
     * @param array{string, string}|callable $handler [ControllerClass, method] or Closure
     *
     * @throws AppException
     */
    private function callController(array|callable $handler): void
    {
        if (is_callable($handler)) {
            call_user_func($handler);
            return;
        }

        [$class, $method] = $handler;

        if (!class_exists($class)) {
            throw new AppException("Controller [{$class}] not found.");
        }

        $controller = new $class($this->request, $this->response);

        if (!method_exists($controller, $method)) {
            throw new AppException("Method [{$method}] not found on [{$class}].");
        }

        $controller->{$method}();
    }
}
