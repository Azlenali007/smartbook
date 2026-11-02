<?php
/**
 * Smart Khata - Standalone Pure-PHP PDF Generator
 * Complies with PDF-1.4 specification to render invoices, receipts, and ledger statements.
 * Zero external dependencies required.
 */

namespace SmartKhata\Services;

class PdfService {
    private array $objects = [];
    private string $buffer = '';
    private int $pageCount = 0;
    private array $pageObjects = [];

    public function __construct() {
        $this->buffer = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
    }

    private function addObject(string $content): int {
        $id = count($this->objects) + 1;
        $this->objects[$id] = $content;
        return $id;
    }

    private function escapeText(string $text): string {
        // Convert UTF-8 currency and symbols to safe ASCII representation for standard Type1 fonts
        $text = str_replace('₹', 'Rs. ', $text);
        $text = iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        return $text;
    }

    /**
     * Render an invoice PDF
     */
    public static function generateInvoicePdf(array $invoiceData, array $settings): string {
        $invoice = $invoiceData['invoice'];
        $items = $invoiceData['items'];
        $shopName = $settings['shop_name'] ?? 'Smart Khata';
        $shopPhone = $settings['shop_phone'] ?? '';
        $shopAddress = $settings['shop_address'] ?? '';
        $gstin = $settings['shop_gstin'] ?? '';

        $pdf = new self();
        $stream = "";

        // Header Background
        $stream .= "0.06 0.09 0.16 rg\n"; // Navy
        $stream .= "30 750 535 70 re f\n";

        // Header Titles
        $stream .= "BT\n/F2 20 Tf\n1 1 1 rg\n50 785 Td (" . $pdf->escapeText($shopName) . ") Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n0.8 0.85 0.95 rg\n50 768 Td (" . $pdf->escapeText($shopAddress . ($shopPhone ? " | Phone: {$shopPhone}" : "")) . ") Tj\nET\n";

        // Invoice Badge
        $stream .= "BT\n/F2 16 Tf\n1 1 1 rg\n420 785 Td (TAX INVOICE) Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n0.8 0.85 0.95 rg\n420 768 Td (" . $pdf->escapeText($invoice['invoice_number']) . ") Tj\nET\n";

        // Customer & Invoice Metadata Box
        $stream .= "0.95 0.96 0.98 rg\n30 670 535 65 re f\n";
        $stream .= "0.85 0.88 0.92 RG\n1 w\n30 670 535 65 re S\n";

        $dateFormatted = date('d-m-Y', strtotime($invoice['invoice_date']));
        $stream .= "BT\n/F2 11 Tf\n0.1 0.1 0.1 rg\n45 715 Td (Billed To:) Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n0.2 0.2 0.2 rg\n45 700 Td (" . $pdf->escapeText($invoice['customer_name']) . ") Tj\nET\n";
        $stream .= "BT\n/F1 9 Tf\n0.4 0.4 0.4 rg\n45 685 Td (" . $pdf->escapeText("Phone: " . ($invoice['customer_phone'] ?: 'N/A') . " | " . ($invoice['customer_address'] ?: '')) . ") Tj\nET\n";

        $stream .= "BT\n/F2 10 Tf\n0.1 0.1 0.1 rg\n400 715 Td (Invoice Date: ) Tj /F1 10 Tf (" . $pdf->escapeText($dateFormatted) . ") Tj\nET\n";
        $statusText = strtoupper($invoice['status']);
        $stream .= "BT\n/F2 10 Tf\n0.1 0.1 0.1 rg\n400 695 Td (Payment Status: ) Tj /F2 10 Tf (" . $pdf->escapeText($statusText) . ") Tj\nET\n";

        // Items Table Header
        $stream .= "0.15 0.23 0.36 rg\n30 635 535 24 re f\n";
        $stream .= "BT\n/F2 10 Tf\n1 1 1 rg\n";
        $stream .= "45 643 Td (#) Tj\n";
        $stream .= "80 643 Td (Item Description) Tj\n";
        $stream .= "330 643 Td (Qty) Tj\n";
        $stream .= "390 643 Td (Rate (Rs.)) Tj\n";
        $stream .= "475 643 Td (Amount (Rs.)) Tj\n";
        $stream .= "ET\n";

        // Table Rows
        $y = 615;
        $idx = 1;
        foreach ($items as $item) {
            // Row background alternating
            if ($idx % 2 === 0) {
                $stream .= "0.98 0.98 0.99 rg\n30 " . ($y - 5) . " 535 20 re f\n";
            }
            $stream .= "0.9 0.9 0.9 RG\n30 " . ($y - 5) . " 535 0.5 re S\n";

            $rateRs = number_format($item['unit_price'] / 100, 2);
            $totalRs = number_format($item['line_total'] / 100, 2);

            $stream .= "BT\n/F1 10 Tf\n0.15 0.15 0.15 rg\n";
            $stream .= "45 {$y} Td ({$idx}) Tj\n";
            $stream .= "80 {$y} Td (" . $pdf->escapeText($item['item_name']) . ") Tj\n";
            $stream .= "330 {$y} Td (" . $pdf->escapeText($item['quantity'] . " Piece") . ") Tj\n";
            $stream .= "390 {$y} Td ({$rateRs}) Tj\n";
            $stream .= "475 {$y} Td ({$totalRs}) Tj\n";
            $stream .= "ET\n";

            $y -= 22;
            $idx++;
            if ($y < 180) break; // Keep inside page
        }

        // Summary Box
        $y -= 10;
        $stream .= "0.95 0.96 0.98 rg\n340 " . ($y - 75) . " 225 80 re f\n";
        $stream .= "0.85 0.88 0.92 RG\n340 " . ($y - 75) . " 225 80 re S\n";

        $subtotalRs = number_format($invoice['subtotal'] / 100, 2);
        $taxRs = number_format($invoice['tax_amount'] / 100, 2);
        $totalRs = number_format($invoice['total_amount'] / 100, 2);
        $paidRs = number_format($invoice['paid_amount'] / 100, 2);
        $dueRs = number_format($invoice['due_amount'] / 100, 2);

        $stream .= "BT\n/F1 10 Tf\n0.3 0.3 0.3 rg\n";
        $stream .= "355 " . ($y - 12) . " Td (Subtotal:) Tj 480 " . ($y - 12) . " Td (Rs. {$subtotalRs}) Tj\n";
        if ((int)$invoice['tax_amount'] > 0) {
            $stream .= "355 " . ($y - 28) . " Td (Tax/GST:) Tj 480 " . ($y - 28) . " Td (Rs. {$taxRs}) Tj\n";
        }
        $stream .= "ET\n";

        $stream .= "BT\n/F2 12 Tf\n0.06 0.09 0.16 rg\n";
        $stream .= "355 " . ($y - 45) . " Td (Total Amount:) Tj 475 " . ($y - 45) . " Td (Rs. {$totalRs}) Tj\n";
        $stream .= "ET\n";

        $stream .= "BT\n/F2 10 Tf\n0.8 0.1 0.1 rg\n";
        $stream .= "355 " . ($y - 65) . " Td (Remaining Due:) Tj 475 " . ($y - 65) . " Td (Rs. {$dueRs}) Tj\n";
        $stream .= "ET\n";

        // Footer & Terms
        $stream .= "BT\n/F1 8 Tf\n0.5 0.5 0.5 rg\n";
        $stream .= "50 80 Td (This is a computer-generated invoice. No physical signature required.) Tj\n";
        $stream .= "50 68 Td (Generated by Smart Khata — Thank you for your business!) Tj\n";
        $stream .= "ET\n";

        return $pdf->buildDocument($stream);
    }

