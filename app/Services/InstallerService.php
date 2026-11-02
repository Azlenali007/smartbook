<?php
/**
 * Smart Khata - Web Installer Engine
 * Server checks, schema installation for MySQL 8+ & SQLite, admin setup, and installation lock.
 */

namespace SmartKhata\Services;

use PDO;
use Exception;
use DateTime;
use SmartKhata\Auth;

class InstallerService {
    public static function isInstalled(): bool {
        $lockFile = dirname(__DIR__, 2) . '/config/installed.lock';
        $configFile = dirname(__DIR__, 2) . '/config/config.php';
        return file_exists($lockFile) && file_exists($configFile);
    }

    public static function checkRequirements(): array {
        $checks = [];

        // PHP Version
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '8.2.0', '>=');
        $checks[] = [
            'name' => 'PHP Version (>= 8.2)',
            'status' => $phpOk ? 'pass' : 'fail',
            'detail' => "Current: PHP {$phpVersion}",
            'critical' => true
        ];

        // PDO Extension
        $pdoOk = extension_loaded('pdo');
        $checks[] = [
            'name' => 'PDO Extension',
            'status' => $pdoOk ? 'pass' : 'fail',
            'detail' => $pdoOk ? 'Enabled' : 'Missing pdo extension',
            'critical' => true
        ];

        // Database drivers
        $drivers = PDO::getAvailableDrivers();
        $hasMysql = in_array('mysql', $drivers);
        $hasSqlite = in_array('sqlite', $drivers);
        $dbOk = $hasMysql || $hasSqlite;
        $checks[] = [
            'name' => 'Database Driver (MySQL or SQLite PDO)',
            'status' => $dbOk ? 'pass' : 'fail',
            'detail' => "Available: " . implode(', ', $drivers),
            'critical' => true
        ];

        // OpenSSL
        $opensslOk = extension_loaded('openssl');
        $checks[] = [
            'name' => 'OpenSSL Extension',
            'status' => $opensslOk ? 'pass' : 'fail',
            'detail' => $opensslOk ? 'Enabled' : 'Missing openssl',
            'critical' => true
        ];

        // Mbstring
        $mbstringOk = extension_loaded('mbstring');
        $checks[] = [
            'name' => 'Mbstring Extension',
            'status' => $mbstringOk ? 'pass' : 'warning',
            'detail' => $mbstringOk ? 'Enabled' : 'Recommended for Unicode text formatting',
            'critical' => false
        ];

        // Writability of directories
        $baseDir = dirname(__DIR__, 2);
        $configDir = $baseDir . '/config';
        $storageDir = $baseDir . '/storage';

        $configWritable = is_writable($configDir) || is_writable($baseDir);
        $checks[] = [
            'name' => 'Config Directory Writable (/config)',
            'status' => $configWritable ? 'pass' : 'fail',
            'detail' => $configWritable ? 'Writable' : 'Permission denied. Ensure 0755 or 0775',
            'critical' => true
        ];

        $storageWritable = is_writable($storageDir) || is_writable($baseDir);
        $checks[] = [
            'name' => 'Storage Directory Writable (/storage)',
            'status' => $storageWritable ? 'pass' : 'fail',
            'detail' => $storageWritable ? 'Writable' : 'Permission denied. Ensure 0755 or 0775',
            'critical' => true
        ];

        $allCriticalPassed = true;
        foreach ($checks as $c) {
            if ($c['critical'] && $c['status'] === 'fail') {
                $allCriticalPassed = false;
            }
        }

