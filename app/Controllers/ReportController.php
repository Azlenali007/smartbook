<?php
/**
 * Smart Khata - Reports & Analytics Controller
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\Services\ReportService;

class ReportController {
    public function index(): void {
        Auth::requireAuth();

        $tab = $_GET['tab'] ?? 'sales';
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // Start of current month
        $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today

        $dailySales = ReportService::getDailySales($startDate, $endDate);
        $customerSales = ReportService::getCustomerSales();
        $dailyCollections = ReportService::getDailyCollections($startDate, $endDate);
        $paymentModes = ReportService::getPaymentModesSummary();
        $outstanding = ReportService::getOutstandingCustomers();
        $advances = ReportService::getCustomerAdvances();
        $invoiceDues = ReportService::getInvoiceWiseDue();
        $reconciliation = ReportService::runReconciliation();

        require SK_APP . '/Views/reports/index.php';
    }
}
