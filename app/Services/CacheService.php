<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Cache Service — File-based Cache
 *
 * Stores serialized PHP values in storage/cache/.
 * Used for: system_settings, categories, regions, roles, permissions.
 * Cache invalidated explicitly when underlying data changes.
 */
final class CacheService
{
    private string $cachePath;
    private int $defaultTtl;

    public function __construct()
    {
        $this->cachePath  = STORAGE_PATH . '/cache';
        $this->defaultTtl = (int) (config('cache.default_ttl') ?? 3600);
    }

    // ── Core Operations ────────────────────────────────────────────────────────

    /**
     * Returns cached value for key, or null if missing / expired.
     */
    public function get(string $key): mixed
    {
        $file = $this->filePath($key);

        if (!file_exists($file)) {
            return null;
        }

        $data = include $file;

        if (!is_array($data) || !isset($data['expires_at'], $data['value'])) {
            return null;
        }

        if ($data['expires_at'] !== 0 && time() > $data['expires_at']) {
            $this->delete($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Stores a value in the cache.
     *
     * @param int $ttl Seconds until expiry. 0 = never expires.
     */
    public function set(string $key, mixed $value, int $ttl = 0): void
    {
        $ttl      = $ttl ?: $this->defaultTtl;
        $expiresAt = $ttl === 0 ? 0 : time() + $ttl;

        $data = [
            'key'        => $key,
            'value'      => $value,
            'expires_at' => $expiresAt,
            'created_at' => time(),
        ];

        file_put_contents(
            $this->filePath($key),
            '<?php return ' . var_export($data, true) . ';',
            LOCK_EX
        );
    }

    /**
     * Checks whether a valid (non-expired) cache entry exists.
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Removes a single cache entry.
     */
    public function invalidate(string $key): void
    {
        $this->delete($this->filePath($key));
    }

    /**
     * Removes all cache files (or only those matching a prefix tag).
     */
    public function flush(string $tag = ''): void
    {
        $pattern = $this->cachePath . '/cache_' . ($tag ? slugify($tag) . '_' : '') . '*.php';
        foreach (glob($pattern) ?: [] as $file) {
            $this->delete($file);
        }
    }

    /**
     * Returns a cached value, computing and storing it if not present.
     *
     * @param callable(): mixed $callback
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = $this->get($key);

        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    // ── Private ────────────────────────────────────────────────────────────────

    private function filePath(string $key): string
    {
        return $this->cachePath . '/cache_' . md5($key) . '.php';
    }

    private function delete(string $file): void
    {
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
