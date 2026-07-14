<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

final class MapController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $pdo = \App\Database\Connection::getInstance()->pdo();

        // Fetch all geolocated incidents with coordinates
        $incidents = $pdo->query(
            "SELECT BIN_TO_UUID(i.id) as uuid_str, i.reference_number, i.title, i.status,
                    i.latitude, i.longitude, i.priority,
                    c.name as category_name
             FROM incidents i
             LEFT JOIN incident_categories c ON i.category_id = c.id
             WHERE i.deleted_at IS NULL
               AND i.latitude IS NOT NULL
               AND i.longitude IS NOT NULL
             ORDER BY i.created_at DESC
             LIMIT 500"
        )->fetchAll(PDO::FETCH_ASSOC);

        $this->view('map/index', [
            'pageTitle'   => 'Incident Map',
            'breadcrumbs' => [['label' => 'Incident Map']],
            'incidents'   => $incidents
        ]);
    }
}
