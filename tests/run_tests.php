<?php
/**
 * Smart Khata - Automated Test Suite
 * Validates Section 6 Financial Calculations, Allocation Logic,
 * Reconciliations, Security, and File Integrity.
 */

require_once dirname(__DIR__) . '/app/bootstrap.php';

use SmartKhata\Database;
use SmartKhata\Services\AccountingService;
use SmartKhata\Services\AllocationService;
use SmartKhata\Services\InvoiceService;
use SmartKhata\Services\ReportService;
use SmartKhata\Services\InstallerService;
use SmartKhata\Auth;

echo "====================================================\n";
echo "   SMART KHATA - AUTOMATED FINANCIAL TEST SUITE   \n";
echo "====================================================\n\n";

$testDbPath = dirname(__DIR__) . '/storage/database/test_suite.sqlite';
if (file_exists($testDbPath)) {
    unlink($testDbPath);
}

// Initialize Test Database in SQLite
$pdo = new PDO("sqlite:" . $testDbPath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

InstallerService::installDatabase($pdo, 'sqlite');
InstallerService::createAdminAndSettings($pdo, 'testadmin', 'AdminPass@123', [
    'shop_name' => 'Test Khata Store',
    'invoice_prefix' => 'TEST-'
]);

// Initialize singleton with test configuration
Database::init([
    'db_driver' => 'sqlite',
    'db_path' => $testDbPath
], true);

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo " [FAIL] {$name} " . ($details ? "({$details})" : "") . "\n";
        $failed++;
    }
}

// ---------------------------------------------------------
// TEST 1: Password Hashing & Verification
// ---------------------------------------------------------
$pwd = "SecurePass#2026";
$hash = Auth::hashPassword($pwd);
assertTest("Argon2id/Bcrypt Password Hash", Auth::verifyPassword($pwd, $hash));
assertTest("Password Hash Rejects Wrong Password", !Auth::verifyPassword("WrongPassword", $hash));

// ---------------------------------------------------------
// TEST 2: Integer Paise Currency Arithmetic
// ---------------------------------------------------------
assertTest("Rupees To Paise (100.50 => 10050)", AccountingService::rupeesToPaise("100.50") === 10050);
assertTest("Rupees To Paise (1,000.00 => 100000)", AccountingService::rupeesToPaise("1,000.00") === 100000);
assertTest("Paise To Rupees (10050 => ₹100.50)", AccountingService::paiseToRupees(10050) === "₹100.50");
assertTest("Paise To Rupees (100000 => ₹1,000.00)", AccountingService::paiseToRupees(100000) === "₹1,000.00");

// ---------------------------------------------------------
// TEST 3: Section 6 - Example 1 (Opening balance ₹1,000 + Invoice ₹2,000 => Due ₹3,000)
// ---------------------------------------------------------
$db = Database::getInstance();
$db->execute(
    "INSERT INTO customers (name, phone, opening_balance, opening_balance_date, status, created_at, updated_at)
     VALUES (?, ?, ?, ?, 'active', datetime('now'), datetime('now'))",
    ['Customer One', '9000000001', 100000, date('Y-m-d')]
);
$c1Id = (int)$db->lastInsertId();
AccountingService::recordLedgerEntry($c1Id, date('Y-m-d'), 'opening_balance', null, 'OPENING', 'Opening Balance', 100000, 0);

// Create Invoice ₹2,000
InvoiceService::createInvoice([
    'customer_id' => $c1Id,
    'invoice_date' => date('Y-m-d'),
    'items' => [
        ['item_name' => 'Nag Cotton Shirt', 'quantity' => 2, 'rate' => 1000]
    ]
]);

$b1 = AccountingService::getCustomerBalance($c1Id);
assertTest("Section 6 Example 1: Outstanding Due == ₹3,000", $b1['outstanding_due'] === 300000, "Got " . $b1['outstanding_due']);
assertTest("Section 6 Example 1: Advance Credit == 0", $b1['advance_credit'] === 0);

