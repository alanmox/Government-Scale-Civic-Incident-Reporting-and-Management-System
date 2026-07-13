<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\AppException;
use Dotenv\Dotenv;

/**
 * Application Bootstrap & Orchestrator
 *
 * Responsibilities:
 * - Load environment variables
 * - Register configuration
 * - Boot core services (session, DB connection)
 * - Dispatch requests through the router
 */
final class Application
{
    private static ?Application $instance = null;

    /** @var array<string, mixed> */
    private array $config = [];

    /** @var array<string, object> */
    private array $bindings = [];

    private Router $router;
    private Request $request;

    private function __construct()
    {
    }

    /**
     * Returns the singleton application instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Bootstraps the application.
     *
     * @throws AppException
     */
    public function bootstrap(): self
    {
        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->configurePHP();
        $this->bootSession();

        $this->request = new Request();
        $this->router  = new Router($this->request);

        return $this;
    }

    /**
     * Dispatches the current HTTP request through the router.
     */
    public function run(): void
    {
        $this->router->dispatch();
    }

    // ── Configuration ─────────────────────────────────────────────────────────

    /**
     * Retrieves a configuration value using dot notation.
     *
     * @param  string $key     e.g. 'database.host', 'app.debug'
     * @param  mixed  $default Returned if key not found
     * @return mixed
     */
    public function config(string $key, mixed $default = null): mixed
    {
        $parts  = explode('.', $key, 2);
        $group  = $parts[0];
        $subkey = $parts[1] ?? null;

        if (!isset($this->config[$group])) {
            $file = CONFIG_PATH . '/' . $group . '.php';
            $this->config[$group] = file_exists($file) ? require $file : [];
        }

        if ($subkey === null) {
            return $this->config[$group] ?? $default;
        }

        return $this->config[$group][$subkey] ?? $default;
    }

    // ── Service Bindings ─────────────────────────────────────────────────────

    /**
     * Registers a shared service instance.
     */
    public function bind(string $abstract, object $instance): void
    {
        $this->bindings[$abstract] = $instance;
    }

    /**
     * Resolves a registered service.
     *
     * @throws AppException If the binding is not registered.
     */
    public function make(string $abstract): object
    {
        if (!isset($this->bindings[$abstract])) {
            throw new AppException("No binding registered for [{$abstract}].");
        }

        return $this->bindings[$abstract];
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    // ── Private Internals ─────────────────────────────────────────────────────

    private function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->safeLoad();
    }

    private function loadConfiguration(): void
    {
        // Eagerly load critical config
        $this->config['app']      = require CONFIG_PATH . '/app.php';
        $this->config['database'] = require CONFIG_PATH . '/database.php';
        $this->config['session']  = require CONFIG_PATH . '/session.php';
    }

    private function configurePHP(): void
    {
        date_default_timezone_set($this->config['app']['timezone'] ?? 'UTC');
        mb_internal_encoding('UTF-8');

        $debug = (bool) ($this->config['app']['debug'] ?? false);

        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', STORAGE_PATH . '/logs/error.log');
        }
    }

    private function bootSession(): void
    {
        $cfg = $this->config['session'];

        session_name($cfg['name'] ?? 'gcirms_session');

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => (bool) ($cfg['secure']   ?? false),
            'httponly' => (bool) ($cfg['httponly']  ?? true),
            'samesite' => $cfg['samesite'] ?? 'Strict',
        ]);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
