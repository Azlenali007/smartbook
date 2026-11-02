<?php
/**
 * Smart Khata - Dashboard Controller
 * Computes live figures derived from real database transactions.
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\Database;
use SmartKhata\Services\AccountingService;

class DashboardController {
    public function index(): void {
        Auth::requireAuth();
        $db = Database::getInstance();

        // 1. Total customers count
        $customerCount = (int)($db->fetchOne("SELECT COUNT(*) as count FROM customers WHERE status = 'active'")['count'] ?? 0);

        // 2. Outstanding debt & customer advances (calculated across all customers)
        $customers = $db->fetchAll("SELECT id, name, phone FROM customers WHERE status = 'active'");
        $totalOutstandingDue = 0;
        $totalCustomerAdvances = 0;
        $highDueCustomers = [];

        foreach ($customers as $c) {
            $bal = AccountingService::getCustomerBalance((int)$c['id']);
            if ($bal['outstanding_due'] > 0) {
                $totalOutstandingDue += $bal['outstanding_due'];
                if ($bal['outstanding_due'] >= 1000000) { // >= ₹10,000 alert threshold
                    $highDueCustomers[] = [
                        'id' => $c['id'],
                        'name' => $c['name'],
                        'due' => $bal['outstanding_due']
                    ];
                }
            }
            if ($bal['advance_credit'] > 0) {
                $totalCustomerAdvances += $bal['advance_credit'];
            }
        }

        // 3. Today's figures
        $today = date('Y-m-d');
        $todaySalesRow = $db->fetchOne(
            "SELECT COALESCE(SUM(total_amount), 0) as total, COUNT(*) as count FROM invoices WHERE invoice_date = ? AND is_reversed = 0",
            [$today]
        );
        $todaySales = (int)($todaySalesRow['total'] ?? 0);
        $todayInvoiceCount = (int)($todaySalesRow['count'] ?? 0);

        $todayPaymentsRow = $db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE payment_date = ? AND is_reversed = 0",
            [$today]
        );
        $todayPayments = (int)($todayPaymentsRow['total'] ?? 0);

        // 4. Recent invoices
        $recentInvoices = $db->fetchAll(
            "SELECT i.*, c.name as customer_name 
             FROM invoices i 
             JOIN customers c ON i.customer_id = c.id 
             ORDER BY i.created_at DESC LIMIT 6"
        );

        // 5. Recent payments
        $recentPayments = $db->fetchAll(
            "SELECT p.*, c.name as customer_name 
             FROM payments p 
             JOIN customers c ON p.customer_id = c.id 
             ORDER BY p.created_at DESC LIMIT 6"
        );

        require SK_APP . '/Views/dashboard/index.php';
    }
}
