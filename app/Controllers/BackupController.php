<?php
/**
 * Smart Khata - Backup & Restore Controller
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\CSRF;
use SmartKhata\Services\BackupService;
use Exception;

class BackupController {
    public function index(): void {
        Auth::requireAuth();
        $backups = BackupService::listBackups();
        require SK_APP . '/Views/backup/index.php';
    }

    public function create(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        try {
            $res = BackupService::createBackup('manual');
            $_SESSION['flash_success'] = "Backup '{$res['filename']}' created successfully.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Backup creation failed: " . $e->getMessage();
        }

        header('Location: /backup');
        exit;
    }

    public function download(): void {
        Auth::requireAuth();

        $file = basename($_GET['file'] ?? '');
        $dir = BackupService::getBackupDir();
        $path = $dir . '/' . $file;

        if ($file === '' || !file_exists($path)) {
            http_response_code(404);
            exit('Backup file not found.');
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function restore(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        if (empty($_FILES['backup_file']['tmp_name'])) {
            $_SESSION['flash_error'] = "Please choose a SQL backup file to restore.";
            header('Location: /backup');
            exit;
        }

        try {
            $tmpFile = $_FILES['backup_file']['tmp_name'];
            $res = BackupService::restoreBackup($tmpFile);
            $_SESSION['flash_success'] = $res['message'];
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Restore failed: " . $e->getMessage();
        }

        header('Location: /backup');
        exit;
    }
}
