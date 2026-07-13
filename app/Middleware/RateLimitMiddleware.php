<?php
declare(strict_types=1);
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Interfaces\MiddlewareInterface;

/**
 * Rate Limit Middleware
 *
 * Tracks requests per IP per route in the file cache.
 * Blocks with 429 if limit is exceeded within the window.
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly int $maxRequests = 60,
        private readonly int $windowSeconds = 60
    ) {}

    public function handle(Request $request, Response $response, callable $next): void
    {
        $key   = 'rate_limit_' . md5($request->ip() . $request->getPath());
        $file  = STORAGE_PATH . '/cache/' . $key . '.php';
        $now   = time();
        $entry = ['count' => 0, 'starts_at' => $now];

        if (file_exists($file)) {
            $entry = include $file;
            if ($now - $entry['starts_at'] > $this->windowSeconds) {
                $entry = ['count' => 0, 'starts_at' => $now];
            }
        }

        $entry['count']++;
        file_put_contents($file, '<?php return ' . var_export($entry, true) . ';', LOCK_EX);

        if ($entry['count'] > $this->maxRequests) {
            header('Retry-After: ' . $this->windowSeconds);
            $response->apiError('Too many requests. Please try again later.', null, 429);
            return;
        }

        $next();
    }
}
