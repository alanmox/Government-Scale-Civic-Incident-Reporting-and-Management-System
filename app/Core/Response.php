<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Response Builder
 *
 * Provides typed response factories: HTML view, JSON, redirect, download.
 * All output goes through here — never echo directly in controllers.
 */
final class Response
{
    private int $statusCode = 200;

    /** @var array<string, string> */
    private array $headers = [];

    private string $body = '';

    // ── Status ─────────────────────────────────────────────────────────────────

    public function setStatus(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->statusCode;
    }

    // ── Headers ────────────────────────────────────────────────────────────────

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    // ── Factories ──────────────────────────────────────────────────────────────

    /**
     * Renders a PHP view file and sends the HTML response.
     *
     * @param string               $view Path relative to views/ directory (e.g. 'auth/login')
     * @param array<string, mixed> $data Variables to extract into the view's scope
     * @param int                  $status HTTP status code
     */
    public function view(string $view, array $data = [], int $status = 200): void
    {
        $file = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            $this->abort(404, "View [{$view}] not found.");
            return;
        }

        extract($data, EXTR_SKIP);

        $this->statusCode = $status;
        $this->sendHeaders();

        // Render view content into buffer
        ob_start();
        require $file;
        $content = ob_get_clean();

        // Wrap in layout if specified
        $layoutFile = isset($layout) ? VIEWS_PATH . '/layouts/' . $layout . '.php' : null;
        if ($layoutFile && file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Sends a JSON response.
     *
     * @param mixed $data
     */
    public function json(mixed $data, int $status = 200): void
    {
        $this->statusCode = $status;
        $this->setHeader('Content-Type', 'application/json; charset=UTF-8');
        $this->sendHeaders();

        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Sends a standardized API JSON response envelope.
     *
     * @param mixed                     $data
     * @param string|null               $message
     * @param array<string, mixed>|null $errors
     * @param array<string, mixed>|null $meta   Pagination, etc.
     */
    public function apiSuccess(
        mixed $data = null,
        ?string $message = 'Success',
        ?array $meta = null,
        int $status = 200
    ): void {
        $this->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'errors'  => null,
            'meta'    => $meta,
        ], $status);
    }

    /**
     * Sends a standardized API error envelope.
     *
     * @param array<string, mixed>|null $errors
     */
    public function apiError(
        string $message,
        ?array $errors = null,
        int $status = 400
    ): void {
        $this->json([
            'success' => false,
            'data'    => null,
            'message' => $message,
            'errors'  => $errors,
            'meta'    => null,
        ], $status);
    }

    /**
     * Performs an HTTP redirect.
     * Relative paths are resolved against the app base URL.
     */
    public function redirect(string $url, int $status = 302): void
    {
        // Use url() helper for relative paths
        if (str_starts_with($url, '/')) {
            $url = url($url);
        }
        $this->statusCode = $status;
        $this->setHeader('Location', $url);
        $this->sendHeaders();
        exit;
    }

    /**
     * Redirects back to the previous page (HTTP_REFERER).
     */
    public function back(string $fallback = '/'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        $this->redirect($referer);
    }

    /**
     * Sends a file download response.
     */
    public function download(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            $this->abort(404, 'File not found.');
            return;
        }

        $filename ??= basename($filePath);
        $mimeType  = mime_content_type($filePath) ?: 'application/octet-stream';

        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->setHeader('Content-Length', (string) filesize($filePath));
        $this->setHeader('Pragma', 'no-cache');
        $this->setHeader('Cache-Control', 'must-revalidate');
        $this->sendHeaders();

        readfile($filePath);
        exit;
    }

    /**
     * Aborts with a given HTTP error code.
     */
    public function abort(int $status, string $message = ''): void
    {
        $this->statusCode = $status;
        $this->sendHeaders();

        $errorView = VIEWS_PATH . '/errors/' . $status . '.php';
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo "<h1>Error {$status}</h1><p>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        exit;
    }

    // ── Private ────────────────────────────────────────────────────────────────

    private function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Security headers always applied
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
