<?php
/**
 * Smart Khata - Automated and Manual Backup & Restore Service
 */

namespace SmartKhata\Services;

use SmartKhata\Database;
use DateTime;
use Exception;

class BackupService {
    public static function getBackupDir(): string {
        $dir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        return $dir;
    }

    /**
     * Generate complete SQL dump for MySQL or SQLite
     */
    public static function createBackup(string $type = 'manual'): array {
        $db = Database::getInstance();
        $pdo = $db->getPdo();
        $driver = $db->getDriver();

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "smart_khata_backup_{$timestamp}.sql";
        $filepath = self::getBackupDir() . '/' . $filename;

        $tables = [
            'settings',
            'admins',
            'customers',
            'invoices',
            'invoice_items',
            'payments',
            'payment_allocations',
            'ledger_entries',
            'adjustments',
            'login_logs',
            'backup_logs'
        ];

        $dump = "-- Smart Khata Full Database Backup\n";
        $dump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $dump .= "-- Driver: " . $driver . "\n\n";

        if ($driver === 'sqlite') {
            $dump .= "PRAGMA foreign_keys = OFF;\n\n";
            foreach ($tables as $table) {
                // Get CREATE TABLE statement
                $createRow = $db->fetchOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
                if (!$createRow || empty($createRow['sql'])) continue;

                $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $dump .= $createRow['sql'] . ";\n\n";

                // Dump data
                $rows = $db->fetchAll("SELECT * FROM `{$table}`");
                foreach ($rows as $row) {
                    $cols = array_keys($row);
                    $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
                    $escapedVals = array_map(function($v) use ($pdo) {
                        return $v === null ? "NULL" : $pdo->quote((string)$v);
                    }, array_values($row));

                    $dump .= "INSERT INTO `{$table}` (" . implode(', ', $escapedCols) . ") VALUES (" . implode(', ', $escapedVals) . ");\n";
                }
                $dump .= "\n";
            }
            $dump .= "PRAGMA foreign_keys = ON;\n";
        } else {
            // MySQL dump
            $dump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            foreach ($tables as $table) {
                try {
                    $createRow = $db->fetchOne("SHOW CREATE TABLE `{$table}`");
                    if (!$createRow) continue;
                    $createSql = $createRow['Create Table'] ?? '';

                    $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $dump .= $createSql . ";\n\n";

                    $rows = $db->fetchAll("SELECT * FROM `{$table}`");
                    foreach ($rows as $row) {
                        $cols = array_keys($row);
                        $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
                        $escapedVals = array_map(function($v) use ($pdo) {
                            return $v === null ? "NULL" : $pdo->quote((string)$v);
                        }, array_values($row));

                        $dump .= "INSERT INTO `{$table}` (" . implode(', ', $escapedCols) . ") VALUES (" . implode(', ', $escapedVals) . ");\n";
                    }
                    $dump .= "\n";
                } catch (\Throwable $e) {}
            }
            $dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        }

        if (file_put_contents($filepath, $dump) === false) {
            throw new Exception("Unable to write backup file to storage.");
        }

        $filesize = filesize($filepath);

        // Record in backup logs
        try {
            $db->execute(
                "INSERT INTO backup_logs (filename, filepath, filesize, backup_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?)",
                [$filename, $filepath, $filesize, $type, 'success', (new DateTime())->format('Y-m-d H:i:s')]
            );
        } catch (\Throwable $e) {}

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'filesize' => $filesize,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Restore database from uploaded SQL file
     */
    public static function restoreBackup(string $sqlFilePath): array {
        if (!file_exists($sqlFilePath)) {
            throw new Exception("Backup file not found.");
        }

        $sql = file_get_contents($sqlFilePath);
        if (empty($sql) || !str_contains($sql, 'Smart Khata')) {
            throw new Exception("The uploaded file does not appear to be a valid Smart Khata database backup.");
        }

        $db = Database::getInstance();
        $pdo = $db->getPdo();

        // Execute batch SQL statements
        $db->beginTransaction();
        try {
            $pdo->exec($sql);
            $db->commit();
            return ['success' => true, 'message' => "Database restored successfully."];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new Exception("Database restore failed: " . $e->getMessage());
        }
    }

    public static function listBackups(): array {
        $dir = self::getBackupDir();
        $files = glob($dir . '/*.sql');
        $backups = [];

        foreach ($files as $file) {
            $filename = basename($file);
            $backups[] = [
                'filename' => $filename,
                'filesize' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        return $backups;
    }
}