        return [
            'checks' => $checks,
            'can_proceed' => $allCriticalPassed,
            'has_mysql' => $hasMysql,
            'has_sqlite' => $hasSqlite
        ];
    }

    public static function testDatabaseConnection(array $config): array {
        $driver = strtolower($config['db_driver'] ?? 'sqlite');
        try {
            if ($driver === 'mysql') {
                $host = $config['db_host'] ?? '127.0.0.1';
                $port = $config['db_port'] ?? '3306';
                $dbname = $config['db_name'] ?? 'smart_khata';
                $charset = $config['db_charset'] ?? 'utf8mb4';
                $user = $config['db_user'] ?? 'root';
                $pass = $config['db_pass'] ?? '';

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
                $pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5
                ]);
                return ['success' => true, 'message' => "Successfully connected to MySQL database '{$dbname}'."];
            } else {
                // SQLite
                $path = $config['db_path'] ?? (dirname(__DIR__, 2) . '/storage/database/smart_khata.sqlite');
                $dir = dirname($path);
                if (!is_dir($dir)) mkdir($dir, 0750, true);
                $pdo = new PDO("sqlite:" . $path, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                return ['success' => true, 'message' => "Successfully connected to SQLite database."];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => "Database Connection Failed: " . $e->getMessage()];
        }
    }

    public static function installDatabase(PDO $pdo, string $driver): void {
        if ($driver === 'sqlite') {
            $queries = [
                "CREATE TABLE IF NOT EXISTS admins (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password_hash TEXT NOT NULL,
                    failed_attempts INTEGER DEFAULT 0,
                    locked_until TEXT NULL,
                    last_login_at TEXT NULL,
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                )",

                "CREATE TABLE IF NOT EXISTS customers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    phone TEXT NULL,
                    address TEXT NULL,
                    notes TEXT NULL,
                    opening_balance INTEGER NOT NULL DEFAULT 0,
                    opening_balance_date TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT 'active',
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                )",

                "CREATE TABLE IF NOT EXISTS invoices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    invoice_number TEXT UNIQUE NOT NULL,
                    invoice_date TEXT NOT NULL,
                    subtotal INTEGER NOT NULL,
                    tax_amount INTEGER NOT NULL DEFAULT 0,
                    total_amount INTEGER NOT NULL,
                    paid_amount INTEGER NOT NULL DEFAULT 0,
                    due_amount INTEGER NOT NULL,
                    status TEXT NOT NULL DEFAULT 'unpaid',
                    notes TEXT NULL,
                    is_reversed INTEGER NOT NULL DEFAULT 0,
                    reversal_reason TEXT NULL,
                    reversed_at TEXT NULL,
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                )",

                "CREATE TABLE IF NOT EXISTS invoice_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    invoice_id INTEGER NOT NULL,
                    item_name TEXT NOT NULL,
                    quantity INTEGER NOT NULL,
                    unit TEXT NOT NULL DEFAULT 'Piece',
                    unit_price INTEGER NOT NULL,
                    line_total INTEGER NOT NULL,
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
                )",

                "CREATE TABLE IF NOT EXISTS payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    payment_number TEXT UNIQUE NOT NULL,
                    payment_date TEXT NOT NULL,
                    amount INTEGER NOT NULL,
                    allocated_amount INTEGER NOT NULL DEFAULT 0,
                    advance_amount INTEGER NOT NULL DEFAULT 0,
                    payment_mode TEXT NOT NULL,
                    reference_no TEXT NULL,
                    notes TEXT NULL,
                    is_reversed INTEGER NOT NULL DEFAULT 0,
                    reversal_reason TEXT NULL,
                    reversed_at TEXT NULL,
                    created_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                )",

                "CREATE TABLE IF NOT EXISTS payment_allocations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    payment_id INTEGER NOT NULL,
                    invoice_id INTEGER NOT NULL,
                    allocated_amount INTEGER NOT NULL,
                    created_at TEXT NOT NULL,
                    FOREIGN KEY (payment_id) REFERENCES payments(id),
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
                )",

                "CREATE TABLE IF NOT EXISTS ledger_entries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    entry_date TEXT NOT NULL,
                    entry_type TEXT NOT NULL,
                    reference_id INTEGER NULL,
                    reference_no TEXT NULL,
                    description TEXT NOT NULL,
                    debit INTEGER NOT NULL DEFAULT 0,
                    credit INTEGER NOT NULL DEFAULT 0,
                    running_balance INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                )",

                "CREATE TABLE IF NOT EXISTS adjustments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    customer_id INTEGER NOT NULL,
                    adjustment_number TEXT UNIQUE NOT NULL,
                    adjustment_date TEXT NOT NULL,
                    invoice_id INTEGER NULL,
                    amount INTEGER NOT NULL,
                    reason TEXT NOT NULL,
                    created_at TEXT NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                )",

                "CREATE TABLE IF NOT EXISTS settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    setting_key TEXT UNIQUE NOT NULL,
                    setting_value TEXT NULL
                )",

                "CREATE TABLE IF NOT EXISTS login_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL,
                    ip_address TEXT NULL,
                    user_agent TEXT NULL,
                    status TEXT NOT NULL,
                    attempted_at TEXT NOT NULL
                )",

                "CREATE TABLE IF NOT EXISTS backup_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    filename TEXT NOT NULL,
                    filepath TEXT NOT NULL,
                    filesize INTEGER NOT NULL,
                    backup_type TEXT NOT NULL,
                    status TEXT NOT NULL,
                    created_at TEXT NOT NULL
                )",

                // Performance Indexes
                "CREATE INDEX IF NOT EXISTS idx_invoices_customer ON invoices(customer_id)",
                "CREATE INDEX IF NOT EXISTS idx_invoices_date ON invoices(invoice_date)",
                "CREATE INDEX IF NOT EXISTS idx_payments_customer ON payments(customer_id)",
                "CREATE INDEX IF NOT EXISTS idx_payments_date ON payments(payment_date)",
                "CREATE INDEX IF NOT EXISTS idx_ledger_customer ON ledger_entries(customer_id, entry_date)",
                "CREATE INDEX IF NOT EXISTS idx_allocations_payment ON payment_allocations(payment_id)",
                "CREATE INDEX IF NOT EXISTS idx_allocations_invoice ON payment_allocations(invoice_id)"
            ];
        } else {
            // MySQL 8+ Engine InnoDB
            $queries = [
                "CREATE TABLE IF NOT EXISTS admins (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    failed_attempts INT DEFAULT 0,
                    locked_until DATETIME NULL,
                    last_login_at DATETIME NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS customers (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(150) NOT NULL,
                    phone VARCHAR(20) NULL,
                    address TEXT NULL,
                    notes TEXT NULL,
                    opening_balance BIGINT NOT NULL DEFAULT 0,
                    opening_balance_date DATE NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'active',
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    INDEX idx_customers_phone (phone)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS invoices (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT NOT NULL,
                    invoice_number VARCHAR(50) NOT NULL UNIQUE,
                    invoice_date DATE NOT NULL,
                    subtotal BIGINT NOT NULL,
                    tax_amount BIGINT NOT NULL DEFAULT 0,
                    total_amount BIGINT NOT NULL,
                    paid_amount BIGINT NOT NULL DEFAULT 0,
                    due_amount BIGINT NOT NULL,
                    status VARCHAR(30) NOT NULL DEFAULT 'unpaid',
                    notes TEXT NULL,
                    is_reversed TINYINT(1) NOT NULL DEFAULT 0,
                    reversal_reason TEXT NULL,
                    reversed_at DATETIME NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id),
                    INDEX idx_invoices_date (invoice_date),
                    INDEX idx_invoices_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS invoice_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    invoice_id INT NOT NULL,
                    item_name VARCHAR(255) NOT NULL,
                    quantity INT NOT NULL,
                    unit VARCHAR(20) NOT NULL DEFAULT 'Piece',
                    unit_price BIGINT NOT NULL,
                    line_total BIGINT NOT NULL,
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS payments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT NOT NULL,
                    payment_number VARCHAR(50) NOT NULL UNIQUE,
                    payment_date DATE NOT NULL,
                    amount BIGINT NOT NULL,
                    allocated_amount BIGINT NOT NULL DEFAULT 0,
                    advance_amount BIGINT NOT NULL DEFAULT 0,
                    payment_mode VARCHAR(50) NOT NULL,
                    reference_no VARCHAR(100) NULL,
                    notes TEXT NULL,
                    is_reversed TINYINT(1) NOT NULL DEFAULT 0,
                    reversal_reason TEXT NULL,
                    reversed_at DATETIME NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id),
                    INDEX idx_payments_date (payment_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS payment_allocations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    payment_id INT NOT NULL,
                    invoice_id INT NOT NULL,
                    allocated_amount BIGINT NOT NULL,
                    created_at DATETIME NOT NULL,
                    FOREIGN KEY (payment_id) REFERENCES payments(id),
                    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
                    INDEX idx_alloc_invoice (invoice_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS ledger_entries (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT NOT NULL,
                    entry_date DATE NOT NULL,
                    entry_type VARCHAR(50) NOT NULL,
                    reference_id INT NULL,
                    reference_no VARCHAR(50) NULL,
                    description VARCHAR(255) NOT NULL,
                    debit BIGINT NOT NULL DEFAULT 0,
                    credit BIGINT NOT NULL DEFAULT 0,
                    running_balance BIGINT NOT NULL DEFAULT 0,
                    created_at DATETIME NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id),
                    INDEX idx_ledger_customer_date (customer_id, entry_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS adjustments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    customer_id INT NOT NULL,
                    adjustment_number VARCHAR(50) NOT NULL UNIQUE,
                    adjustment_date DATE NOT NULL,
                    invoice_id INT NULL,
                    amount BIGINT NOT NULL,
                    reason TEXT NOT NULL,
                    created_at DATETIME NOT NULL,
                    FOREIGN KEY (customer_id) REFERENCES customers(id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) NOT NULL UNIQUE,
                    setting_value TEXT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS login_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL,
                    ip_address VARCHAR(50) NULL,
                    user_agent TEXT NULL,
                    status VARCHAR(20) NOT NULL,
                    attempted_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

                "CREATE TABLE IF NOT EXISTS backup_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    filename VARCHAR(255) NOT NULL,
                    filepath VARCHAR(255) NOT NULL,
                    filesize BIGINT NOT NULL,
                    backup_type VARCHAR(20) NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    created_at DATETIME NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            ];
        }

        foreach ($queries as $sql) {
            $pdo->exec($sql);
        }
    }

    public static function createAdminAndSettings(PDO $pdo, string $adminUser, string $adminPass, array $appSettings): void {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $hash = Auth::hashPassword($adminPass);

        // Check if admin exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$adminUser]);
        $existing = $stmt->fetch();

        if ($existing) {
            $update = $pdo->prepare("UPDATE admins SET password_hash = ?, updated_at = ? WHERE id = ?");
            $update->execute([$hash, $now, $existing['id']]);
        } else {
            $insert = $pdo->prepare("INSERT INTO admins (username, password_hash, created_at, updated_at) VALUES (?, ?, ?, ?)");
            $insert->execute([$adminUser, $hash, $now, $now]);
        }

        // Insert / Update Settings
        $defaults = [
            'shop_name' => $appSettings['shop_name'] ?? 'Smart Khata Store',
            'shop_phone' => $appSettings['shop_phone'] ?? '',
            'shop_address' => $appSettings['shop_address'] ?? '',
            'shop_gstin' => $appSettings['shop_gstin'] ?? '',
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'timezone' => 'Asia/Kolkata',
            'date_format' => 'd-m-Y',
            'invoice_prefix' => $appSettings['invoice_prefix'] ?? 'INV-',
            'gst_rate' => '0',
            'invoice_footer' => $appSettings['invoice_footer'] ?? 'Thank you for your business! Payment due upon presentation.',
            'installed_at' => $now
        ];

        foreach ($defaults as $key => $val) {
            $sCheck = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
            $sCheck->execute([$key]);
            if ($sCheck->fetch()) {
                $sUp = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $sUp->execute([$val, $key]);
            } else {
                $sIn = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                $sIn->execute([$key, $val]);
            }
        }
    }

    public static function finalizeInstallation(array $dbConfig): void {
        $baseDir = dirname(__DIR__, 2);
        $configPath = $baseDir . '/config/config.php';
        $lockPath = $baseDir . '/config/installed.lock';

        $secret = bin2hex(random_bytes(32));

        $configCode = "<?php\n// Smart Khata Production Configuration\nreturn " . var_export([
            'db_driver' => $dbConfig['db_driver'] ?? 'sqlite',
            'db_host' => $dbConfig['db_host'] ?? '127.0.0.1',
            'db_port' => $dbConfig['db_port'] ?? '3306',
            'db_name' => $dbConfig['db_name'] ?? 'smart_khata',
            'db_user' => $dbConfig['db_user'] ?? 'root',
            'db_pass' => $dbConfig['db_pass'] ?? '',
            'db_charset' => 'utf8mb4',
            'db_path' => $dbConfig['db_path'] ?? ($baseDir . '/storage/database/smart_khata.sqlite'),
            'app_name' => 'Smart Khata',
            'app_env' => 'production',
            'app_secret' => $secret,
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'currency_symbol' => '₹'
        ], true) . ";\n";

        file_put_contents($configPath, $configCode);
        file_put_contents($lockPath, "INSTALLED=" . date('c') . "\nSECRET_HASH=" . hash('sha256', $secret) . "\n");
    }
}
