<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Role Repository
 * Manages roles and their associated permissions.
 */
final class RoleRepository extends BaseRepository
{
    protected string $table       = 'roles';
    protected string $primaryKey  = 'id';
    protected bool   $softDeletes = true;

    /**
     * Finds a role by its unique slug.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findOneWhere(['slug' => $slug]);
    }

    /**
     * Retrieves all permissions assigned to a specific role.
     *
     * @return list<array<string, mixed>>
     */
    public function getPermissions(string $roleId): array
    {
        $sql = 'SELECT p.*
                FROM `permissions` p
                INNER JOIN `role_permissions` rp ON rp.permission_id = p.id
                WHERE rp.role_id = :roleId
                ORDER BY p.module, p.name';

        return $this->execute($sql, ['roleId' => $roleId])->fetchAll();
    }

    /**
     * Assigns a permission to a role.
     */
    public function assignPermission(string $roleId, string $permissionId, ?string $grantedBy = null): void
    {
        $sql = 'INSERT IGNORE INTO `role_permissions` (role_id, permission_id, granted_by)
                VALUES (:roleId, :permissionId, :grantedBy)';

        $this->execute($sql, [
            'roleId'       => $roleId,
            'permissionId' => $permissionId,
            'grantedBy'    => $grantedBy,
        ]);
    }

    /**
     * Revokes a permission from a role.
     */
    public function revokePermission(string $roleId, string $permissionId): void
    {
        $sql = 'DELETE FROM `role_permissions` WHERE role_id = :roleId AND permission_id = :permissionId';
        $this->execute($sql, ['roleId' => $roleId, 'permissionId' => $permissionId]);
    }

    /**
     * Replaces all permissions for a role with a new set.
     *
     * @param string[] $permissionIds
     */
    public function syncPermissions(string $roleId, array $permissionIds, ?string $grantedBy = null): void
    {
        $this->transaction(function() use ($roleId, $permissionIds, $grantedBy): void {
            $this->execute('DELETE FROM `role_permissions` WHERE role_id = :roleId', ['roleId' => $roleId]);

            foreach ($permissionIds as $permId) {
                $this->assignPermission($roleId, $permId, $grantedBy);
            }
        });
    }
}
