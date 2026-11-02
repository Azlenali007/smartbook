<?php
/**
 * Smart Khata - Web Installer Controller (Steps 1 to 7)
 */

namespace SmartKhata\Controllers;

use SmartKhata\CSRF;
use SmartKhata\Services\InstallerService;
use PDO;
use Exception;

class InstallerController {
    public function __construct() {
        if (InstallerService::isInstalled()) {
            header('Location: /login');
            exit;
        }
    }

    public function index(): void {
        $step = (int)($_GET['step'] ?? 1);
        if ($step < 1 || $step > 7) $step = 1;

        $req = InstallerService::checkRequirements();

        $stepFiles = [
            1 => 'step1_welcome.php',
            2 => 'step2_requirements.php',
            3 => 'step3_database.php',
            4 => 'step3_database.php',
            5 => 'step5_admin.php',
            6 => 'step5_admin.php',
            7 => 'step7_complete.php'
        ];
        $file = $stepFiles[$step] ?? 'step1_welcome.php';
        require SK_APP . "/Views/installer/{$file}";
    }

    public function testDatabase(): void {
        header('Content-Type: application/json');
        CSRF::verifyOrAbort();

        $driver = $_POST['db_driver'] ?? 'sqlite';
        $config = [
            'db_driver' => $driver,
            'db_host' => $_POST['db_host'] ?? '127.0.0.1',
            'db_port' => $_POST['db_port'] ?? '3306',
            'db_name' => $_POST['db_name'] ?? 'smart_khata',
            'db_user' => $_POST['db_user'] ?? 'root',
            'db_pass' => $_POST['db_pass'] ?? '',
        ];

        $res = InstallerService::testDatabaseConnection($config);
        echo json_encode($res);
        exit;
    }

    public function processInstallation(): void {
        CSRF::verifyOrAbort();

        $dbDriver = $_POST['db_driver'] ?? 'sqlite';
        $dbConfig = [
            'db_driver' => $dbDriver,
            'db_host' => $_POST['db_host'] ?? '127.0.0.1',
            'db_port' => $_POST['db_port'] ?? '3306',
            'db_name' => $_POST['db_name'] ?? 'smart_khata',
            'db_user' => $_POST['db_user'] ?? 'root',
            'db_pass' => $_POST['db_pass'] ?? '',
        ];

        $adminUser = trim($_POST['admin_username'] ?? 'admin');
        $adminPass = $_POST['admin_password'] ?? '';
        $adminPassConf = $_POST['admin_password_confirm'] ?? '';

        if ($adminUser === '') {
            $_SESSION['installer_error'] = 'Admin username is required.';
            header('Location: /install?step=5');
            exit;
        }

        if (strlen($adminPass) < 8) {
            $_SESSION['installer_error'] = 'Admin password must be at least 8 characters long.';
            header('Location: /install?step=5');
            exit;
        }

        if ($adminPass !== $adminPassConf) {
            $_SESSION['installer_error'] = 'Admin passwords do not match.';
            header('Location: /install?step=5');
            exit;
        }

        $appSettings = [
            'shop_name' => trim($_POST['shop_name'] ?? 'Smart Khata Store'),
            'shop_phone' => trim($_POST['shop_phone'] ?? ''),
            'shop_address' => trim($_POST['shop_address'] ?? ''),
            'shop_gstin' => trim($_POST['shop_gstin'] ?? ''),
            'invoice_prefix' => trim($_POST['invoice_prefix'] ?? 'INV-'),
            'invoice_footer' => trim($_POST['invoice_footer'] ?? 'Thank you for your business!')
        ];

        try {
            // 1. Connect
            if ($dbDriver === 'mysql') {
                $dsn = "mysql:host={$dbConfig['db_host']};port={$dbConfig['db_port']};dbname={$dbConfig['db_name']};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbConfig['db_user'], $dbConfig['db_pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
            } else {
                $sqlitePath = dirname(__DIR__, 2) . '/storage/database/smart_khata.sqlite';
                $dir = dirname($sqlitePath);
                if (!is_dir($dir)) mkdir($dir, 0750, true);
                $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                $dbConfig['db_path'] = $sqlitePath;
            }

            // 2. Install schema
            InstallerService::installDatabase($pdo, $dbDriver);

            // 3. Create Admin & Settings
            InstallerService::createAdminAndSettings($pdo, $adminUser, $adminPass, $appSettings);

            // 4. Finalize lock
            InstallerService::finalizeInstallation($dbConfig);

            $_SESSION['installed_username'] = $adminUser;
            header('Location: /install?step=7');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['installer_error'] = "Installation failed: " . $e->getMessage();
            header('Location: /install?step=3');
            exit;
        }
    }
}