// ---------------------------------------------------------
// TEST 4: Section 6 - Example 2 (Old ₹1,000 + New ₹2,000 + Payment ₹1,000 => Clears Old, Due ₹2,000)
// ---------------------------------------------------------
$db->execute(
    "INSERT INTO customers (name, phone, opening_balance, opening_balance_date, status, created_at, updated_at)
     VALUES (?, ?, 0, ?, 'active', datetime('now'), datetime('now'))",
    ['Customer Two', '9000000002', date('Y-m-d')]
);
$c2Id = (int)$db->lastInsertId();

$invOld = InvoiceService::createInvoice([
    'customer_id' => $c2Id,
    'invoice_date' => '2026-01-01',
    'items' => [['item_name' => 'Old Item', 'quantity' => 1, 'rate' => 1000]]
]);
$invNew = InvoiceService::createInvoice([
    'customer_id' => $c2Id,
    'invoice_date' => '2026-01-02',
    'items' => [['item_name' => 'New Item', 'quantity' => 2, 'rate' => 1000]]
]);

// Payment ₹1,000
$db->execute(
    "INSERT INTO payments (customer_id, payment_number, payment_date, amount, allocated_amount, advance_amount, payment_mode, is_reversed, created_at, updated_at)
     VALUES (?, 'PAY-TEST-002', date('now'), 100000, 0, 100000, 'Cash', 0, datetime('now'), datetime('now'))",
    [$c2Id]
);
$p2Id = (int)$db->lastInsertId();
AllocationService::allocatePaymentAutomatic($p2Id, $c2Id, 100000);
AccountingService::recordLedgerEntry($c2Id, date('Y-m-d'), 'payment', $p2Id, 'PAY-TEST-002', 'Payment', 0, 100000);

$oldCheck = $db->fetchOne("SELECT due_amount, status FROM invoices WHERE id = ?", [$invOld['invoice_id']]);
$newCheck = $db->fetchOne("SELECT due_amount, status FROM invoices WHERE id = ?", [$invNew['invoice_id']]);
$b2 = AccountingService::getCustomerBalance($c2Id);

assertTest("Section 6 Example 2: Old Invoice Fully Cleared", $oldCheck['due_amount'] === 0 && $oldCheck['status'] === 'paid');
assertTest("Section 6 Example 2: New Invoice Remains Due ₹2,000", $newCheck['due_amount'] === 200000);
assertTest("Section 6 Example 2: Total Customer Due == ₹2,000", $b2['outstanding_due'] === 200000);

// ---------------------------------------------------------
// TEST 5: Section 6 - Example 3 (Due ₹3,000 + Payment ₹10,000 => Alloc ₹3,000, Advance ₹7,000, Due ₹0)
// ---------------------------------------------------------
$db->execute(
    "INSERT INTO customers (name, phone, opening_balance, opening_balance_date, status, created_at, updated_at)
     VALUES (?, ?, 0, ?, 'active', datetime('now'), datetime('now'))",
    ['Customer Three', '9000000003', date('Y-m-d')]
);
$c3Id = (int)$db->lastInsertId();

InvoiceService::createInvoice([
    'customer_id' => $c3Id,
    'invoice_date' => date('Y-m-d'),
    'items' => [['item_name' => 'Bill Items', 'quantity' => 3, 'rate' => 1000]]
]); // Total Due: 300,000 paise (₹3,000)

// Receive Payment ₹10,000 (1,000,000 paise)
$db->execute(
    "INSERT INTO payments (customer_id, payment_number, payment_date, amount, allocated_amount, advance_amount, payment_mode, is_reversed, created_at, updated_at)
     VALUES (?, 'PAY-TEST-003', date('now'), 1000000, 0, 1000000, 'UPI', 0, datetime('now'), datetime('now'))",
    [$c3Id]
);
$p3Id = (int)$db->lastInsertId();
$alloc3 = AllocationService::allocatePaymentAutomatic($p3Id, $c3Id, 1000000);
AccountingService::recordLedgerEntry($c3Id, date('Y-m-d'), 'payment', $p3Id, 'PAY-TEST-003', 'Payment', 0, 1000000);

