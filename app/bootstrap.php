<?php
/**
 * Smart Khata - Bootstrap & Global Initialization
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/php_error.log');

// Define root constants
define('SK_ROOT', dirname(__DIR__));
define('SK_APP', SK_ROOT . '/app');
define('SK_STORAGE', SK_ROOT . '/storage');
define('SK_CONFIG', SK_ROOT . '/config');

// Default Timezone
date_default_timezone_set('Asia/Kolkata');

// PSR-4 Style Autoloader for SmartKhata namespace
spl_autoload_register(function ($class) {
    $prefix = 'SmartKhata\\';
    $baseDir = SK_APP . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Helper Functions
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatINR(int|float|string $paise, bool $withSymbol = true): string {
    return \SmartKhata\Services\AccountingService::paiseToRupees((int)$paise, $withSymbol);
}

function formatDate(?string $dateStr, string $format = 'd-m-Y'): string {
    if (empty($dateStr)) return '-';
    $time = strtotime($dateStr);
    return $time ? date($format, $time) : $dateStr;
}

function getSettings(): array {
    static $settingsCache = null;
    if ($settingsCache !== null) return $settingsCache;

    try {
        if (\SmartKhata\Services\InstallerService::isInstalled()) {
            $db = \SmartKhata\Database::getInstance();
            $rows = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
            $settingsCache = [];
            foreach ($rows as $r) {
                $settingsCache[$r['setting_key']] = $r['setting_value'];
            }
            return $settingsCache;
        }
    } catch (\Throwable $e) {}

    return [
        'shop_name' => 'Smart Khata',
        'currency_symbol' => '₹',
        'date_format' => 'd-m-Y',
        'invoice_prefix' => 'INV-'
    ];
}

// Session initialization
\SmartKhata\Auth::initSession();

// Initialize Database if configured
if (\SmartKhata\Services\InstallerService::isInstalled()) {
    $configFile = SK_CONFIG . '/config.php';
    if (file_exists($configFile)) {
        $config = require $configFile;
        \SmartKhata\Database::init($config);
    }
}
