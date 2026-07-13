<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

final class CategoryRepository extends BaseRepository
{
    protected string $table      = 'incident_categories';
    protected string $primaryKey = 'id';
    protected bool   $softDeletes = true;

    /**
     * Finds active categories ordered by name.
     */
    public function findActive(): array
    {
        $sql = "SELECT id, name, slug, description, default_priority, sla_hours
                FROM `{$this->table}`
                WHERE is_active = 1 AND deleted_at IS NULL
                ORDER BY name ASC";
        return $this->execute($sql)->fetchAll();
    }
}
