<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEWS_PATH', BASE_PATH . '/views');
define('RESOURCES_PATH', BASE_PATH . '/resources');

require_once BASE_PATH . '/vendor/autoload.php';
$app = require_once BASE_PATH . '/bootstrap/app.php';

use App\Database\Connection;
use App\Utilities\UUIDHelper;
use App\Utilities\PasswordHasher;

$pdo = Connection::getInstance()->getPdo();

echo "Seeding basic data...\n";

try {
    $pdo->beginTransaction();

    // 1. Core Roles
    $roles = [
        ['name' => 'Super Administrator', 'slug' => 'super_admin', 'desc' => 'Full system access', 'system' => 1],
        ['name' => 'National Administrator', 'slug' => 'national_admin', 'desc' => 'National oversight', 'system' => 1],
        ['name' => 'Agency Administrator', 'slug' => 'agency_admin', 'desc' => 'Agency management', 'system' => 1],
        ['name' => 'Agency Officer', 'slug' => 'agency_officer', 'desc' => 'Handles agency incidents', 'system' => 1],
        ['name' => 'Regional Officer', 'slug' => 'regional_officer', 'desc' => 'Regional oversight', 'system' => 1],
        ['name' => 'District Officer', 'slug' => 'district_officer', 'desc' => 'District oversight', 'system' => 1],
        ['name' => 'Ward Officer', 'slug' => 'ward_officer', 'desc' => 'Ward operations', 'system' => 1],
        ['name' => 'Citizen', 'slug' => 'citizen', 'desc' => 'Public user', 'system' => 1],
    ];

    $roleIds = [];
    $stmtRole = $pdo->prepare("INSERT IGNORE INTO roles (id, name, slug, description, is_system) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($roles as $r) {
        $id = UUIDHelper::generate();
        $binId = UUIDHelper::toBinary($id);
        $roleIds[$r['slug']] = $binId;
        $stmtRole->execute([$binId, $r['name'], $r['slug'], $r['desc'], $r['system']]);
    }

    // 2. Base Permissions
    $permissions = [
        ['name' => 'Create Incident', 'slug' => 'incident.create', 'module' => 'incident'],
        ['name' => 'View Incidents', 'slug' => 'incident.view', 'module' => 'incident'],
        ['name' => 'Verify Incident', 'slug' => 'incident.verify', 'module' => 'incident'],
        ['name' => 'Assign Incident', 'slug' => 'incident.assign', 'module' => 'incident'],
        ['name' => 'Resolve Incident', 'slug' => 'incident.resolve', 'module' => 'incident'],
        ['name' => 'Manage Users', 'slug' => 'user.manage', 'module' => 'system'],
        ['name' => 'Configure System', 'slug' => 'system.configure', 'module' => 'system'],
        ['name' => 'View Analytics', 'slug' => 'analytics.view', 'module' => 'system'],
    ];

    $permIds = [];
    $stmtPerm = $pdo->prepare("INSERT IGNORE INTO permissions (id, name, slug, module) VALUES (?, ?, ?, ?)");
    foreach ($permissions as $p) {
        $id = UUIDHelper::toBinary(UUIDHelper::generate());
        $permIds[$p['slug']] = $id;
        $stmtPerm->execute([$id, $p['name'], $p['slug'], $p['module']]);
    }

    // 3. Super Admin User
    $adminUuid = UUIDHelper::generate();
    $adminBinId = UUIDHelper::toBinary($adminUuid);
    $hash = PasswordHasher::hash('SuperSecret123!');

    $stmtUser = $pdo->prepare("
        INSERT IGNORE INTO users (id, uuid, full_name, username, email, password_hash, status) 
        VALUES (?, ?, 'System Administrator', 'admin', 'admin@gcirms.gov.tz', ?, 'active')
    ");
    $stmtUser->execute([$adminBinId, $adminUuid, $hash]);

    // Assign role
    $stmtUserRole = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)");
    $stmtUserRole->execute([$adminBinId, $roleIds['super_admin']]);

    $pdo->commit();
    echo "Seed complete.\n";

} catch (\Exception $e) {
    $pdo->rollBack();
    echo "Seeding failed: " . $e->getMessage() . "\n";
}
