<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

final class AuditLogController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $pdo = \App\Database\Connection::getInstance()->pdo();

        $page   = max(1, (int)($this->request->query('page') ?? 1));
        $limit  = 50;
        $offset = ($page - 1) * $limit;

        // Use workflow_logs as audit log — production system uses a dedicated audit_logs table
        $logs = $pdo->prepare(
            "SELECT wl.action, wl.from_status, wl.to_status, wl.comments, wl.created_at,
                    i.reference_number,
                    BIN_TO_UUID(i.id) as incident_uuid,
                    u.full_name as actor_name
             FROM workflow_logs wl
             JOIN incidents i ON wl.incident_id = i.id
             LEFT JOIN users u ON wl.actor_id = u.id
             ORDER BY wl.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $logs->bindValue(':limit', $limit, PDO::PARAM_INT);
        $logs->bindValue(':offset', $offset, PDO::PARAM_INT);
        $logs->execute();

        $total = (int) $pdo->query("SELECT COUNT(*) FROM workflow_logs")->fetchColumn();

        $this->view('admin/audit_logs', [
            'pageTitle'   => 'Audit Logs',
            'breadcrumbs' => [['label' => 'Administration'], ['label' => 'Audit Logs']],
            'logs'        => $logs->fetchAll(),
            'page'        => $page,
            'totalPages'  => (int) ceil($total / $limit)
        ]);
    }
}
