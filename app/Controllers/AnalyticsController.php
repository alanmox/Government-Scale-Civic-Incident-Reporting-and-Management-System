<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\IncidentRepository;
use App\Services\DashboardService;
use App\Services\WorkOrderService;
use App\Repositories\WorkOrderRepository;
use PDO;

/**
 * AnalyticsController — System-wide KPI analytics for supervisors and admins.
 */
final class AnalyticsController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $pdo = \App\Database\Connection::getInstance()->getPdo();

        // Incidents per category (top 10)
        $byCategory = $pdo->query(
            "SELECT c.name, COUNT(i.id) as total
             FROM incidents i
             JOIN incident_categories c ON i.category_id = c.id
             WHERE i.deleted_at IS NULL
             GROUP BY c.name ORDER BY total DESC LIMIT 10"
        )->fetchAll(PDO::FETCH_ASSOC);

        // Incidents per status
        $byStatus = $pdo->query(
            "SELECT status, COUNT(*) as total FROM incidents WHERE deleted_at IS NULL GROUP BY status"
        )->fetchAll(PDO::FETCH_ASSOC);

        // Daily trend — last 30 days
        $trend = $pdo->query(
            "SELECT DATE(created_at) as day, COUNT(*) as count
             FROM incidents
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND deleted_at IS NULL
             GROUP BY day ORDER BY day ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        // Agency performance: avg resolution time in hours
        $agencyPerf = $pdo->query(
            "SELECT a.name as agency, 
                    ROUND(AVG(TIMESTAMPDIFF(HOUR, i.created_at, i.updated_at)), 1) as avg_hours,
                    COUNT(i.id) as total
             FROM incidents i
             JOIN agencies a ON i.agency_id = a.id
             WHERE i.status IN ('resolved','closed') AND i.deleted_at IS NULL
             GROUP BY a.name ORDER BY avg_hours ASC LIMIT 10"
        )->fetchAll(PDO::FETCH_ASSOC);

        // SLA breach count
        $slaBreaches = (int) $pdo->query(
            "SELECT COUNT(*) FROM incidents 
             WHERE sla_due_at < NOW() AND status NOT IN ('resolved','closed','archived') AND deleted_at IS NULL"
        )->fetchColumn();

        $this->view('analytics/index', [
            'pageTitle'   => 'Analytics & KPIs',
            'breadcrumbs' => [['label' => 'Analytics']],
            'byCategory'  => $byCategory,
            'byStatus'    => $byStatus,
            'trend'       => $trend,
            'agencyPerf'  => $agencyPerf,
            'slaBreaches' => $slaBreaches
        ]);
    }
}
