<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Searchable;
use PDO;

final class IncidentRepository extends BaseRepository implements Searchable
{
    protected string $table      = 'incidents';
    protected string $primaryKey = 'id';
    protected bool   $softDeletes = true;

    /**
     * Get incidents for a specific citizen.
     */
    public function findByCitizen(string $citizenId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT i.*, BIN_TO_UUID(i.id) AS uuid_str, c.name as category_name
                FROM `{$this->table}` i
                LEFT JOIN `incident_categories` c ON i.category_id = c.id
                WHERE i.citizen_id = :citizenId AND i.deleted_at IS NULL
                ORDER BY i.reported_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':citizenId', $citizenId);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get count of incidents for a specific citizen.
     */
    public function countByCitizen(string $citizenId): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE citizen_id = :citizenId AND deleted_at IS NULL";
        $stmt = $this->execute($sql, ['citizenId' => $citizenId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Generates a unique reference number.
     * Format: INC-YYYYMM-XXXX
     */
    public function generateReferenceNumber(): string
    {
        $prefix = 'INC-' . date('Ym') . '-';
        
        // Find the highest number for this month
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
    
    // ── Searchable Interface ───────────────────────────────────────────────────

    public function search(array $criteria, int $limit = 20, int $offset = 0): array
    {
        [$sql, $params] = $this->buildSearchQuery($criteria);
        $sql .= ' ORDER BY i.reported_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function searchCount(array $criteria): int
    {
        [$sql, $params] = $this->buildSearchQuery($criteria, true);
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":{$key}", $val);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Fetch all incidents with optional status filter and pagination.
     */
    public function findFiltered(string $status = '', int $limit = 20, int $offset = 0): array
    {
        $criteria = [];
        if ($status !== '') {
            $criteria['status'] = $status;
        }

        [$sql, $params] = $this->buildSearchQuery($criteria);

        $sql .= ' ORDER BY i.reported_at DESC LIMIT :limit OFFSET :offset';
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(":$key", $val, $type);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function buildSearchQuery(array $criteria, bool $countOnly = false): array
    {
        $select = $countOnly
            ? "SELECT COUNT(*) FROM `{$this->table}` i"
            : "SELECT i.*, BIN_TO_UUID(i.id) AS uuid_str, c.name as category_name, u.full_name as citizen_name 
               FROM `{$this->table}` i
               LEFT JOIN `incident_categories` c ON i.category_id = c.id
               LEFT JOIN `users` u ON i.citizen_id = u.id";

        $where  = ['i.deleted_at IS NULL'];
        $params = [];

        if (!empty($criteria['q'])) {
            $where[]        = '(i.title LIKE :q OR i.reference_number LIKE :q)';
            $params['q']    = '%' . $criteria['q'] . '%';
        }

        if (!empty($criteria['status'])) {
            $where[]           = 'i.status = :status';
            $params['status']  = $criteria['status'];
        }

        if (!empty($criteria['priority'])) {
            $where[]             = 'i.priority = :priority';
            $params['priority']  = $criteria['priority'];
        }

        $sql = $select . ' WHERE ' . implode(' AND ', $where);
        return [$sql, $params];
    }
}
