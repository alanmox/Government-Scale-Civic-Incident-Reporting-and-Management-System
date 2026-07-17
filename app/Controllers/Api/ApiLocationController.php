<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Database\Connection;
use PDO;

final class ApiLocationController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance()->getPdo();
    }

    public function regions(Request $request, Response $response): void
    {
        $stmt = $this->pdo->query("SELECT HEX(id) as id, name FROM regions ORDER BY name ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->apiSuccess($data);
    }

    public function districts(Request $request, Response $response): void
    {
        $regionId = $request->query('region_id', '');
        if (empty($regionId)) {
            $response->apiError('region_id is required');
            return;
        }

        $stmt = $this->pdo->prepare("SELECT HEX(id) as id, name FROM districts WHERE region_id = UNHEX(:regionId) ORDER BY name ASC");
        $stmt->bindValue(':regionId', $regionId);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->apiSuccess($data);
    }

    public function wards(Request $request, Response $response): void
    {
        $districtId = $request->query('district_id', '');
        if (empty($districtId)) {
            $response->apiError('district_id is required');
            return;
        }

        $stmt = $this->pdo->prepare("SELECT HEX(id) as id, name FROM wards WHERE district_id = UNHEX(:districtId) ORDER BY name ASC");
        $stmt->bindValue(':districtId', $districtId);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->apiSuccess($data);
    }

    public function villages(Request $request, Response $response): void
    {
        $wardId = $request->query('ward_id', '');
        if (empty($wardId)) {
            $response->apiError('ward_id is required');
            return;
        }

        $stmt = $this->pdo->prepare("SELECT HEX(id) as id, name FROM villages WHERE ward_id = UNHEX(:wardId) ORDER BY name ASC");
        $stmt->bindValue(':wardId', $wardId);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->apiSuccess($data);
    }
}
