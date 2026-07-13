<?php

declare(strict_types=1);

namespace App\Database;

use App\Exceptions\DatabaseException;
use PDO;

/**
 * PDO Connection — Singleton
 *
 * Ensures a single database connection for the lifetime of the request.
 * All repositories receive this instance via constructor injection.
 *
 * @throws DatabaseException On connection failure.
 */
final class Connection
{
    private static ?Connection $instance = null;
    private PDO $pdo;

    /**
     * @throws DatabaseException
     */
    private function __construct()
    {
        $cfg = require CONFIG_PATH . '/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['name'],
            $cfg['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $cfg['options']);
        } catch (\PDOException $e) {
            // Never expose connection details in the message
            throw new DatabaseException(
                'Database connection failed. Check your configuration.',
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Returns the singleton Connection instance.
     *
     * @throws DatabaseException
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Returns the underlying PDO instance.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // ── Transaction Helpers ────────────────────────────────────────────────────

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Executes a callable within a transaction.
     * Automatically rolls back on any exception.
     *
     * @throws DatabaseException|\Throwable
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this->pdo);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Returns the last inserted auto-increment ID.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    // ── Prevent cloning and unserialization ───────────────────────────────────

    public function __clone()
    {
        throw new DatabaseException('Cannot clone a singleton Connection instance.');
    }

    public function __wakeup(): void
    {
        throw new DatabaseException('Cannot unserialize a singleton Connection instance.');
    }
}
