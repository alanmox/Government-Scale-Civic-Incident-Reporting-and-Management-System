<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\IncidentRepository;
use App\Repositories\WorkOrderRepository;
use App\Utilities\UUIDHelper;
use PDO;

final class DashboardService extends BaseService
{
    private IncidentRepository $incidentRepo;
    private WorkOrderRepository $woRepo;

    public function __construct(IncidentRepository $incidentRepo, WorkOrderRepository $woRepo)
    {
        parent::__construct();
        $this->incidentRepo = $incidentRepo;
        $this->woRepo = $woRepo;
    }

    /**
     * Get analytics for a citizen dashboard.
     */
    public function getCitizenStats(string $citizenUuid): array
    {
        $binCitizenId = UUIDHelper::toBinary($citizenUuid);
        $pdo = $this->incidentRepo->getConnection();

        // 1. Overall counts by status
        $sql = "SELECT status, COUNT(*) as count 
                FROM incidents 
                WHERE citizen_id = :citizenId AND deleted_at IS NULL 
                GROUP BY status";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['citizenId' => $binCitizenId]);
        $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $total = array_sum($statusCounts);
        $resolved = ($statusCounts['resolved'] ?? 0) + ($statusCounts['closed'] ?? 0) + ($statusCounts['archived'] ?? 0);
        $inProgress = ($statusCounts['assigned'] ?? 0) + ($statusCounts['in_progress'] ?? 0);
        $pending = ($statusCounts['draft'] ?? 0) + ($statusCounts['submitted'] ?? 0) + ($statusCounts['received'] ?? 0) + ($statusCounts['verified'] ?? 0);

        // 2. Recent incidents (limit 5)
        $recent = $this->incidentRepo->findByCitizen($binCitizenId, 5, 0);

        return [
            'total'       => $total,
            'resolved'    => $resolved,
            'in_progress' => $inProgress,
            'pending'     => $pending,
            'recent'      => $recent
        ];
    }

    /**
     * Get analytics for an officer dashboard.
     */
    public function getOfficerStats(string $officerUuid): array
    {
        $binOfficerId = UUIDHelper::toBinary($officerUuid);
        $pdo = $this->incidentRepo->getConnection();

        // 1. Work Orders counts by status
        $sql = "SELECT status, COUNT(*) as count 
                FROM work_orders 
                WHERE officer_id = :officerId AND deleted_at IS NULL 
                GROUP BY status";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['officerId' => $binOfficerId]);
        $woCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $totalWo = array_sum($woCounts);
        $completedWo = $woCounts['completed'] ?? 0;
        $activeWo = ($woCounts['pending'] ?? 0) + ($woCounts['in_progress'] ?? 0) + ($woCounts['on_hold'] ?? 0);

        // 2. SLA Breaches (Assigned incidents past SLA)
        $sqlSla = "SELECT COUNT(*) FROM incidents 
                   WHERE assigned_officer_id = :officerId 
                     AND status NOT IN ('resolved', 'closed', 'archived')
                     AND sla_due_at < NOW() 
                     AND deleted_at IS NULL";
        $stmtSla = $pdo->prepare($sqlSla);
        $stmtSla->execute(['officerId' => $binOfficerId]);
        $slaBreaches = (int) $stmtSla->fetchColumn();

        // 3. Recent Work Orders
        $recent = $this->woRepo->findByOfficer($binOfficerId, 5, 0);

        return [
            'total_wo'     => $totalWo,
            'completed_wo' => $completedWo,
            'active_wo'    => $activeWo,
            'sla_breaches' => $slaBreaches,
            'recent_wo'    => $recent
        ];
    }
    
    /**
     * Get generalized system stats for Super Admin.
     */
    public function getSystemStats(): array
    {
        $pdo = $this->incidentRepo->getConnection();

        $sql = "SELECT status, COUNT(*) as count FROM incidents WHERE deleted_at IS NULL GROUP BY status";
        $statusCounts = $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $sqlUsers = "SELECT COUNT(*) FROM users WHERE deleted_at IS NULL";
        $totalUsers = (int) $pdo->query($sqlUsers)->fetchColumn();
        
        $totalIncidents = array_sum($statusCounts);
        $resolved = ($statusCounts['resolved'] ?? 0) + ($statusCounts['closed'] ?? 0) + ($statusCounts['archived'] ?? 0);

        return [
            'total_incidents' => $totalIncidents,
            'resolved'        => $resolved,
            'total_users'     => $totalUsers,
            'status_breakdown'=> $statusCounts
        ];
    }
}
