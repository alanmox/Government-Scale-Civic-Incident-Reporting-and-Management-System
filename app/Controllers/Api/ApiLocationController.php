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

    public function regions(): void
    {
        $response = new Response();
        $stmt = $this->pdo->query("SELECT HEX(id) as id, name FROM regions ORDER BY name ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->apiSuccess($data);
    }

    public function districts(): void
    {
        $request = new Request();
        $response = new Response();
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

    public function wards(): void
    {
        $request = new Request();
        $response = new Response();
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

    public function villages(): void
    {
        $request = new Request();
        $response = new Response();
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
