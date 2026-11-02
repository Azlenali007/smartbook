<?php
/**
 * Smart Khata - Invoice Management Controller
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\CSRF;
use SmartKhata\Database;
use SmartKhata\Services\InvoiceService;
use SmartKhata\Services\PdfService;
use Exception;

class InvoiceController {
    public function index(): void {
        Auth::requireAuth();
        $db = Database::getInstance();

        $search = trim($_GET['q'] ?? '');
        $status = $_GET['status'] ?? 'all';
        $customerId = (int)($_GET['customer_id'] ?? 0);

        $sql = "SELECT i.*, c.name as customer_name, c.phone as customer_phone 
                FROM invoices i 
                JOIN customers c ON i.customer_id = c.id 
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (i.invoice_number LIKE ? OR c.name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status !== 'all' && in_array($status, ['unpaid', 'partial', 'paid', 'reversed'])) {
            if ($status === 'reversed') {
                $sql .= " AND i.is_reversed = 1";
            } else {
                $sql .= " AND i.is_reversed = 0 AND i.status = ?";
                $params[] = $status;
            }
        }

        if ($customerId > 0) {
            $sql .= " AND i.customer_id = ?";
            $params[] = $customerId;
        }

        $sql .= " ORDER BY i.invoice_date DESC, i.id DESC LIMIT 100";
        $invoices = $db->fetchAll($sql, $params);

        $allCustomers = $db->fetchAll("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name ASC");

        require SK_APP . '/Views/invoices/index.php';
    }

    public function create(): void {
        Auth::requireAuth();
        $db = Database::getInstance();
        $customers = $db->fetchAll("SELECT id, name, phone, opening_balance FROM customers WHERE status = 'active' ORDER BY name ASC");
        $selectedCustomerId = (int)($_GET['customer_id'] ?? 0);

        require SK_APP . '/Views/invoices/create.php';
    }

    public function store(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        try {
            $customerId = (int)($_POST['customer_id'] ?? 0);
            $invoiceDate = $_POST['invoice_date'] ?? date('Y-m-d');
            $notes = $_POST['notes'] ?? '';
            $applyAdvance = $_POST['apply_advance'] ?? 'no';

            // Parse items from dynamic form arrays
            $itemNames = $_POST['item_name'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            $rates = $_POST['rate'] ?? [];

            $items = [];
            for ($i = 0; $i < count($itemNames); $i++) {
                if (trim($itemNames[$i]) === '') continue;
                $items[] = [
                    'item_name' => $itemNames[$i],
                    'quantity' => $quantities[$i] ?? 1,
                    'rate' => $rates[$i] ?? 0
                ];
            }

            $res = InvoiceService::createInvoice([
                'customer_id' => $customerId,
                'invoice_date' => $invoiceDate,
                'notes' => $notes,
                'apply_advance' => $applyAdvance,
                'items' => $items
            ]);

            $_SESSION['flash_success'] = "Invoice #{$res['invoice_number']} created successfully.";
            header("Location: /invoices/show?id={$res['invoice_id']}");
            exit;
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Invoice creation failed: " . $e->getMessage();
            header('Location: /invoices/create' . (!empty($customerId) ? "?customer_id={$customerId}" : ''));
            exit;
        }
    }

    public function show(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $details = InvoiceService::getInvoiceDetails($id);

        if (!$details) {
            header('Location: /invoices');
            exit;
        }

        $invoice = $details['invoice'];
        $items = $details['items'];
        $allocations = $details['allocations'];
        $customerBalance = $details['customer_balance'];
        $settings = getSettings();

        require SK_APP . '/Views/invoices/show.php';
    }

    public function print(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $details = InvoiceService::getInvoiceDetails($id);

        if (!$details) {
            exit('Invoice not found');
        }

        $invoice = $details['invoice'];
        $items = $details['items'];
        $allocations = $details['allocations'];
        $settings = getSettings();

        require SK_APP . '/Views/invoices/print.php';
    }

    public function pdf(): void {
        Auth::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $details = InvoiceService::getInvoiceDetails($id);

        if (!$details) {
            http_response_code(404);
            exit('Invoice not found');
        }

        $settings = getSettings();
        $pdfContent = PdfService::generateInvoicePdf($details, $settings);

        $filename = "Invoice_{$details['invoice']['invoice_number']}.pdf";
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }

    public function reverse(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reversal_reason'] ?? '');

        try {
            $res = InvoiceService::reverseInvoice($id, $reason);
            $_SESSION['flash_success'] = $res['message'];
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Reversal error: " . $e->getMessage();
        }

        header("Location: /invoices/show?id={$id}");
        exit;
    }
}
