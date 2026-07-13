<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AttachmentRepository extends BaseRepository
{
    protected string $table      = 'attachments';
    protected string $primaryKey = 'id';
    protected bool   $softDeletes = true;

    /**
     * Get attachments for a specific entity.
     * 
     * @param string $entityType e.g. 'incident', 'work_order'
     * @param string $entityId   Binary UUID of the entity
     */
    public function getForEntity(string $entityType, string $entityId): array
    {
        $sql = "SELECT a.*, BIN_TO_UUID(a.id) as uuid_str, u.full_name as uploader_name
                FROM `{$this->table}` a
                JOIN `users` u ON a.uploader_id = u.id
                WHERE a.entity_type = :entityType 
                  AND a.entity_id = :entityId 
                  AND a.deleted_at IS NULL
                ORDER BY a.created_at ASC";
                
        return $this->execute($sql, [
            'entityType' => $entityType,
            'entityId'   => $entityId
        ])->fetchAll();
    }
}
