<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Permission Repository
 * Read-only access to available system permissions.
 * Permissions are seeded, not created via UI.
 */
final class PermissionRepository extends BaseRepository
{
    protected string $table       = 'permissions';
    protected string $primaryKey  = 'id';
    protected bool   $softDeletes = false; // Permissions cannot be deleted

    /**
     * Retrieves all permissions grouped by their module.
     * Useful for rendering the role assignment UI.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function findAllGroupedByModule(): array
    {
        $permissions = $this->findAll(1000, 0); // No pagination needed here
        $grouped = [];

        foreach ($permissions as $perm) {
            $module = $perm['module'] ?? 'other';
            $grouped[$module][] = $perm;
        }

        return $grouped;
    }
}
