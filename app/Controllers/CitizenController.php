<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

final class CitizenController extends BaseController
{
    /** My Impact — gamification stats for the citizen. */
    public function impact(): void
    {
        $this->requireAuth();

        $pdo = \App\Database\Connection::getInstance()->getPdo();
        $binId = \App\Utilities\UUIDHelper::toBinary($this->session->userId());

        $stats = $pdo->prepare(
            "SELECT
                COUNT(*) as total_reports,
                SUM(status IN ('resolved','closed','archived')) as resolved,
                SUM(status = 'rejected') as rejected,
                SUM(status NOT IN ('resolved','closed','archived','rejected')) as active
             FROM incidents WHERE citizen_id = :id AND deleted_at IS NULL"
        );
        $stats->execute([':id' => $binId]);
        $data = $stats->fetch(PDO::FETCH_ASSOC);

        // Resolution rate
        $data['resolution_rate'] = $data['total_reports'] > 0
            ? round(($data['resolved'] / $data['total_reports']) * 100)
            : 0;

        $this->view('citizen/impact', [
            'pageTitle'   => 'My Impact',
            'breadcrumbs' => [['label' => 'My Impact']],
            'stats'       => $data
        ]);
    }

    /** Notification preferences self-service page. */
    public function notificationSettings(): void
    {
        $this->requireAuth();
        $this->view('citizen/notification_settings', [
            'pageTitle'   => 'Alert Settings',
            'breadcrumbs' => [['label' => 'Alert Settings']]
        ]);
    }

    /** Bookmarked community reports. */
    public function bookmarks(): void
    {
        $this->requireAuth();
        $this->view('citizen/bookmarks', [
            'pageTitle'   => 'Bookmarks',
            'breadcrumbs' => [['label' => 'Bookmarks']],
            'bookmarks'   => []
        ]);
    }

    /** Updates inbox — officer messages on citizen's reports. */
    public function updates(): void
    {
        $this->requireAuth();
        $pdo = \App\Database\Connection::getInstance()->getPdo();
        $binId = \App\Utilities\UUIDHelper::toBinary($this->session->userId());

        // Fetch work order updates on incidents owned by this citizen
        $updates = $pdo->prepare(
            "SELECT wou.notes, wou.progress_percent, wou.is_internal, wou.created_at,
                    i.reference_number, BIN_TO_UUID(i.id) as incident_uuid,
                    u.full_name as officer_name
             FROM work_order_updates wou
             JOIN work_orders wo ON wou.work_order_id = wo.id
             JOIN incidents i ON wo.incident_id = i.id
             LEFT JOIN users u ON wou.officer_id = u.id
             WHERE i.citizen_id = :citizenId AND wou.is_internal = 0
             ORDER BY wou.created_at DESC LIMIT 50"
        );
        $updates->execute([':citizenId' => $binId]);

        $this->view('citizen/updates', [
            'pageTitle'   => 'Updates Inbox',
            'breadcrumbs' => [['label' => 'Updates Inbox']],
            'updates'     => $updates->fetchAll()
        ]);
    }
}
