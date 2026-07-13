<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Database\Connection;

$pdo = Connection::getInstance()->getPdo();
$migrationsDir = __DIR__ . '/migrations';

echo "Running migrations...\n";

// Ensure migrations table exists first
$migrationsTableFile = $migrationsDir . '/20260101_000000_create_migrations_table.sql';
if (file_exists($migrationsTableFile)) {
    $pdo->exec(file_get_contents($migrationsTableFile));
}

// Get already executed migrations
$stmt = $pdo->query("SELECT migration FROM migrations");
$executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get all .sql files in migrations dir
$files = glob($migrationsDir . '/*.sql');
sort($files);

$batchStmt = $pdo->query("SELECT MAX(batch) FROM migrations");
$batch = (int) $batchStmt->fetchColumn() + 1;

$count = 0;
foreach ($files as $file) {
    $filename = basename($file);
    if ($filename === '20260101_000000_create_migrations_table.sql') continue;
    
    if (!in_array($filename, $executed)) {
        echo "Migrating: {$filename}\n";
        
        $sql = file_get_contents($file);
        
        try {
            $pdo->beginTransaction();
            
            // Execute statements individually if separated by ';'
            // A simple exec is often enough unless there are delimiters, but for robust schema parsing:
            $pdo->exec($sql);
            
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$filename, $batch]);
            
            $pdo->commit();
            $count++;
            echo "Migrated:  {$filename}\n";
        } catch (\Exception $e) {
            $pdo->rollBack();
            echo "Error migrating {$filename}: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

if ($count === 0) {
    echo "Nothing to migrate.\n";
} else {
    echo "Successfully migrated {$count} files.\n";
}