$b3 = AccountingService::getCustomerBalance($c3Id);
assertTest("Section 6 Example 3: Allocated == ₹3,000", $alloc3['total_allocated'] === 300000);
assertTest("Section 6 Example 3: Advance Retained == ₹7,000", $alloc3['advance_retained'] === 700000);
assertTest("Section 6 Example 3: Customer Outstanding Due == ₹0", $b3['outstanding_due'] === 0);
assertTest("Section 6 Example 3: Customer Advance Balance == ₹7,000", $b3['advance_credit'] === 700000);

// ---------------------------------------------------------
// TEST 6: Section 6 - Example 4 (Invoice ₹2,000 + Payment ₹500 => Remaining due ₹1,500)
// ---------------------------------------------------------
$db->execute(
    "INSERT INTO customers (name, phone, opening_balance, opening_balance_date, status, created_at, updated_at)
     VALUES (?, ?, 0, ?, 'active', datetime('now'), datetime('now'))",
    ['Customer Four', '9000000004', date('Y-m-d')]
);
$c4Id = (int)$db->lastInsertId();

$inv4 = InvoiceService::createInvoice([
    'customer_id' => $c4Id,
    'invoice_date' => date('Y-m-d'),
    'items' => [['item_name' => 'Goods', 'quantity' => 2, 'rate' => 1000]]
]); // ₹2,000

$db->execute(
    "INSERT INTO payments (customer_id, payment_number, payment_date, amount, allocated_amount, advance_amount, payment_mode, is_reversed, created_at, updated_at)
     VALUES (?, 'PAY-TEST-004', date('now'), 50000, 0, 50000, 'Cash', 0, datetime('now'), datetime('now'))",
    [$c4Id]
);
$p4Id = (int)$db->lastInsertId();
AllocationService::allocatePaymentAutomatic($p4Id, $c4Id, 50000);
AccountingService::recordLedgerEntry($c4Id, date('Y-m-d'), 'payment', $p4Id, 'PAY-TEST-004', 'Payment', 0, 50000);

$inv4Check = $db->fetchOne("SELECT due_amount, status FROM invoices WHERE id = ?", [$inv4['invoice_id']]);
$b4 = AccountingService::getCustomerBalance($c4Id);

assertTest("Section 6 Example 4: Invoice Due == ₹1,500", $inv4Check['due_amount'] === 150000);
assertTest("Section 6 Example 4: Invoice Status == 'partial'", $inv4Check['status'] === 'partial');
assertTest("Section 6 Example 4: Net Customer Due == ₹1,500", $b4['outstanding_due'] === 150000);

// ---------------------------------------------------------
// TEST 7: Advance Credit Adjustment against Invoice
// ---------------------------------------------------------
// Customer 3 currently has ₹7,000 advance. Let's issue a new invoice of ₹2,500 and adjust advance.
$inv3New = InvoiceService::createInvoice([
    'customer_id' => $c3Id,
    'invoice_date' => date('Y-m-d'),
    'items' => [['item_name' => 'New Order', 'quantity' => 1, 'rate' => 2500]]
]);

AllocationService::applyAdvanceToInvoice($c3Id, $inv3New['invoice_id'], 250000);
$inv3NewCheck = $db->fetchOne("SELECT due_amount, status FROM invoices WHERE id = ?", [$inv3New['invoice_id']]);
$b3AfterAdj = AccountingService::getCustomerBalance($c3Id);

assertTest("Advance Adjustment: Invoice Fully Cleared via Advance", $inv3NewCheck['due_amount'] === 0 && $inv3NewCheck['status'] === 'paid');
assertTest("Advance Adjustment: Remaining Advance == ₹4,500", $b3AfterAdj['advance_credit'] === 450000);

