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

    public function findById(string $id): ?array
    {
        $sql = "SELECT i.*, BIN_TO_UUID(i.id) AS uuid_str, c.name as category_name, u.full_name as citizen_name
                FROM `{$this->table}` i
                LEFT JOIN `incident_categories` c ON i.category_id = c.id
                LEFT JOIN `users` u ON i.citizen_id = u.id
                WHERE i.`{$this->primaryKey}` = :id"
             . ($this->softDeletes ? ' AND i.deleted_at IS NULL' : '');

        $stmt = $this->execute($sql, ['id' => $id]);
        $row  = $stmt->fetch();

        return $row ?: null;
    }

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

    /**
     * Returns citizen impact stats.
     */
    public function getCitizenStats(string $citizenId): array
    {
        $sql = "SELECT
                    COUNT(*) as total_reports,
                    SUM(status IN ('resolved','closed','archived')) as resolved,
                    SUM(status = 'rejected') as rejected,
                    SUM(status NOT IN ('resolved','closed','archived','rejected')) as active
                FROM `{$this->table}` WHERE citizen_id = :citizenId AND deleted_at IS NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':citizenId', $citizenId);
        $stmt->execute();
        $data = $stmt->fetch();
        $data['resolution_rate'] = $data['total_reports'] > 0
            ? round(($data['resolved'] / $data['total_reports']) * 100)
            : 0;
        return $data;
    }

    /**
     * Returns escalated incidents (SLA breached or open > 14 days).
     */
    public function findEscalated(int $limit = 100): array
    {
        $sql = "SELECT i.reference_number, i.title, i.status, i.priority,
                       i.sla_due_at, i.created_at,
                       BIN_TO_UUID(i.id) as uuid_str,
                       c.name as category_name,
                       u.full_name as citizen_name
                FROM `{$this->table}` i
                LEFT JOIN `incident_categories` c ON i.category_id = c.id
                LEFT JOIN `users` u ON i.citizen_id = u.id
                WHERE i.deleted_at IS NULL
                  AND i.status NOT IN ('resolved','closed','archived')
                  AND (
                      i.sla_due_at < NOW()
                      OR i.created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
                  )
                ORDER BY i.sla_due_at ASC, i.priority DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Returns geolocated incidents for the map view.
     */
    public function findGeolocated(int $limit = 500): array
    {
        $sql = "SELECT BIN_TO_UUID(i.id) as uuid_str, i.reference_number, i.title, i.status,
                       i.latitude, i.longitude, i.priority,
                       c.name as category_name
                FROM `{$this->table}` i
                LEFT JOIN `incident_categories` c ON i.category_id = c.id
                WHERE i.deleted_at IS NULL
                  AND i.latitude IS NOT NULL
                  AND i.longitude IS NOT NULL
                ORDER BY i.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Returns count of SLA breached incidents.
     */
    public function countSlaBreaches(): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}` 
                WHERE sla_due_at < NOW() 
                  AND status NOT IN ('resolved','closed','archived') 
                  AND deleted_at IS NULL";
        return (int) $this->pdo->query($sql)->fetchColumn();
    }

    /**
     * Returns incident stats per category (top N).
     */
    public function countByCategory(int $limit = 10): array
    {
        $sql = "SELECT c.name, COUNT(i.id) as total
                FROM `{$this->table}` i
                JOIN `incident_categories` c ON i.category_id = c.id
                WHERE i.deleted_at IS NULL
                GROUP BY c.name ORDER BY total DESC LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Returns incident count per status.
     */
    public function countByStatus(): array
    {
        $sql = "SELECT status, COUNT(*) as total 
                FROM `{$this->table}` WHERE deleted_at IS NULL 
                GROUP BY status";
        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * Returns daily incident count for the last N days.
     */
    public function countByDay(int $days = 30): array
    {
        $sql = "SELECT DATE(created_at) as day, COUNT(*) as count
                FROM `{$this->table}`
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY) 
                  AND deleted_at IS NULL
                GROUP BY day ORDER BY day ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Returns average resolution time per agency.
     */
    public function avgResolutionByAgency(int $limit = 10): array
    {
        $sql = "SELECT a.name as agency, 
                       ROUND(AVG(TIMESTAMPDIFF(HOUR, i.created_at, i.updated_at)), 1) as avg_hours,
                       COUNT(i.id) as total
                FROM `{$this->table}` i
                JOIN `agencies` a ON i.agency_id = a.id
                WHERE i.status IN ('resolved','closed') AND i.deleted_at IS NULL
                GROUP BY a.name ORDER BY avg_hours ASC LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
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
