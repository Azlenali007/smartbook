<?php
/**
 * Smart Khata - Automated CLI Backup Script
 * Run via cron: 0 2 * * * /usr/bin/php /path/to/smart-khata/cli/backup.php
 */

if (php_sapi_name() !== 'cli') {
    die("Access denied: CLI execution only.\n");
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

use SmartKhata\Services\InstallerService;
use SmartKhata\Services\BackupService;

echo "[" . date('Y-m-d H:i:s') . "] Starting automated Smart Khata database backup...\n";

if (!InstallerService::isInstalled()) {
    echo "ERROR: Application is not installed yet. Aborting.\n";
    exit(1);
}

try {
    $res = BackupService::createBackup('scheduled');
    echo "SUCCESS: Backup created successfully!\n";
    echo "Filename: {$res['filename']}\n";
    echo "Size: " . number_format($res['filesize'] / 1024, 2) . " KB\n";
    echo "Path: {$res['filepath']}\n";
    exit(0);
} catch (\Throwable $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
    file_put_contents(
        SK_STORAGE . '/logs/backup.log',
        "[" . date('Y-m-d H:i:s') . "] Scheduled backup failed: " . $e->getMessage() . "\n",
        FILE_APPEND
    );
    exit(1);
}