    /**
     * Render Payment Receipt PDF
     */
    public static function generateReceiptPdf(array $payment, array $customer, array $settings): string {
        $shopName = $settings['shop_name'] ?? 'Smart Khata';
        $shopPhone = $settings['shop_phone'] ?? '';
        $shopAddress = $settings['shop_address'] ?? '';

        $pdf = new self();
        $stream = "";

        // Header Background
        $stream .= "0.06 0.09 0.16 rg\n30 750 535 70 re f\n";
        $stream .= "BT\n/F2 18 Tf\n1 1 1 rg\n50 785 Td (" . $pdf->escapeText($shopName) . ") Tj\nET\n";
        $stream .= "BT\n/F1 9 Tf\n0.8 0.85 0.95 rg\n50 768 Td (" . $pdf->escapeText($shopAddress . " | Phone: {$shopPhone}") . ") Tj\nET\n";
        $stream .= "BT\n/F2 15 Tf\n1 1 1 rg\n410 785 Td (PAYMENT RECEIPT) Tj\nET\n";
        $stream .= "BT\n/F1 9 Tf\n0.8 0.85 0.95 rg\n410 768 Td (" . $pdf->escapeText($payment['payment_number']) . ") Tj\nET\n";

        // Receipt Details Box
        $stream .= "0.97 0.98 0.99 rg\n30 520 535 210 re f\n";
        $stream .= "0.85 0.88 0.92 RG\n30 520 535 210 re S\n";

        $dateFormatted = date('d-m-Y', strtotime($payment['payment_date']));
        $amountRs = number_format($payment['amount'] / 100, 2);
        $allocatedRs = number_format($payment['allocated_amount'] / 100, 2);
        $advanceRs = number_format($payment['advance_amount'] / 100, 2);

        $stream .= "BT\n/F2 12 Tf\n0.1 0.1 0.1 rg\n60 700 Td (Received From:) Tj /F1 12 Tf\n190 700 Td (" . $pdf->escapeText($customer['name']) . ") Tj\nET\n";
        $stream .= "BT\n/F2 11 Tf\n0.1 0.1 0.1 rg\n60 670 Td (Payment Date:) Tj /F1 11 Tf\n190 670 Td ({$dateFormatted}) Tj\nET\n";
        $stream .= "BT\n/F2 11 Tf\n0.1 0.1 0.1 rg\n60 640 Td (Payment Mode:) Tj /F1 11 Tf\n190 640 Td (" . $pdf->escapeText($payment['payment_mode']) . ") Tj\nET\n";
        if (!empty($payment['reference_no'])) {
            $stream .= "BT\n/F2 11 Tf\n0.1 0.1 0.1 rg\n60 610 Td (Reference / UTR:) Tj /F1 11 Tf\n190 610 Td (" . $pdf->escapeText($payment['reference_no']) . ") Tj\nET\n";
        }

        // Amount Box Highlight
        $stream .= "0.02 0.6 0.35 rg\n60 545 475 40 re f\n";
        $stream .= "BT\n/F2 15 Tf\n1 1 1 rg\n80 560 Td (Total Amount Received: Rs. {$amountRs}) Tj\nET\n";

        // Allocations breakdown
        $stream .= "BT\n/F1 10 Tf\n0.3 0.3 0.3 rg\n";
        $stream .= "60 480 Td (Amount Allocated to Invoices: Rs. {$allocatedRs}) Tj\n";
        $stream .= "60 460 Td (Amount Retained as Customer Advance: Rs. {$advanceRs}) Tj\n";
        if (!empty($payment['notes'])) {
            $stream .= "60 435 Td (" . $pdf->escapeText("Notes: " . $payment['notes']) . ") Tj\n";
        }
        $stream .= "ET\n";

        return $pdf->buildDocument($stream);
    }

