<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Searchable;
use PDO;

final class WorkOrderRepository extends BaseRepository implements Searchable
{
    protected string $table      = 'work_orders';
    protected string $primaryKey = 'id';
    protected bool   $softDeletes = true;

    /**
     * Get work orders assigned to a specific officer.
     */
    public function findByOfficer(string $officerId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT w.*, BIN_TO_UUID(w.id) AS uuid_str, 
                       i.reference_number AS incident_ref, i.title AS incident_title,
                       BIN_TO_UUID(i.id) AS incident_uuid_str
                FROM `{$this->table}` w
                JOIN `incidents` i ON w.incident_id = i.id
                WHERE w.officer_id = :officerId AND w.deleted_at IS NULL
                ORDER BY w.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':officerId', $officerId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Generates a unique reference number.
     * Format: WO-YYYYMM-XXXX
     */
    public function generateReferenceNumber(): string
    {
        $prefix = 'WO-' . date('Ym') . '-';
        
        $sql = "SELECT reference_number FROM `{$this->table}` 
                WHERE reference_number LIKE :prefix 
                ORDER BY reference_number DESC LIMIT 1";
                
        $stmt = $this->execute($sql, ['prefix' => $prefix . '%']);
        $lastRef = $stmt->fetchColumn();
        
        if ($lastRef) {
            $parts = explode('-', $lastRef);
            $sequence = (int) end($parts);
            $sequence++;
        } else {
            $sequence = 1;
        }
        
        return $prefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get updates for a work order.
     */
    public function getUpdates(string $workOrderId): array
    {
        $sql = "SELECT u.*, BIN_TO_UUID(u.id) as uuid_str, o.full_name as officer_name 
                FROM `work_order_updates` u
                JOIN `users` o ON u.officer_id = o.id
                WHERE u.work_order_id = :woId
                  AND u.deleted_at IS NULL
                ORDER BY u.created_at DESC";
        return $this->execute($sql, ['woId' => $workOrderId])->fetchAll();
    }

    /**
     * Get updates for incidents owned by a specific citizen.
     */
    public function getUpdatesForCitizen(string $citizenId, int $limit = 50): array
    {
        $sql = "SELECT wou.notes, wou.progress_percent, wou.is_internal, wou.created_at,
                       i.reference_number, BIN_TO_UUID(i.id) as incident_uuid,
                       u.full_name as officer_name
                FROM `work_order_updates` wou
                JOIN `work_orders` wo ON wou.work_order_id = wo.id
                JOIN `incidents` i ON wo.incident_id = i.id
                LEFT JOIN `users` u ON wou.officer_id = u.id
                WHERE i.citizen_id = :citizenId 
                  AND wou.is_internal = 0
                  AND wou.deleted_at IS NULL
                ORDER BY wou.created_at DESC LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':citizenId', $citizenId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Add a progress update.
     */
    public function addUpdate(array $data): void
    {
        $sql = "INSERT INTO `work_order_updates` 
                (id, work_order_id, officer_id, progress_percent, notes, is_internal)
                VALUES (:id, :workOrderId, :officerId, :progress, :notes, :isInternal)";
                
        $this->execute($sql, [
            'id'          => $data['id'],
            'workOrderId' => $data['work_order_id'],
            'officerId'   => $data['officer_id'],
            'progress'    => $data['progress_percent'],
            'notes'       => $data['notes'],
            'isInternal'  => $data['is_internal'] ?? 1,
        ]);
    }

    // ── Searchable Interface (Stubbed for now) ────────────────────────────────
    public function search(array $criteria, int $limit = 20, int $offset = 0): array { return []; }
    public function searchCount(array $criteria): int { return 0; }
}
