<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\AppException;

/**
 * HTTP Request Abstraction
 *
 * Wraps superglobals into a clean, immutable-by-convention object.
 * All access to $_GET, $_POST, $_FILES, $_SERVER, $_COOKIE goes through here.
 */
final class Request
{
    private string $method;
    private string $uri;
    private string $path;

    /** @var array<string, string> */
    private array $queryParams;

    /** @var array<string, mixed> */
    private array $body;

    /** @var array<string, mixed> */
    private array $files;

    /** @var array<string, string> */
    private array $headers;

    /** @var array<string, string> */
    private array $routeParams = [];

    public function __construct()
    {
        $this->method      = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri         = $_SERVER['REQUEST_URI'] ?? '/';
        $this->queryParams = $_GET ?? [];
        $this->body        = $_POST ?? [];
        $this->files       = $_FILES ?? [];
        $this->headers     = $this->parseHeaders();
        $this->path        = $this->parsePath();
    }

    // ── HTTP Method ────────────────────────────────────────────────────────────

    public function getMethod(): string
    {
        // Support method spoofing for PUT/PATCH/DELETE via hidden _method field
        if ($this->method === 'POST' && isset($this->body['_method'])) {
            return strtoupper($this->body['_method']);
        }

        return $this->method;
    }

    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    public function isGet(): bool   { return $this->getMethod() === 'GET'; }
    public function isPost(): bool  { return $this->getMethod() === 'POST'; }
    public function isAjax(): bool  { return ($this->headers['X-Requested-With'] ?? '') === 'XMLHttpRequest'; }
    public function isApi(): bool   { return str_starts_with($this->path, '/api/'); }

    // ── Path & URI ─────────────────────────────────────────────────────────────

    public function getPath(): string
    {
        return $this->path;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    // ── Input ──────────────────────────────────────────────────────────────────

    /**
     * Retrieves a sanitized POST/query value.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->queryParams[$key] ?? $default;
    }

    /**
     * Retrieves and trims a string input value.
     */
    public function string(string $key, string $default = ''): string
    {
        return trim((string) $this->input($key, $default));
    }

    /**
     * Retrieves an integer input value.
     */
    public function int(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    /**
     * Retrieves all POST body data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->body;
    }

    /**
     * Retrieves only specified keys from input.
     *
     * @param  string[] $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->body, array_flip($keys));
    }

    /**
     * Checks whether an input key exists.
     */
    public function has(string $key): bool
    {
        return isset($this->body[$key]) || isset($this->queryParams[$key]);
    }

    // ── Query String ───────────────────────────────────────────────────────────

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    // ── Files ──────────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    // ── Headers ────────────────────────────────────────────────────────────────

    public function header(string $name, ?string $default = null): ?string
    {
        $normalized = str_replace('-', '_', strtoupper($name));
        return $this->headers[$normalized] ?? $this->headers[$name] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization', '');
        if (str_starts_with($auth ?? '', 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    public function csrfToken(): ?string
    {
        return $this->header('X-CSRF-Token')
            ?? $this->input('_csrf_token');
    }

    // ── Route Parameters ───────────────────────────────────────────────────────

    /**
     * @param array<string, string> $params
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    // ── IP & User Agent ────────────────────────────────────────────────────────

    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    // ── JSON Body ──────────────────────────────────────────────────────────────

    /**
     * Parses and returns JSON request body (for API endpoints).
     *
     * @return array<string, mixed>
     * @throws AppException On invalid JSON.
     */
    public function json(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new AppException('Invalid JSON body: ' . json_last_error_msg());
        }

        return $data;
    }

    // ── Private ────────────────────────────────────────────────────────────────

    private function parsePath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH) ?? '/';

        $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($script !== '/' && str_starts_with($path, $script)) {
            $path = substr($path, strlen($script));
        }

        return '/' . ltrim($path, '/');
    }

    /**
     * @return array<string, string>
     */
    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', substr($key, 5));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}
