<?php
declare(strict_types=1);

namespace App\Services;

final class BackupService extends BaseService
{
    private string $backupDir;

    public function __construct()
    {
        parent::__construct();
        // Fallback to a default path if APP_ROOT is not available, though it should be.
        $this->backupDir = (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__, 2)) . '/storage/backups';
        
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function getBackups(): array
    {
        $files = [];
        $iterator = new \DirectoryIterator($this->backupDir);
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'sql') {
                $files[] = [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'date' => $file->getMTime()
                ];
            }
        }

        // Sort descending by date
        usort($files, fn($a, $b) => $b['date'] <=> $a['date']);

        return $files;
    }

    public function createDatabaseBackup(): string
    {
        $dbConfig = require CONFIG_PATH . '/database.php';

        $host = escapeshellarg($dbConfig['host']);
        $port = escapeshellarg((string)$dbConfig['port']);
        $user = escapeshellarg($dbConfig['user']);
        $pass = escapeshellarg($dbConfig['pass']);
        $name = escapeshellarg($dbConfig['name']);

        $filename = 'backup_gcirms_' . date('Ymd_His') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;

        // Note: passing password on CLI might raise a warning, but this is a standard approach for simple backups
        $command = "mysqldump -h {$host} -P {$port} -u {$user} -p{$pass} {$name} > " . escapeshellarg($filepath) . " 2>&1";
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            // Clean up the file if it failed
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            throw new \RuntimeException("Database backup failed: " . implode("\n", $output));
        }

        return $filename;
    }

    public function getBackupPath(string $filename): ?string
    {
        $filepath = $this->backupDir . '/' . basename($filename);
        if (file_exists($filepath) && is_file($filepath)) {
            return $filepath;
        }
        return null;
    }
}
