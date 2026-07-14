<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Database\Connection;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define('CONFIG_PATH', __DIR__ . '/config');

$pdo = Connection::getInstance()->getPdo();

$sql = file_get_contents(__DIR__ . '/database/migrations/20260101_000010_create_tanzania_locations_table.sql');
$pdo->exec($sql);

echo "Migrations executed successfully.\n";

require_once __DIR__ . '/database/seeds/tanzania_locations.php';
