<?php

declare(strict_types=1);

/**
 * GCIRMS Global Helper Functions
 *
 * These are utility functions available throughout the application.
 * Autoloaded via composer.json "files" directive.
 *
 * Rules:
 * - Stateless utility functions only
 * - No business logic
 * - No direct DB access
 * - No HTTP concerns
 */

// ── Output Escaping ───────────────────────────────────────────────────────────

/**
 * Escapes a string for safe HTML output (XSS prevention).
 * Use on every piece of user-supplied data before echoing in a view.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Localization ───────────────────────────────────────────────────────────────

/**
 * Returns the translated string for the given key.
 * Falls back to the key itself if not found.
 *
 * Usage: __('auth.login_failed')
 */
function __(string $key, array $replace = []): string
{
    static $translations = [];
    static $locale       = null;

    if ($locale === null) {
        $locale = $_ENV['APP_LOCALE'] ?? 'en';
    }

    if (empty($translations[$locale])) {
        $file = RESOURCES_PATH . '/lang/' . $locale . '.php';
        $translations[$locale] = file_exists($file) ? require $file : [];
    }

    $text = $translations[$locale][$key] ?? $key;

    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, (string) $value, $text);
    }

    return $text;
}

// ── Configuration ─────────────────────────────────────────────────────────────

/**
 * Retrieves a configuration value using dot notation.
 * Delegates to the Application singleton.
 */
function config(string $key, mixed $default = null): mixed
{
    return \App\Core\Application::getInstance()->config($key, $default);
}

// ── URL Generation ────────────────────────────────────────────────────────────

/**
 * Generates a URL relative to the application base URL.
 */
function url(string $path = ''): string
{
    $base = rtrim(config('app.url', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Generates a URL for a public asset.
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

// ── Date / Time ───────────────────────────────────────────────────────────────

/**
 * Returns the current datetime formatted for MySQL.
 */
function now(): string
{
    return date('Y-m-d H:i:s');
}

/**
 * Formats a datetime string for display.
 */
function format_date(string $datetime, string $format = 'd M Y, H:i'): string
{
    if (empty($datetime)) {
        return '—';
    }

    try {
        $dt = new \DateTimeImmutable($datetime);
        return $dt->format($format);
    } catch (\Exception) {
        return $datetime;
    }
}

// ── CSRF ──────────────────────────────────────────────────────────────────────

/**
 * Returns a hidden CSRF token input field for inclusion in forms.
 */
function csrf_field(): string
{
    $token = \App\Core\SessionManager::getInstance()->csrfToken();
    return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
}

/**
 * Returns only the raw CSRF token string.
 */
function csrf_token(): string
{
    return \App\Core\SessionManager::getInstance()->csrfToken();
}

// ── Debugging (Dev only) ──────────────────────────────────────────────────────

/**
 * Dumps a variable and exits. Only active when APP_DEBUG=true.
 */
function dd(mixed ...$vars): never
{
    if (config('app.debug')) {
        foreach ($vars as $var) {
            echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;margin:0.5rem;border-radius:4px;">';
            echo e(print_r($var, true));
            echo '</pre>';
        }
    }
    exit;
}

// ── String Utilities ───────────────────────────────────────────────────────────

/**
 * Truncates a string to a maximum length, appending an ellipsis.
 */
function str_limit(string $text, int $limit = 100, string $end = '...'): string
{
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return mb_substr($text, 0, $limit) . $end;
}

/**
 * Converts a string to slug format
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text) ?? $text;
    $text = preg_replace('/[\s-]+/', '-', $text) ?? $text;
    return trim($text, '-');
}

// ── Number Formatting ─────────────────────────────────────────────────────────

/**
 * Formats a number with comma thousands separator.
 */
function num_format(float|int $number, int $decimals = 0): string
{
    return number_format($number, $decimals, '.', ',');
}

/**
 * Formats file size bytes into a human-readable string.
 */
function file_size_format(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i     = 0;
    $size  = (float) $bytes;

    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }

    return round($size, 2) . ' ' . $units[$i];
}
