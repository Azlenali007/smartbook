<?php
/**
 * Smart Khata - Payment Management Controller
 * Handles receiving payments, auto & manual allocation, advance handling, and receipts.
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\CSRF;
use SmartKhata\Database;
use SmartKhata\Services\AccountingService;
use SmartKhata\Services\AllocationService;
use SmartKhata\Services\PdfService;
use DateTime;
use Exception;

class PaymentController {
    public function index(): void {
        Auth::requireAuth();
        $db = Database::getInstance();

        $search = trim($_GET['q'] ?? '');
        $customerId = (int)($_GET['customer_id'] ?? 0);

        $sql = "SELECT p.*, c.name as customer_name, c.phone as customer_phone 
                FROM payments p 
                JOIN customers c ON p.customer_id = c.id 
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (p.payment_number LIKE ? OR p.reference_no LIKE ? OR c.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($customerId > 0) {
            $sql .= " AND p.customer_id = ?";
            $params[] = $customerId;
        }

        $sql .= " ORDER BY p.payment_date DESC, p.id DESC LIMIT 100";
        $payments = $db->fetchAll($sql, $params);

        $allCustomers = $db->fetchAll("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name ASC");

        require SK_APP . '/Views/payments/index.php';
    }

    public function create(): void {
        Auth::requireAuth();
        $db = Database::getInstance();

        $selectedCustomerId = (int)($_GET['customer_id'] ?? 0);
        $customers = $db->fetchAll("SELECT id, name, phone FROM customers WHERE status = 'active' ORDER BY name ASC");

        $customerInvoices = [];
        $customerBalance = null;

        if ($selectedCustomerId > 0) {
            $customerInvoices = $db->fetchAll(
                "SELECT id, invoice_number, invoice_date, total_amount, paid_amount, due_amount 
                 FROM invoices 
                 WHERE customer_id = ? AND is_reversed = 0 AND due_amount > 0 
                 ORDER BY invoice_date ASC, id ASC",
                [$selectedCustomerId]
            );
            $customerBalance = AccountingService::getCustomerBalance($selectedCustomerId);
        }

        require SK_APP . '/Views/payments/create.php';
    }

    public function store(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $customerId = (int)($_POST['customer_id'] ?? 0);
            if ($customerId <= 0) {
                throw new Exception("Please select a customer.");
            }

            $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$customerId]);
            if (!$customer) {
                throw new Exception("Customer not found.");
            }

            $paymentDate = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
            $amountInput = trim($_POST['amount'] ?? '0');
            $amountPaise = AccountingService::rupeesToPaise($amountInput);

            if ($amountPaise <= 0) {
                throw new Exception("Payment amount must be greater than zero.");
            }

            $paymentMode = $_POST['payment_mode'] ?? 'Cash';
            $referenceNo = trim($_POST['reference_no'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $allocationMethod = $_POST['allocation_method'] ?? 'auto'; // 'auto' or 'manual'

            // Generate unique payment number
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));

            // Insert initial payment record
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $db->execute(
                "INSERT INTO payments (
                    customer_id, payment_number, payment_date, amount, allocated_amount, advance_amount,
                    payment_mode, reference_no, notes, is_reversed, created_at, updated_at
                ) VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?, 0, ?, ?)",
                [
                    $customerId,
                    $paymentNumber,
                    $paymentDate,
                    $amountPaise,
                    $amountPaise, // initially all advance until allocated
                    $paymentMode,
                    $referenceNo,
                    $notes,
                    $now,
                    $now
                ]
            );

            $paymentId = (int)$db->lastInsertId();

            // Allocation
            if ($allocationMethod === 'manual') {
                $rawAllocations = $_POST['allocated_invoices'] ?? [];
                $allocationsMap = [];
                foreach ($rawAllocations as $invId => $rupeesVal) {
                    $allocPaise = AccountingService::rupeesToPaise($rupeesVal);
                    if ($allocPaise > 0) {
                        $allocationsMap[(int)$invId] = $allocPaise;
                    }
                }
                $allocResult = AllocationService::allocatePaymentManual($paymentId, $customerId, $amountPaise, $allocationsMap);
            } else {
                // Automatic FIFO allocation
                $allocResult = AllocationService::allocatePaymentAutomatic($paymentId, $customerId, $amountPaise);
            }

            // Record in Customer Ledger as Credit (Customer paid)
            $ledgerDesc = "Payment Received ({$paymentMode})";
            if ($referenceNo !== '') {
                $ledgerDesc .= " Ref: {$referenceNo}";
            }
            if ($allocResult['advance_retained'] > 0) {
                $advRs = number_format($allocResult['advance_retained'] / 100, 2);
                $ledgerDesc .= " [Incl. Advance: ₹{$advRs}]";
            }

            AccountingService::recordLedgerEntry(
                $customerId,
                $paymentDate,
                'payment',
                $paymentId,
                $paymentNumber,
                $ledgerDesc,
                0, // Debit 0
                $amountPaise // Credit amount
            );

            $db->commit();

            $_SESSION['flash_success'] = "Payment {$paymentNumber} of " . AccountingService::paiseToRupees($amountPaise) . " recorded successfully.";
            header("Location: /payments/show?id={$paymentId}");
            exit;
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Payment recording failed: " . $e->getMessage();
            header("Location: /payments/create" . (!empty($customerId) ? "?customer_id={$customerId}" : ''));
            exit;
        }
    }

    public function show(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();

        $payment = $db->fetchOne(
            "SELECT p.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address 
             FROM payments p 
             JOIN customers c ON p.customer_id = c.id 
             WHERE p.id = ?",
            [$id]
        );

        if (!$payment) {
            header('Location: /payments');
            exit;
        }

        $allocations = $db->fetchAll(
            "SELECT pa.*, i.invoice_number, i.invoice_date, i.total_amount 
             FROM payment_allocations pa 
             JOIN invoices i ON pa.invoice_id = i.id 
             WHERE pa.payment_id = ?",
            [$id]
        );

        $customer = [
            'id' => $payment['customer_id'],
            'name' => $payment['customer_name'],
            'phone' => $payment['customer_phone'],
            'address' => $payment['customer_address']
        ];

        $customerBalance = AccountingService::getCustomerBalance((int)$payment['customer_id']);
        $settings = getSettings();

        require SK_APP . '/Views/payments/show.php';
    }

    public function print(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();

        $payment = $db->fetchOne(
            "SELECT p.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address 
             FROM payments p 
             JOIN customers c ON p.customer_id = c.id 
             WHERE p.id = ?",
            [$id]
        );

        if (!$payment) exit('Payment not found');

        $allocations = $db->fetchAll(
            "SELECT pa.*, i.invoice_number, i.invoice_date 
             FROM payment_allocations pa 
             JOIN invoices i ON pa.invoice_id = i.id 
             WHERE pa.payment_id = ?",
            [$id]
        );

        $customer = [
            'id' => $payment['customer_id'],
            'name' => $payment['customer_name'],
            'phone' => $payment['customer_phone'],
            'address' => $payment['customer_address']
        ];

        $settings = getSettings();
        require SK_APP . '/Views/payments/print.php';
    }

    public function pdf(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::getInstance();

        $payment = $db->fetchOne(
            "SELECT p.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address 
             FROM payments p 
             JOIN customers c ON p.customer_id = c.id 
             WHERE p.id = ?",
            [$id]
        );

        if (!$payment) exit('Payment not found');

        $customer = [
            'name' => $payment['customer_name'],
            'phone' => $payment['customer_phone'],
            'address' => $payment['customer_address']
        ];
        $settings = getSettings();

        $pdfContent = PdfService::generateReceiptPdf($payment, $customer, $settings);
        $filename = "Receipt_{$payment['payment_number']}.pdf";

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }
}