// ---------------------------------------------------------
// TEST 8: Full Accounting Reconciliation Check
// ---------------------------------------------------------
$recon = ReportService::runReconciliation();
assertTest("System Financial Reconciliation is Healthy", $recon['is_healthy'] === true);
assertTest("All Invoice Sums Reconciled", $recon['invoices_reconciled'] === true);
assertTest("All Ledger Balances Reconciled", $recon['ledger_reconciled'] === true);

// ---------------------------------------------------------
// TEST 9: PWA Manifest & Icons Compliance
// ---------------------------------------------------------
$manifestFile = dirname(__DIR__) . '/public/manifest.webmanifest';
$manifestExists = file_exists($manifestFile);
$manifestData = $manifestExists ? json_decode(file_get_contents($manifestFile), true) : [];

assertTest("PWA Manifest Exists", $manifestExists);
assertTest("PWA Manifest Has Standalone Display", ($manifestData['display'] ?? '') === 'standalone');
assertTest("PWA 192x192 PNG Icon Exists", file_exists(dirname(__DIR__) . '/public/pwa-192x192.png'));
assertTest("PWA 512x512 PNG Icon Exists", file_exists(dirname(__DIR__) . '/public/pwa-512x512.png'));
assertTest("PWA Apple Touch PNG Icon Exists", file_exists(dirname(__DIR__) . '/public/apple-touch-icon.png'));
assertTest("PWA Service Worker Exists", file_exists(dirname(__DIR__) . '/public/sw.js'));
assertTest("PWA Offline Fallback HTML Exists", file_exists(dirname(__DIR__) . '/public/offline.html'));

// ---------------------------------------------------------
// TEST 10: Landing Page & Homepage Routing Compliance
// ---------------------------------------------------------
$landingFile = dirname(__DIR__) . '/app/Views/home/landing.php';
$landingExists = file_exists($landingFile);
$landingHtml = $landingExists ? file_get_contents($landingFile) : '';

assertTest("Landing Page View Exists", $landingExists);
assertTest("Landing Page Has Admin Login CTA", str_contains($landingHtml, 'Admin Login'));
assertTest("Landing Page Has Branding & Features", str_contains($landingHtml, 'Customer Khata') && str_contains($landingHtml, 'Smart FIFO Allocation'));
assertTest("Landing Page Has Mobile / PWA CTA", str_contains($landingHtml, 'Install'));
assertTest("Landing Page Does Not Have Redirect Placeholder", !str_contains($landingHtml, 'Redirect to Smart Khata'));

$swJs = file_get_contents(dirname(__DIR__) . '/public/sw.js');
assertTest("Service Worker Excludes '/' From Static Precache", !preg_match("/STATIC_ASSETS\s*=\s*\[[^\]]*'\/',/", $swJs));

$indexPhp = file_get_contents(dirname(__DIR__) . '/public/index.php');
assertTest("Public Router Has '/' Landing Route", str_contains($indexPhp, "case '/':") && str_contains($indexPhp, "LandingController"));
assertTest("Public Router Has '/dashboard' Route", str_contains($indexPhp, "case '/dashboard':") && str_contains($indexPhp, "DashboardController"));
assertTest("Public Router Redirects '/index.html' to '/'", str_contains($indexPhp, "case '/index.html':"));

// ---------------------------------------------------------
// TEST 10: Mandatory Zero TypeScript Verification
// ---------------------------------------------------------
$tsFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__)));
foreach ($iterator as $f) {
    if ($f->isFile()) {
        $ext = strtolower($f->getExtension());
        $path = $f->getPathname();
        if (str_contains($path, '/node_modules/')) continue;
        if ($ext === 'ts' || $ext === 'tsx') {
            $tsFiles[] = $path;
        }
    }
}

assertTest("STRICT RESTRICTION: Zero .ts and .tsx files count == 0", count($tsFiles) === 0, "Found: " . implode(', ', $tsFiles));

// Clean up test sqlite
if (file_exists($testDbPath)) {
    unlink($testDbPath);
}

echo "\n====================================================\n";
echo " TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "====================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
