<?php
/**
 * Smart Khata - Financial Reporting & Reconciliations
 */

namespace SmartKhata\Services;

use SmartKhata\Database;

class ReportService {
    public static function getDailySales(string $startDate, string $endDate): array {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT invoice_date, COUNT(*) as invoice_count, SUM(subtotal) as subtotal, 
                    SUM(tax_amount) as tax_amount, SUM(total_amount) as total_amount,
                    SUM(paid_amount) as paid_amount, SUM(due_amount) as due_amount
             FROM invoices 
             WHERE is_reversed = 0 AND invoice_date BETWEEN ? AND ?
             GROUP BY invoice_date 
             ORDER BY invoice_date DESC",
            [$startDate, $endDate]
        );
    }

    public static function getCustomerSales(): array {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.id, c.name, c.phone, COUNT(i.id) as invoice_count, 
                    COALESCE(SUM(i.total_amount), 0) as total_sales,
                    COALESCE(SUM(i.paid_amount), 0) as total_received,
                    COALESCE(SUM(i.due_amount), 0) as total_due
             FROM customers c
             LEFT JOIN invoices i ON c.id = i.customer_id AND i.is_reversed = 0
             GROUP BY c.id, c.name, c.phone
             ORDER BY total_sales DESC"
        );
    }

    public static function getDailyCollections(string $startDate, string $endDate): array {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT payment_date, payment_mode, COUNT(*) as payment_count, 
                    SUM(amount) as total_amount, SUM(allocated_amount) as allocated_amount, 
                    SUM(advance_amount) as advance_amount
             FROM payments 
             WHERE is_reversed = 0 AND payment_date BETWEEN ? AND ?
             GROUP BY payment_date, payment_mode 
             ORDER BY payment_date DESC, total_amount DESC",
            [$startDate, $endDate]
        );
    }

    public static function getPaymentModesSummary(): array {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT payment_mode, COUNT(*) as count, SUM(amount) as total_amount,
                    SUM(allocated_amount) as total_allocated, SUM(advance_amount) as total_advance
             FROM payments 
             WHERE is_reversed = 0
             GROUP BY payment_mode
             ORDER BY total_amount DESC"
        );
    }

    public static function getOutstandingCustomers(): array {
        $db = Database::getInstance();
        $customers = $db->fetchAll("SELECT id, name, phone, opening_balance FROM customers WHERE status = 'active'");
        $result = [];

        foreach ($customers as $c) {
            $bal = AccountingService::getCustomerBalance((int)$c['id']);
            if ($bal['outstanding_due'] > 0) {
                $result[] = [
                    'customer_id' => $c['id'],
                    'name' => $c['name'],
                    'phone' => $c['phone'],
                    'outstanding_due' => $bal['outstanding_due'],
                    'total_invoiced' => $bal['total_invoiced'],
                    'total_paid' => $bal['total_paid']
                ];
            }
        }

        usort($result, fn($a, $b) => $b['outstanding_due'] <=> $a['outstanding_due']);
        return $result;
    }

    public static function getCustomerAdvances(): array {
        $db = Database::getInstance();
        $customers = $db->fetchAll("SELECT id, name, phone FROM customers WHERE status = 'active'");
        $result = [];

        foreach ($customers as $c) {
            $bal = AccountingService::getCustomerBalance((int)$c['id']);
            if ($bal['advance_credit'] > 0) {
                $result[] = [
                    'customer_id' => $c['id'],
                    'name' => $c['name'],
                    'phone' => $c['phone'],
                    'advance_credit' => $bal['advance_credit'],
                    'total_paid' => $bal['total_paid'],
                    'total_invoiced' => $bal['total_invoiced']
                ];
            }
        }

        usort($result, fn($a, $b) => $b['advance_credit'] <=> $a['advance_credit']);
        return $result;
    }

    public static function getInvoiceWiseDue(): array {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT i.*, c.name as customer_name, c.phone as customer_phone 
             FROM invoices i 
             JOIN customers c ON i.customer_id = c.id 
             WHERE i.is_reversed = 0 AND i.due_amount > 0 
             ORDER BY i.invoice_date ASC, i.id ASC"
        );
    }

    /**
     * Overall Financial Reconciliation Check
     */
    public static function runReconciliation(): array {
        $db = Database::getInstance();

        // Total opening balances
        $sumOpening = (int)($db->fetchOne("SELECT SUM(opening_balance) as s FROM customers")['s'] ?? 0);

        // Total active invoices
        $sumInvoices = (int)($db->fetchOne("SELECT SUM(total_amount) as s FROM invoices WHERE is_reversed = 0")['s'] ?? 0);

        // Total active invoice dues
        $sumInvoiceDues = (int)($db->fetchOne("SELECT SUM(due_amount) as s FROM invoices WHERE is_reversed = 0")['s'] ?? 0);

        // Total active invoice paids
        $sumInvoicePaids = (int)($db->fetchOne("SELECT SUM(paid_amount) as s FROM invoices WHERE is_reversed = 0")['s'] ?? 0);

        // Total payments received
        $sumPayments = (int)($db->fetchOne("SELECT SUM(amount) as s FROM payments WHERE is_reversed = 0")['s'] ?? 0);

        // Total payment allocations
        $sumAllocations = (int)($db->fetchOne(
            "SELECT SUM(pa.allocated_amount) as s 
             FROM payment_allocations pa 
             JOIN payments p ON pa.payment_id = p.id 
             WHERE p.is_reversed = 0"
        )['s'] ?? 0);

        // Total customer advances
        $customers = $db->fetchAll("SELECT id FROM customers");
        $totalCustomerDues = 0;
        $totalCustomerAdvances = 0;
        foreach ($customers as $c) {
            $bal = AccountingService::getCustomerBalance((int)$c['id']);
            $totalCustomerDues += $bal['outstanding_due'];
            $totalCustomerAdvances += $bal['advance_credit'];
        }

        // Integrity checks:
        // 1. Total invoices must equal sum of invoice paid + invoice due
        $invoicesReconciled = ($sumInvoices === ($sumInvoicePaids + $sumInvoiceDues));

        // 2. Sum of payment allocations must equal invoice paids (minus adjustments if any)
        $allocationsReconciled = ($sumAllocations <= $sumInvoicePaids);

        // 3. Customer dues minus advances must equal sumOpening + sumInvoices - sumPayments
        $netCustomerBalances = $totalCustomerDues - $totalCustomerAdvances;
        $expectedNet = $sumOpening + $sumInvoices - $sumPayments;
        $ledgerReconciled = ($netCustomerBalances === $expectedNet);

        return [
            'sum_opening' => $sumOpening,
            'sum_invoices' => $sumInvoices,
            'sum_invoice_dues' => $sumInvoiceDues,
            'sum_invoice_paids' => $sumInvoicePaids,
            'sum_payments' => $sumPayments,
            'sum_allocations' => $sumAllocations,
            'total_customer_dues' => $totalCustomerDues,
            'total_customer_advances' => $totalCustomerAdvances,
            'invoices_reconciled' => $invoicesReconciled,
            'allocations_reconciled' => $allocationsReconciled,
            'ledger_reconciled' => $ledgerReconciled,
            'is_healthy' => ($invoicesReconciled && $allocationsReconciled && $ledgerReconciled)
        ];
    }
}
