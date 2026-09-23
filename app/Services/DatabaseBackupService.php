<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupService
{
    protected string $configFile;

    public function __construct()
    {
        $this->configFile = storage_path('app/backup_settings.json');
    }

    /**
     * Get the configured backup directory path.
     */
    public function getBackupDirectory(): string
    {
        if (File::exists($this->configFile)) {
            $data = json_decode(File::get($this->configFile), true);
            if (!empty($data['backup_directory']) && is_string($data['backup_directory'])) {
                $dir = trim($data['backup_directory']);
                if (!empty($dir)) {
                    return $dir;
                }
            }
        }

        return base_path('sauvegardes');
    }

    /**
     * Save the configured backup directory.
     */
    public function setBackupDirectory(string $path): string
    {
        $normalized = trim($path);
        // Remove trailing slashes
        $normalized = rtrim($normalized, "\\/");

        if (!File::isDirectory($normalized)) {
            File::makeDirectory($normalized, 0755, true, true);
        }

        $data = ['backup_directory' => $normalized, 'updated_at' => now()->toIso8601String()];
        
        $settingsDir = storage_path('app');
        if (!File::isDirectory($settingsDir)) {
            File::makeDirectory($settingsDir, 0755, true, true);
        }

        File::put($this->configFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $normalized;
    }

    /**
     * Locate mysqldump.exe in Laragon or system PATH.
     */
    public function getMysqldumpPath(): ?string
    {
        // 1. Check Laragon default MySQL installations
        $laragonMysqlBase = 'C:\\laragon\\bin\\mysql';
        if (is_dir($laragonMysqlBase)) {
            $dirs = glob($laragonMysqlBase . '\\mysql-*', GLOB_ONLYDIR);
            if (!empty($dirs)) {
                // Take latest version
                rsort($dirs);
                foreach ($dirs as $dir) {
                    $exe = $dir . '\\bin\\mysqldump.exe';
                    if (file_exists($exe)) {
                        return $exe;
                    }
                }
            }
        }

        // 2. Check if mysqldump is directly in PATH
        $pathOutput = @shell_exec('where mysqldump 2>nul');
        if (!empty($pathOutput)) {
            $lines = explode("\n", trim($pathOutput));
            if (!empty($lines[0]) && file_exists(trim($lines[0]))) {
                return trim($lines[0]);
            }
        }

        return null;
    }

    /**
     * Perform the database backup to the configured folder.
     */
    public function createBackup(?string $targetDir = null): array
    {
        $dir = $targetDir ?: $this->getBackupDirectory();

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        $timestamp = now()->format('Y-m-d_H\hi\ms');
        $filename = "paradou_backup_{$timestamp}.sql";
        $fullPath = rtrim($dir, "\\/") . DIRECTORY_SEPARATOR . $filename;

        // Try mysqldump first
        $mysqldump = $this->getMysqldumpPath();
        $dbName = config('database.connections.mysql.database', env('DB_DATABASE', 'msk_dry_plus'));
        $dbUser = config('database.connections.mysql.username', env('DB_USERNAME', 'root'));
        $dbPass = config('database.connections.mysql.password', env('DB_PASSWORD', ''));
        $dbHost = config('database.connections.mysql.host', env('DB_HOST', '127.0.0.1'));
        $dbPort = config('database.connections.mysql.port', env('DB_PORT', '3306'));

        $success = false;

        if ($mysqldump && file_exists($mysqldump)) {
            $passParam = !empty($dbPass) ? "--password=" . escapeshellarg($dbPass) : "";
            $cmd = sprintf(
                '"%s" -h %s -P %s -u %s %s --databases %s > "%s" 2>&1',
                $mysqldump,
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                $passParam,
                escapeshellarg($dbName),
                $fullPath
            );

            @shell_exec($cmd);

            if (file_exists($fullPath) && filesize($fullPath) > 500) {
                $success = true;
            }
        }

        // Fallback to robust PHP PDO SQL Dump if mysqldump failed or wasn't available
        if (!$success) {
            $this->dumpDatabaseViaPdo($fullPath, $dbName);
            if (file_exists($fullPath) && filesize($fullPath) > 500) {
                $success = true;
            }
        }

        if (!$success) {
            throw new \RuntimeException("Impossible de générer le fichier de sauvegarde. Vérifiez les droits d'écriture et la connexion MySQL.");
        }

        $bytes = filesize($fullPath);
        $sizeFormatted = $this->formatBytes($bytes);

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $fullPath,
            'size' => $sizeFormatted,
            'bytes' => $bytes,
            'created_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Fallback database dumper using PDO.
     */
    protected function dumpDatabaseViaPdo(string $filePath, string $dbName): void
    {
        $pdo = DB::connection()->getPdo();
        $tables = DB::connection()->select('SHOW TABLES');
        $dbKey = 'Tables_in_' . $dbName;

        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            throw new \RuntimeException("Impossible d'ouvrir le fichier en écriture : {$filePath}");
        }

        fwrite($handle, "-- --------------------------------------------------------\n");
        fwrite($handle, "-- Paradou POS Database Backup (PHP PDO Engine)\n");
        fwrite($handle, "-- Base : {$dbName}\n");
        fwrite($handle, "-- Date : " . now()->format('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- --------------------------------------------------------\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

        foreach ($tables as $tableObj) {
            $tableArr = (array) $tableObj;
            $tableName = reset($tableArr);

            // Table Structure
            $createTable = DB::connection()->select("SHOW CREATE TABLE `{$tableName}`");
            if (!empty($createTable)) {
                $createArr = (array) $createTable[0];
                $createSql = $createArr['Create Table'] ?? reset($createArr);
                fwrite($handle, "-- Table: `{$tableName}`\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                fwrite($handle, $createSql . ";\n\n");
            }

            // Table Data
            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                fwrite($handle, "-- Données pour `{$tableName}`\n");
                foreach ($rows->chunk(100) as $chunk) {
                    $insertValues = [];
                    foreach ($chunk as $row) {
                        $rowArr = (array) $row;
                        $escapedValues = array_map(function ($val) use ($pdo) {
                            if (is_null($val)) return 'NULL';
                            return $pdo->quote($val);
                        }, array_values($rowArr));
                        $insertValues[] = '(' . implode(', ', $escapedValues) . ')';
                    }

                    $cols = array_map(fn($c) => "`{$c}`", array_keys((array) $rows->first()));
                    $sql = "INSERT INTO `{$tableName}` (" . implode(', ', $cols) . ") VALUES \n" . implode(",\n", $insertValues) . ";\n\n";
                    fwrite($handle, $sql);
                }
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($handle, "-- Fin de la sauvegarde\n");
        fclose($handle);
    }

    /**
     * Get list of all SQL backup files in the configured directory.
     */
    public function getBackupsList(): array
    {
        $dir = $this->getBackupDirectory();
        if (!File::isDirectory($dir)) {
            return [];
        }

        $files = File::files($dir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => $this->formatBytes($file->getSize()),
                    'bytes' => $file->getSize(),
                    'created_at' => date('d/m/Y H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime(),
                ];
            }
        }

        // Sort newest first
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Format bytes to human readable format.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
