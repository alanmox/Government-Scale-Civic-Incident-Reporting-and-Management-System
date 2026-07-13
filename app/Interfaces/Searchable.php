<?php declare(strict_types=1);
namespace App\Interfaces;

/** Classes implementing this support full-text search. */
interface Searchable
{
    /**
     * @param  array<string, mixed> $criteria
     * @return list<array<string, mixed>>
     */
    public function search(array $criteria, int $limit = 20, int $offset = 0): array;
    public function searchCount(array $criteria): int;
}
