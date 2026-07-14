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
        
        // Remove comment lines (-- style) then split by semicolons
        $lines = explode("\n", $sql);
        $cleanLines = array_filter($lines, fn(string $line): bool => !str_starts_with(trim($line), '--'));
        $sql = implode("\n", $cleanLines);
        
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn(string $s): bool => $s !== ''
        );
        
        // DDL (CREATE TABLE) auto-commits in MySQL, so we run each statement
        // individually and track in the migrations table after all succeed.
        try {
            foreach ($statements as $i => $statement) {
                $pdo->exec($statement);
            }
            
            // Record the migration as executed
            $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
            $stmt->execute([$filename, $batch]);
            
            $count++;
            echo "Migrated:  {$filename}\n";
        } catch (\Exception $e) {
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
