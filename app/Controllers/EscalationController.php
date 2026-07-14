<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

final class EscalationController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $pdo = \App\Database\Connection::getInstance()->getPdo();

        // All incidents that have breached SLA or have been open > 14 days
        $escalated = $pdo->query(
            "SELECT i.reference_number, i.title, i.status, i.priority,
                    i.sla_due_at, i.created_at,
                    BIN_TO_UUID(i.id) as uuid_str,
                    c.name as category_name,
                    u.full_name as citizen_name
             FROM incidents i
             LEFT JOIN incident_categories c ON i.category_id = c.id
             LEFT JOIN users u ON i.citizen_id = u.id
             WHERE i.deleted_at IS NULL
               AND i.status NOT IN ('resolved','closed','archived')
               AND (
                   i.sla_due_at < NOW()
                   OR i.created_at < DATE_SUB(NOW(), INTERVAL 14 DAY)
               )
             ORDER BY i.sla_due_at ASC, i.priority DESC
             LIMIT 100"
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->view('escalations/index', [
            'pageTitle'   => 'Escalated Incidents',
            'breadcrumbs' => [['label' => 'Escalations']],
            'escalated'   => $escalated
        ]);
    }
}
