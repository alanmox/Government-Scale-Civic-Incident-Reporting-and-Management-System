<?php

declare(strict_types=1);

namespace App\Interfaces;

/**
 * Repository Interface
 *
 * Defines the standard CRUD contract every repository must fulfill.
 * Services depend on this interface, not on concrete repository classes.
 */
interface RepositoryInterface
{
    /**
     * Finds a single record by its primary key.
     *
     * @return array<string, mixed>|null
     */
    public function findById(string $id): ?array;

    /**
     * Returns all records (use with caution — always paginate in practice).
     *
     * @return list<array<string, mixed>>
     */
    public function findAll(int $limit = 20, int $offset = 0): array;

    /**
     * Persists a new record and returns its generated ID.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): string;

    /**
     * Updates an existing record by ID.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): bool;

    /**
     * Soft-deletes a record (sets deleted_at).
     */
    public function delete(string $id): bool;

    /**
     * Counts total records (excluding soft-deleted).
     */
    public function count(): int;
}
