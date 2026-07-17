<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Utilities\UUIDHelper;
use PDO;

final class WorkflowLogRepository extends BaseRepository
{
    protected string $table       = 'workflow_logs';
    protected string $primaryKey  = 'id';
    protected bool   $softDeletes = false; // Immutable audit log

    /**
     * Records a state transition in the workflow log.
     */
    public function logTransition(
        string $incidentId, 
        string $actorId, 
        string $action, 
        string $fromStatus, 
        string $toStatus, 
        ?string $comments = null
    ): void {
        $id = UUIDHelper::toBinary(UUIDHelper::generate());
        
        $sql = "INSERT INTO `{$this->table}` (id, incident_id, actor_id, action, from_status, to_status, comments)
                VALUES (:id, :incidentId, :actorId, :action, :fromStatus, :toStatus, :comments)";
                
        $this->execute($sql, [
            'id'          => $id,
            'incidentId'  => $incidentId,
            'actorId'     => $actorId,
            'action'      => $action,
            'fromStatus'  => $fromStatus,
            'toStatus'    => $toStatus,
            'comments'    => $comments,
        ]);
    }

    /**
     * Fetches the workflow history for a specific incident.
     */
    public function getHistoryForIncident(string $incidentId): array
    {
        $sql = "SELECT w.*, BIN_TO_UUID(w.id) as uuid_str, u.full_name as actor_name, u.email as actor_email 
                FROM `{$this->table}` w
                JOIN `users` u ON w.actor_id = u.id
                WHERE w.incident_id = :incidentId
                ORDER BY w.created_at DESC";
                
        return $this->execute($sql, ['incidentId' => $incidentId])->fetchAll();
    }

    /**
     * Fetches audit log entries with incident and actor details.
     */
    public function findAuditLogs(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT wl.action, wl.from_status, wl.to_status, wl.comments, wl.created_at,
                       i.reference_number,
                       BIN_TO_UUID(i.id) as incident_uuid,
                       u.full_name as actor_name
                FROM `{$this->table}` wl
                JOIN `incidents` i ON wl.incident_id = i.id
                LEFT JOIN `users` u ON wl.actor_id = u.id
                ORDER BY wl.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }
}
