<?php

declare(strict_types=1);

/**
 * Application Bootstrapper
 *
 * Called from public/index.php. Returns the fully bootstrapped Application
 * instance ready for run().
 */

use App\Core\Application;
use App\Exceptions\AppException;

// ── Global Exception Handler ───────────────────────────────────────────────────
set_exception_handler(function (\Throwable $e): void {
    $debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);

    // Always log
    $logDir  = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : __DIR__ . '/../storage/logs';
    $logFile = $logDir . '/error.log';

    if (is_dir($logDir)) {
        error_log(
            '[' . date('Y-m-d H:i:s') . '] ' . get_class($e) . ': '
            . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL,
            3,
            $logFile
        );
    }

    // Display user-friendly error
    if (!headers_sent()) {
        http_response_code($e instanceof AppException ? ($e->getCode() ?: 500) : 500);
    }

    if ($debug) {
        echo '<pre style="background:#1e1e1e;color:#f44;padding:2rem;">';
        echo '<strong>' . get_class($e) . '</strong>: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo "\n\n" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        echo '</pre>';
    } else {
        $errorView = defined('VIEWS_PATH') ? VIEWS_PATH . '/errors/500.php' : null;
        if ($errorView && file_exists($errorView)) {
            require $errorView;
        } else {
            echo '<h1>500 — Internal Server Error</h1><p>An unexpected error occurred. Please try again later.</p>';
        }
    }

    exit(1);
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// ── Boot Application ───────────────────────────────────────────────────────────
$app = Application::getInstance()->bootstrap();

return $app;
