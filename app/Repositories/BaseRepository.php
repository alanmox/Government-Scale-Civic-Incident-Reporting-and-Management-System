<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use App\Exceptions\DatabaseException;
use App\Interfaces\RepositoryInterface;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Base Repository
 *
 * Provides concrete repositories with a shared PDO instance and
 * reusable query helpers. All SQL stays here or in subclasses —
 * never in Services or Controllers.
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected readonly PDO $pdo;

    /** Subclasses declare their table name. */
    protected string $table = '';

    /** Primary key column name. */
    protected string $primaryKey = 'id';

    /** Whether this table uses soft deletes. */
    protected bool $softDeletes = true;

    public function __construct()
    {
        $this->pdo = Connection::getInstance()->getPdo();
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // ── RepositoryInterface Implementation ─────────────────────────────────────

    /**
     * {@inheritdoc}
     */
    public function findById(string $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}`
                WHERE `{$this->primaryKey}` = :id"
             . ($this->softDeletes ? ' AND `deleted_at` IS NULL' : '');

        $stmt = $this->execute($sql, ['id' => $id]);
        $row  = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT * FROM `{$this->table}`"
             . ($this->softDeletes ? ' WHERE `deleted_at` IS NULL' : '')
             . " ORDER BY `created_at` DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): string
    {
        $columns      = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        $columnList   = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
        $valueList    = implode(', ', $placeholders);

        $sql  = "INSERT INTO `{$this->table}` ({$columnList}) VALUES ({$valueList})";
        $this->execute($sql, $data);

        return $this->pdo->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $setParts = array_map(fn($col) => "`{$col}` = :{$col}", array_keys($data));
        $setClause = implode(', ', $setParts);

        $sql       = "UPDATE `{$this->table}` SET {$setClause} WHERE `{$this->primaryKey}` = :__id";
        $data['__id'] = $id;

        $stmt = $this->execute($sql, $data);
        return $stmt->rowCount() > 0;
    }

    /**
     * {@inheritdoc} — Soft delete sets deleted_at; hard delete removes the row.
     */
    public function delete(string $id): bool
    {
        if ($this->softDeletes) {
            return $this->update($id, [
                'deleted_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $sql  = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->execute($sql, ['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`"
             . ($this->softDeletes ? ' WHERE `deleted_at` IS NULL' : '');

        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    // ── Transaction Support ────────────────────────────────────────────────────

    /**
     * Executes a callback within a database transaction.
     *
     * @param callable $callback
     * @throws DatabaseException
     */
    public function transaction(callable $callback): void
    {
        try {
            $this->pdo->beginTransaction();
            $callback();
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ── Protected Query Helpers ────────────────────────────────────────────────

    /**
     * Prepares and executes a parameterized query.
     *
     * @param array<string, mixed> $params
     * @throws DatabaseException
     */
    protected function execute(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new DatabaseException(
                'Query execution failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Finds rows matching multiple column conditions (simple equality).
     *
     * @param  array<string, mixed> $conditions
     * @return list<array<string, mixed>>
     */
    protected function findWhere(array $conditions, int $limit = 20, int $offset = 0): array
    {
        $parts = [];
        foreach (array_keys($conditions) as $col) {
            $parts[] = "`{$col}` = :{$col}";
        }

        $where = implode(' AND ', $parts);

        if ($this->softDeletes) {
            $where = $where ? "{$where} AND `deleted_at` IS NULL" : '`deleted_at` IS NULL';
        }

        $sql = "SELECT * FROM `{$this->table}`"
             . ($where ? " WHERE {$where}" : '')
             . " LIMIT :__limit OFFSET :__offset";

        $stmt = $this->pdo->prepare($sql);

        foreach ($conditions as $col => $val) {
            $stmt->bindValue(":{$col}", $val);
        }

        $stmt->bindValue(':__limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':__offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Finds the first row matching conditions.
     *
     * @param  array<string, mixed> $conditions
     * @return array<string, mixed>|null
     */
    protected function findOneWhere(array $conditions): ?array
    {
        $rows = $this->findWhere($conditions, 1, 0);
        return $rows[0] ?? null;
    }
}