    /**
     * Render Customer Ledger Statement PDF
     */
    public static function generateLedgerPdf(array $customer, array $entries, array $balance, array $settings): string {
        $shopName = $settings['shop_name'] ?? 'Smart Khata';
        $pdf = new self();
        $stream = "";

        // Header
        $stream .= "0.06 0.09 0.16 rg\n30 750 535 70 re f\n";
        $stream .= "BT\n/F2 18 Tf\n1 1 1 rg\n50 785 Td (" . $pdf->escapeText($shopName) . ") Tj\nET\n";
        $stream .= "BT\n/F1 9 Tf\n0.8 0.85 0.95 rg\n50 768 Td (CUSTOMER ACCOUNT STATEMENT / LEDGER) Tj\nET\n";

        // Customer Summary
        $stream .= "0.95 0.96 0.98 rg\n30 675 535 60 re f\n";
        $stream .= "BT\n/F2 11 Tf\n0.1 0.1 0.1 rg\n45 715 Td (" . $pdf->escapeText($customer['name']) . ") Tj\nET\n";
        $stream .= "BT\n/F1 9 Tf\n0.3 0.3 0.3 rg\n45 700 Td (" . $pdf->escapeText("Phone: " . ($customer['phone'] ?: 'N/A') . " | Address: " . ($customer['address'] ?: 'N/A')) . ") Tj\nET\n";

        $netRs = number_format(abs($balance['net_balance']) / 100, 2);
        $statusStr = $balance['net_balance'] > 0 ? "TOTAL DUE: Rs. {$netRs}" : ($balance['net_balance'] < 0 ? "ADVANCE BALANCE: Rs. {$netRs}" : "ACCOUNT SETTLED");
        $stream .= "BT\n/F2 11 Tf\n0.06 0.09 0.16 rg\n380 705 Td (" . $pdf->escapeText($statusStr) . ") Tj\nET\n";

        // Ledger Table Header
        $stream .= "0.15 0.23 0.36 rg\n30 640 535 22 re f\n";
        $stream .= "BT\n/F2 9 Tf\n1 1 1 rg\n";
        $stream .= "40 647 Td (Date) Tj\n";
        $stream .= "105 647 Td (Type) Tj\n";
        $stream .= "165 647 Td (Reference / Description) Tj\n";
        $stream .= "360 647 Td (Debit (Rs.)) Tj\n";
        $stream .= "430 647 Td (Credit (Rs.)) Tj\n";
        $stream .= "500 647 Td (Balance) Tj\n";
        $stream .= "ET\n";

        $y = 620;
        $count = 0;
        foreach ($entries as $entry) {
            $date = date('d-m-Y', strtotime($entry['entry_date']));
            $type = strtoupper(str_replace('_', ' ', $entry['entry_type']));
            $desc = substr($entry['description'], 0, 32);
            $debit = $entry['debit'] > 0 ? number_format($entry['debit'] / 100, 2) : '-';
            $credit = $entry['credit'] > 0 ? number_format($entry['credit'] / 100, 2) : '-';
            $bal = number_format(abs($entry['running_balance']) / 100, 2) . ($entry['running_balance'] > 0 ? ' Dr' : ($entry['running_balance'] < 0 ? ' Cr' : ''));

            if ($count % 2 === 1) {
                $stream .= "0.98 0.98 0.99 rg\n30 " . ($y - 4) . " 535 18 re f\n";
            }
            $stream .= "0.9 0.9 0.9 RG\n30 " . ($y - 4) . " 535 0.5 re S\n";

            $stream .= "BT\n/F1 8 Tf\n0.15 0.15 0.15 rg\n";
            $stream .= "40 {$y} Td ({$date}) Tj\n";
            $stream .= "105 {$y} Td ({$type}) Tj\n";
            $stream .= "165 {$y} Td (" . $pdf->escapeText($desc) . ") Tj\n";
            $stream .= "360 {$y} Td ({$debit}) Tj\n";
            $stream .= "430 {$y} Td ({$credit}) Tj\n";
            $stream .= "500 {$y} Td ({$bal}) Tj\n";
            $stream .= "ET\n";

            $y -= 19;
            $count++;
            if ($y < 70) break;
        }

        return $pdf->buildDocument($stream);
    }

