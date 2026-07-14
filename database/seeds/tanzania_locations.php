<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Database\Connection;
use App\Utilities\UUIDHelper;

// Load env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', __DIR__ . '/../../config');
}

$pdo = Connection::getInstance()->getPdo();

// Clean up existing
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE villages;");
$pdo->exec("TRUNCATE TABLE wards;");
$pdo->exec("TRUNCATE TABLE districts;");
$pdo->exec("TRUNCATE TABLE regions;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

$regions = [
    'Dar es Salaam' => [
        'Ilala' => [
            'Kariakoo' => ['Kariakoo North', 'Kariakoo South'],
            'Upanga' => ['Upanga East', 'Upanga West'],
        ],
        'Kinondoni' => [
            'Oysterbay' => ['Masaki', 'Toure'],
            'Magomeni' => ['Makurumla', 'Idrisa'],
        ]
    ],
    'Arusha' => [
        'Arusha City' => [
            'Sombetini' => ['Sombetini Kati', 'Simanjiro'],
            'Kati' => ['Bondeni', 'Kaloleni'],
        ]
    ],
    'Dodoma' => [
        'Dodoma Urban' => [
            'Majengo' => ['Majengo A', 'Majengo B'],
            'Tambukareli' => ['Railway', 'Kikuyu'],
        ]
    ],
    'Mwanza' => [
        'Nyamagana' => [
            'Nyegezi' => ['Nyegezi Kati', 'Malimbe'],
            'Mkolani' => ['Buhongwa', 'Lwanhima'],
        ]
    ]
];

foreach ($regions as $regionName => $districts) {
    $regionId = UUIDHelper::generate();
    $stmt = $pdo->prepare("INSERT INTO regions (id, name) VALUES (?, ?)");
    $stmt->execute([UUIDHelper::toBinary($regionId), $regionName]);

    foreach ($districts as $districtName => $wards) {
        $districtId = UUIDHelper::generate();
        $stmt = $pdo->prepare("INSERT INTO districts (id, region_id, name) VALUES (?, ?, ?)");
        $stmt->execute([UUIDHelper::toBinary($districtId), UUIDHelper::toBinary($regionId), $districtName]);

        foreach ($wards as $wardName => $villages) {
            $wardId = UUIDHelper::generate();
            $stmt = $pdo->prepare("INSERT INTO wards (id, district_id, name) VALUES (?, ?, ?)");
            $stmt->execute([UUIDHelper::toBinary($wardId), UUIDHelper::toBinary($districtId), $wardName]);

            foreach ($villages as $villageName) {
                $villageId = UUIDHelper::generate();
                $stmt = $pdo->prepare("INSERT INTO villages (id, ward_id, name) VALUES (?, ?, ?)");
                $stmt->execute([UUIDHelper::toBinary($villageId), UUIDHelper::toBinary($wardId), $villageName]);
            }
        }
    }
}

echo "Tanzania locations seeded successfully!\n";
