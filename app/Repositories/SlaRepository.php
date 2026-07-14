<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use App\Models\SlaDefinition;
use App\Utilities\UUIDHelper;

final class SlaRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'sla_definitions';
    }

    /**
     * Gets all SLA definitions joined with category names.
     */
    public function getAllWithCategories(): array
    {
        $sql = "
            SELECT 
                s.id, s.category_id, s.priority, s.resolve_hours, s.escalate_hours,
                c.name as category_name
            FROM {$this->table} s
            JOIN incident_categories c ON s.category_id = c.id
            WHERE s.deleted_at IS NULL AND c.deleted_at IS NULL
            ORDER BY c.name ASC, s.priority ASC
        ";

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convert binary IDs to string for the view
        return array_map(function($row) {
            $row['id'] = UUIDHelper::toString($row['id']);
            $row['category_id'] = UUIDHelper::toString($row['category_id']);
            return $row;
        }, $rows);
    }
    
    /**
     * Finds a specific SLA definition for a category and priority.
     */
    public function findByCategoryAndPriority(string $categoryId, string $priority): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = :category_id AND priority = :priority AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':category_id', UUIDHelper::toBinary($categoryId), PDO::PARAM_LOB);
        $stmt->bindValue(':priority', $priority, PDO::PARAM_STR);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        
        $row['id'] = UUIDHelper::toString($row['id']);
        $row['category_id'] = UUIDHelper::toString($row['category_id']);
        return $row;
    }
}