    private function buildDocument(string $pageStream): string {
        $contentLength = strlen($pageStream);
        $contentObjId = $this->addObject("<< /Length {$contentLength} >>\nstream\n{$pageStream}\nendstream");

        // Font 1: Helvetica
        $font1Id = $this->addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
        // Font 2: Helvetica-Bold
        $font2Id = $this->addObject("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");

        // Page Object
        $pageObjId = $this->addObject("<< /Type /Page /Parent 4 0 R /MediaBox [0 0 595.28 841.89] /Contents {$contentObjId} 0 R /Resources << /Font << /F1 {$font1Id} 0 R /F2 {$font2Id} 0 R >> >> >>");

        // Pages Object
        $pagesObjId = $this->addObject("<< /Type /Pages /Kids [{$pageObjId} 0 R] /Count 1 >>");

        // Catalog
        $catalogId = $this->addObject("<< /Type /Catalog /Pages {$pagesObjId} 0 R >>");

        // Render PDF binary string with exact cross-reference table
        $output = "%PDF-1.4\n";
        $xref = [0 => 0];

        foreach ($this->objects as $id => $obj) {
            $xref[$id] = strlen($output);
            $output .= "{$id} 0 obj\n{$obj}\nendobj\n";
        }

        $xrefStart = strlen($output);
        $output .= "xref\n0 " . (count($this->objects) + 1) . "\n";
        $output .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($this->objects); $i++) {
            $output .= sprintf("%010d 00000 n \n", $xref[$i]);
        }

        $output .= "trailer\n<< /Size " . (count($this->objects) + 1) . " /Root {$catalogId} 0 R >>\n";
        $output .= "startxref\n{$xrefStart}\n%%EOF\n";

        return $output;
    }
}
