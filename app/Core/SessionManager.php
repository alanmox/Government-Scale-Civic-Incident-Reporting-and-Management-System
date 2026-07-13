<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session Manager
 *
 * Centralizes all session operations. Enforces security policies:
 * idle timeout, absolute timeout, CSRF token management,
 * flash messages, and session fixation prevention.
 */
final class SessionManager
{
    private static ?SessionManager $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ── Lifecycle ──────────────────────────────────────────────────────────────

    /**
     * Called after successful authentication.
     * Regenerates session ID to prevent session fixation.
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_started_at']  = time();
        $_SESSION['_last_active'] = time();
    }

    /**
     * Destroys the session completely on logout.
     */
    public function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Checks whether the session has exceeded idle or absolute timeout.
     * Destroys and redirects if expired.
     */
    public function checkTimeout(int $idleSeconds = 1800, int $absoluteSeconds = 28800): bool
    {
        $now = time();

        // Absolute timeout
        if (isset($_SESSION['_started_at'])) {
            if ($now - $_SESSION['_started_at'] > $absoluteSeconds) {
                $this->destroy();
                return false;
            }
        }

        // Idle timeout
        if (isset($_SESSION['_last_active'])) {
            if ($now - $_SESSION['_last_active'] > $idleSeconds) {
                $this->destroy();
                return false;
            }
        }

        $_SESSION['_last_active'] = $now;
        return true;
    }

    // ── Authentication State ───────────────────────────────────────────────────

    public function login(string $userId, string $roleId, array $extra = []): void
    {
        $this->regenerate();

        $_SESSION['user_id'] = $userId;
        $_SESSION['role_id'] = $roleId;

        foreach ($extra as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    public function logout(): void
    {
        $this->destroy();
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function userId(): ?string
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function roleId(): ?string
    {
        return $_SESSION['role_id'] ?? null;
    }

    // ── CSRF Token ─────────────────────────────────────────────────────────────

    /**
     * Returns the current CSRF token, generating one if needed.
     */
    public function csrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Validates an incoming CSRF token using constant-time comparison.
     */
    public function validateCsrf(string $token): bool
    {
        return isset($_SESSION['_csrf_token'])
            && hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Rotates the CSRF token (call after any state-changing request).
     */
    public function rotateCsrf(): void
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    // ── Flash Messages ─────────────────────────────────────────────────────────

    /**
     * Stores a flash message consumed on the next request.
     */
    public function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    /**
     * Retrieves and clears flash messages of a given type.
     *
     * @return string[]
     */
    public function getFlash(string $type): array
    {
        $messages = $_SESSION['_flash'][$type] ?? [];
        unset($_SESSION['_flash'][$type]);
        return $messages;
    }

    /**
     * Checks whether any flash messages of a given type exist.
     */
    public function hasFlash(string $type): bool
    {
        return !empty($_SESSION['_flash'][$type]);
    }

    // ── Generic Get/Set ────────────────────────────────────────────────────────

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
