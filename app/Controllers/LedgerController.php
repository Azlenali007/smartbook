<?php
/**
 * Smart Khata - Customer Ledger Controller
 * Complete chronological transaction history, running balances, statements, and advance adjustments.
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\CSRF;
use SmartKhata\Database;
use SmartKhata\Services\AccountingService;
use SmartKhata\Services\AllocationService;
use SmartKhata\Services\PdfService;

class LedgerController {
    public function show(): void {
        Auth::requireAuth();
        $customerId = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();

        $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$customerId]);
        if (!$customer) {
            header('Location: /customers');
            exit;
        }

        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $sql = "SELECT * FROM ledger_entries WHERE customer_id = ?";
        $params = [$customerId];

        if ($startDate !== '') {
            $sql .= " AND entry_date >= ?";
            $params[] = $startDate;
        }
        if ($endDate !== '') {
            $sql .= " AND entry_date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY entry_date ASC, id ASC";
        $entries = $db->fetchAll($sql, $params);

        $balance = AccountingService::getCustomerBalance($customerId);

        // Fetch unpaid invoices for potential advance credit adjustment
        $unpaidInvoices = $db->fetchAll(
            "SELECT id, invoice_number, invoice_date, total_amount, paid_amount, due_amount 
             FROM invoices 
             WHERE customer_id = ? AND is_reversed = 0 AND due_amount > 0 
             ORDER BY invoice_date ASC, id ASC",
            [$customerId]
        );

        require SK_APP . '/Views/ledger/show.php';
    }

    public function print(): void {
        Auth::requireAuth();
        $customerId = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();

        $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$customerId]);
        if (!$customer) exit('Customer not found');

        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';

        $sql = "SELECT * FROM ledger_entries WHERE customer_id = ?";
        $params = [$customerId];
        if ($startDate !== '') {
            $sql .= " AND entry_date >= ?";
            $params[] = $startDate;
        }
        if ($endDate !== '') {
            $sql .= " AND entry_date <= ?";
            $params[] = $endDate;
        }
        $sql .= " ORDER BY entry_date ASC, id ASC";
        $entries = $db->fetchAll($sql, $params);

        $balance = AccountingService::getCustomerBalance($customerId);
        $settings = getSettings();

        require SK_APP . '/Views/ledger/print.php';
    }

    public function pdf(): void {
        Auth::requireAuth();
        $customerId = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();

        $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$customerId]);
        if (!$customer) exit('Customer not found');

        $entries = $db->fetchAll(
            "SELECT * FROM ledger_entries WHERE customer_id = ? ORDER BY entry_date ASC, id ASC",
            [$customerId]
        );

        $balance = AccountingService::getCustomerBalance($customerId);
        $settings = getSettings();

        $pdfContent = PdfService::generateLedgerPdf($customer, $entries, $balance, $settings);
        $filename = "Statement_" . preg_replace('/[^A-Za-z0-9_-]/', '_', $customer['name']) . ".pdf";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }

    public function applyAdvance(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        $customerId = (int)($_POST['customer_id'] ?? 0);
        $invoiceId = (int)($_POST['invoice_id'] ?? 0);
        $amountRupees = trim($_POST['amount'] ?? '0');
        $amountPaise = AccountingService::rupeesToPaise($amountRupees);

        try {
            AllocationService::applyAdvanceToInvoice($customerId, $invoiceId, $amountPaise);
            $_SESSION['flash_success'] = "Advance credit of " . AccountingService::paiseToRupees($amountPaise) . " successfully applied to invoice.";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Adjustment error: " . $e->getMessage();
        }

        header("Location: /ledger?id={$customerId}");
        exit;
    }
}
