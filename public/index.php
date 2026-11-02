<?php
/**
 * Smart Khata - Front Controller & HTTP Router
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use SmartKhata\Services\InstallerService;
use SmartKhata\Controllers\AuthController;
use SmartKhata\Controllers\DashboardController;
use SmartKhata\Controllers\CustomerController;
use SmartKhata\Controllers\InvoiceController;
use SmartKhata\Controllers\PaymentController;
use SmartKhata\Controllers\LedgerController;
use SmartKhata\Controllers\ReportController;
use SmartKhata\Controllers\SettingController;
use SmartKhata\Controllers\BackupController;
use SmartKhata\Controllers\InstallerController;

$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// If not installed, redirect everything except /install to installer
if (!InstallerService::isInstalled()) {
    if (!str_starts_with($requestUri, '/install') && !str_starts_with($requestUri, '/assets/') && $requestUri !== '/favicon.svg' && $requestUri !== '/manifest.webmanifest' && $requestUri !== '/sw.js') {
        header('Location: /install');
        exit;
    }
} else {
    // If installed, block installer
    if (str_starts_with($requestUri, '/install')) {
        header('Location: /login');
        exit;
    }
}

// Router Dispatch
try {
    switch ($requestUri) {
        // Dashboard
        case '/':
            (new DashboardController())->index();
            break;

        // Auth
        case '/login':
            if ($requestMethod === 'POST') {
                (new AuthController())->handleLogin();
            } else {
                (new AuthController())->showLogin();
            }
            break;

        case '/logout':
            (new AuthController())->logout();
            break;

        // Customers
        case '/customers':
            (new CustomerController())->index();
            break;
        case '/customers/create':
            (new CustomerController())->create();
            break;
        case '/customers/store':
            if ($requestMethod === 'POST') {
                (new CustomerController())->store();
            }
            break;
        case '/customers/show':
            (new CustomerController())->show();
            break;
        case '/customers/edit':
            (new CustomerController())->edit();
            break;
        case '/customers/update':
            if ($requestMethod === 'POST') {
                (new CustomerController())->update();
            }
            break;

        // Invoices
        case '/invoices':
            (new InvoiceController())->index();
            break;
        case '/invoices/create':
            (new InvoiceController())->create();
            break;
        case '/invoices/store':
            if ($requestMethod === 'POST') {
                (new InvoiceController())->store();
            }
            break;
        case '/invoices/show':
            (new InvoiceController())->show();
            break;
        case '/invoices/print':
            (new InvoiceController())->print();
            break;
        case '/invoices/pdf':
            (new InvoiceController())->pdf();
            break;
        case '/invoices/reverse':
            if ($requestMethod === 'POST') {
                (new InvoiceController())->reverse();
            }
            break;

        // Payments
        case '/payments':
            (new PaymentController())->index();
            break;
        case '/payments/create':
            (new PaymentController())->create();
            break;
        case '/payments/store':
            if ($requestMethod === 'POST') {
                (new PaymentController())->store();
            }
            break;
        case '/payments/show':
            (new PaymentController())->show();
            break;
        case '/payments/print':
            (new PaymentController())->print();
            break;
        case '/payments/pdf':
            (new PaymentController())->pdf();
            break;

        // Customer Ledger
        case '/ledger':
            (new LedgerController())->show();
            break;
        case '/ledger/print':
            (new LedgerController())->print();
            break;
        case '/ledger/pdf':
            (new LedgerController())->pdf();
            break;
        case '/ledger/apply-advance':
            if ($requestMethod === 'POST') {
                (new LedgerController())->applyAdvance();
            }
            break;

        // Reports
        case '/reports':
            (new ReportController())->index();
            break;

        // Settings
        case '/settings':
            (new SettingController())->index();
            break;
        case '/settings/update-business':
            if ($requestMethod === 'POST') {
                (new SettingController())->updateBusiness();
            }
            break;
        case '/settings/update-password':
            if ($requestMethod === 'POST') {
                (new SettingController())->updatePassword();
            }
            break;

        // Backup
        case '/backup':
            (new BackupController())->index();
            break;
        case '/backup/create':
            if ($requestMethod === 'POST') {
                (new BackupController())->create();
            }
            break;
        case '/backup/download':
            (new BackupController())->download();
            break;
        case '/backup/restore':
            if ($requestMethod === 'POST') {
                (new BackupController())->restore();
            }
            break;

        // Installer
        case '/install':
            (new InstallerController())->index();
            break;
        case '/install/test-db':
            if ($requestMethod === 'POST') {
                (new InstallerController())->testDatabase();
            }
            break;
        case '/install/process':
            if ($requestMethod === 'POST') {
                (new InstallerController())->processInstallation();
            }
            break;

        default:
            http_response_code(404);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><title>404 Not Found</title><link rel="stylesheet" href="/assets/css/app.css"></head><body class="bg-slate-50 min-h-screen flex items-center justify-center p-4"><div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-lg border border-slate-200 text-center"><h1 class="text-3xl font-black text-slate-900 mb-2">404</h1><p class="text-sm text-slate-600 mb-6">The requested page does not exist in Smart Khata.</p><a href="/" class="bg-blue-600 text-white font-semibold text-xs px-5 py-2.5 rounded-xl hover:bg-blue-700 transition">Return to Dashboard</a></div></body></html>';
            break;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>500 Internal Error</title></head><body style="font-family:sans-serif;padding:40px;background:#f8fafc;color:#1e293b;"><div style="max-width:600px;margin:0 auto;background:#fff;padding:30px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);"><h2>Internal Application Notice</h2><p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p><p><a href="/">Return to Home</a></p></div></body></html>';
}
