<?php
/**
 * Smart Khata - Customer Management Controller
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\CSRF;
use SmartKhata\Database;
use SmartKhata\Services\AccountingService;
use DateTime;
use Exception;

class CustomerController {
    public function index(): void {
        Auth::requireAuth();
        $db = Database::getInstance();

        $search = trim($_GET['q'] ?? '');
        $status = $_GET['status'] ?? 'all';

        $sql = "SELECT * FROM customers WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (name LIKE ? OR phone LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status === 'active') {
            $sql .= " AND status = 'active'";
        } elseif ($status === 'inactive') {
            $sql .= " AND status = 'inactive'";
        }

        $sql .= " ORDER BY name ASC";
        $rawCustomers = $db->fetchAll($sql, $params);

        // Augment with live financial balances
        $customers = [];
        foreach ($rawCustomers as $c) {
            $bal = AccountingService::getCustomerBalance((int)$c['id']);
            $c['balance'] = $bal;
            $customers[] = $c;
        }

        require SK_APP . '/Views/customers/index.php';
    }

    public function create(): void {
        Auth::requireAuth();
        require SK_APP . '/Views/customers/create.php';
    }

    public function store(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $balanceType = $_POST['balance_type'] ?? 'due'; // 'due' or 'advance'
        $openingBalanceRupees = trim($_POST['opening_balance'] ?? '0');
        $openingDate = !empty($_POST['opening_balance_date']) ? $_POST['opening_balance_date'] : date('Y-m-d');

        if ($name === '') {
            $_SESSION['flash_error'] = 'Customer name is required.';
            header('Location: /customers/create');
            exit;
        }

        $paise = AccountingService::rupeesToPaise($openingBalanceRupees);
        // Positive = Customer owes (Due/Debit), Negative = Customer has advance credit (Credit)
        $openingBalancePaise = ($balanceType === 'advance') ? -$paise : $paise;

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $db->execute(
                "INSERT INTO customers (name, phone, address, notes, opening_balance, opening_balance_date, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?)",
                [$name, $phone, $address, $notes, $openingBalancePaise, $openingDate, $now, $now]
            );

            $customerId = (int)$db->lastInsertId();

            // Traceable dated opening balance ledger entry
            if ($openingBalancePaise !== 0) {
                $debit = $openingBalancePaise > 0 ? $openingBalancePaise : 0;
                $credit = $openingBalancePaise < 0 ? abs($openingBalancePaise) : 0;
                $desc = "Opening Balance (" . ($openingBalancePaise > 0 ? 'Previous Debt / Udhaar' : 'Customer Advance Credit') . ")";

                AccountingService::recordLedgerEntry(
                    $customerId,
                    $openingDate,
                    'opening_balance',
                    null,
                    'OPENING',
                    $desc,
                    $debit,
                    $credit
                );
            }

            $db->commit();
            $_SESSION['flash_success'] = "Customer '{$name}' created successfully.";
            header("Location: /customers/show?id={$customerId}");
            exit;
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Failed to create customer: " . $e->getMessage();
            header('Location: /customers/create');
            exit;
        }
    }

    public function show(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();

        $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$id]);
        if (!$customer) {
            header('Location: /customers');
            exit;
        }

        $balance = AccountingService::getCustomerBalance($id);
        $invoices = $db->fetchAll("SELECT * FROM invoices WHERE customer_id = ? ORDER BY invoice_date DESC, id DESC LIMIT 15", [$id]);
        $payments = $db->fetchAll("SELECT * FROM payments WHERE customer_id = ? ORDER BY payment_date DESC, id DESC LIMIT 15", [$id]);
        $recentLedger = $db->fetchAll("SELECT * FROM ledger_entries WHERE customer_id = ? ORDER BY entry_date DESC, id DESC LIMIT 10", [$id]);

        require SK_APP . '/Views/customers/show.php';
    }

    public function edit(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();

        $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$id]);
        if (!$customer) {
            header('Location: /customers');
            exit;
        }

        require SK_APP . '/Views/customers/edit.php';
    }

    public function update(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if ($name === '') {
            $_SESSION['flash_error'] = 'Customer name cannot be empty.';
            header("Location: /customers/edit?id={$id}");
            exit;
        }

        $db = Database::getInstance();
        $db->execute(
            "UPDATE customers SET name = ?, phone = ?, address = ?, notes = ?, status = ?, updated_at = ? WHERE id = ?",
            [$name, $phone, $address, $notes, $status, (new DateTime())->format('Y-m-d H:i:s'), $id]
        );

        $_SESSION['flash_success'] = "Customer profile updated successfully.";
        header("Location: /customers/show?id={$id}");
        exit;
    }
}
