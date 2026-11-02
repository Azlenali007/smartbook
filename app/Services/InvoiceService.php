<?php
/**
 * Smart Khata - Invoice Management & Creation Service
 * Atomic creation, sequential numbering, manual item entry, and audit reversals.
 */

namespace SmartKhata\Services;

use SmartKhata\Database;
use DateTime;
use Exception;

class InvoiceService {
    /**
     * Generate next sequential invoice number based on prefix and settings
     */
    public static function generateInvoiceNumber(): string {
        $db = Database::getInstance();
        $prefix = 'INV-';
        $prefixSetting = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'invoice_prefix'");
        if ($prefixSetting && !empty($prefixSetting['setting_value'])) {
            $prefix = $prefixSetting['setting_value'];
        }

        $year = date('Y');
        // Count invoices this year to find sequential sequence
        $likePattern = $prefix . $year . '-%';
        $countRow = $db->fetchOne("SELECT COUNT(*) as count FROM invoices WHERE invoice_number LIKE ?", [$likePattern]);
        $nextSeq = (int)($countRow['count'] ?? 0) + 1;

        // Ensure uniqueness loop
        do {
            $invNum = sprintf("%s%s-%04d", $prefix, $year, $nextSeq);
            $exists = $db->fetchOne("SELECT id FROM invoices WHERE invoice_number = ?", [$invNum]);
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        return $invNum;
    }

    /**
     * Create Invoice atomically with items and ledger entry
     */
    public static function createInvoice(array $data): array {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $customerId = (int)($data['customer_id'] ?? 0);
            if ($customerId <= 0) {
                throw new Exception("Please select a valid customer.");
            }

            $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$customerId]);
            if (!$customer) {
                throw new Exception("Selected customer does not exist.");
            }

            $invoiceDate = !empty($data['invoice_date']) ? $data['invoice_date'] : date('Y-m-d');
            $notes = trim($data['notes'] ?? '');
            $items = $data['items'] ?? [];

            if (empty($items) || !is_array($items)) {
                throw new Exception("Invoice must contain at least one line item.");
            }

            $subtotalPaise = 0;
            $validatedItems = [];

            foreach ($items as $index => $item) {
                $itemName = trim($item['item_name'] ?? '');
                $qty = (int)($item['quantity'] ?? 0);
                $rateInput = $item['rate'] ?? 0;

                if ($itemName === '') {
                    throw new Exception("Item #" . ($index + 1) . " requires a name.");
                }

                if ($qty <= 0) {
                    throw new Exception("Item '{$itemName}' quantity must be greater than zero pieces.");
                }

                $ratePaise = AccountingService::rupeesToPaise($rateInput);
                if ($ratePaise < 0) {
                    throw new Exception("Item '{$itemName}' rate cannot be negative.");
                }

                $lineTotalPaise = $qty * $ratePaise;
                $subtotalPaise += $lineTotalPaise;

                $validatedItems[] = [
                    'item_name' => $itemName,
                    'quantity' => $qty,
                    'unit' => 'Piece',
                    'unit_price' => $ratePaise,
                    'line_total' => $lineTotalPaise
                ];
            }

            // Optional GST calculation (if enabled in settings)
            $taxAmountPaise = 0;
            $gstRateSetting = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'gst_rate'");
            if ($gstRateSetting && (float)$gstRateSetting['setting_value'] > 0) {
                $gstRate = (float)$gstRateSetting['setting_value'];
                $taxAmountPaise = (int)round(($subtotalPaise * $gstRate) / 100);
            }

            $totalAmountPaise = $subtotalPaise + $taxAmountPaise;
            $invoiceNumber = self::generateInvoiceNumber();

            // Insert invoice record
            $db->execute(
                "INSERT INTO invoices (
                    customer_id, invoice_number, invoice_date, subtotal, tax_amount,
                    total_amount, paid_amount, due_amount, status, notes, is_reversed, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'unpaid', ?, 0, ?, ?)",
                [
                    $customerId,
                    $invoiceNumber,
                    $invoiceDate,
                    $subtotalPaise,
                    $taxAmountPaise,
                    $totalAmountPaise,
                    $totalAmountPaise,
                    $notes,
                    (new DateTime())->format('Y-m-d H:i:s'),
                    (new DateTime())->format('Y-m-d H:i:s')
                ]
            );

            $invoiceId = (int)$db->lastInsertId();

            // Insert line items
            foreach ($validatedItems as $item) {
                $db->execute(
                    "INSERT INTO invoice_items (invoice_id, item_name, quantity, unit, unit_price, line_total)
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $invoiceId,
                        $item['item_name'],
                        $item['quantity'],
                        $item['unit'],
                        $item['unit_price'],
                        $item['line_total']
                    ]
                );
            }

            // Record in Customer Ledger as Debit (Customer owes more)
            AccountingService::recordLedgerEntry(
                $customerId,
                $invoiceDate,
                'invoice',
                $invoiceId,
                $invoiceNumber,
                "Invoice #{$invoiceNumber} (" . count($validatedItems) . " items)",
                $totalAmountPaise,
                0 // Credit is 0
            );

            // Auto-check: If customer has existing advance credit, can we automatically suggest or apply?
            // User requested that payments be allocated, and advance may be adjusted with traceable transaction.
            // If the user checked 'auto_apply_advance' and customer has advance:
            if (!empty($data['apply_advance']) && $data['apply_advance'] === 'yes') {
                $balance = AccountingService::getCustomerBalance($customerId);
                if ($balance['advance_credit'] > 0) {
                    $applyAmount = min($balance['advance_credit'], $totalAmountPaise);
                    AllocationService::applyAdvanceToInvoice($customerId, $invoiceId, $applyAmount);
                }
            }

            $db->commit();

            return [
                'success' => true,
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $totalAmountPaise
            ];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Reverse an issued invoice safely with an audit trail
     */
    public static function reverseInvoice(int $invoiceId, string $reason): array {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $invoice = $db->fetchOne("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
            if (!$invoice) {
                throw new Exception("Invoice not found.");
            }

            if ((int)$invoice['is_reversed'] === 1) {
                throw new Exception("Invoice has already been reversed.");
            }

            if (trim($reason) === '') {
                throw new Exception("A detailed reversal reason is mandatory.");
            }

            // If payments were allocated to this invoice, un-allocate them
            // The payment amount becomes unallocated advance for the customer!
            $allocations = $db->fetchAll("SELECT * FROM payment_allocations WHERE invoice_id = ?", [$invoiceId]);
            foreach ($allocations as $alloc) {
                $payId = (int)$alloc['payment_id'];
                $allocAmount = (int)$alloc['allocated_amount'];

                // Update payment: reduce allocated_amount, increase advance_amount
                $db->execute(
                    "UPDATE payments SET allocated_amount = allocated_amount - ?, advance_amount = advance_amount + ? WHERE id = ?",
                    [$allocAmount, $allocAmount, $payId]
                );

                // Delete allocation link
                $db->execute("DELETE FROM payment_allocations WHERE id = ?", [$alloc['id']]);
            }

            // Mark invoice as reversed
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $db->execute(
                "UPDATE invoices SET is_reversed = 1, status = 'reversed', reversal_reason = ?, reversed_at = ?, updated_at = ? WHERE id = ?",
                [$reason, $now, $now, $invoiceId]
            );

            // Record Reversal in Customer Ledger (Credit entry of the invoice amount to reverse the debit)
            AccountingService::recordLedgerEntry(
                (int)$invoice['customer_id'],
                date('Y-m-d'),
                'reversal',
                $invoiceId,
                $invoice['invoice_number'],
                "Reversal of Invoice #{$invoice['invoice_number']} — Reason: {$reason}",
                0, // Debit 0
                (int)$invoice['total_amount'] // Credit reverses the previous debit
            );

            $db->commit();
            return ['success' => true, 'message' => "Invoice #{$invoice['invoice_number']} successfully reversed."];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Get full invoice detail with items, customer details, and payment allocations
     */
    public static function getInvoiceDetails(int $invoiceId): ?array {
        $db = Database::getInstance();
        $invoice = $db->fetchOne(
            "SELECT i.*, c.name as customer_name, c.phone as customer_phone, c.address as customer_address 
             FROM invoices i 
             JOIN customers c ON i.customer_id = c.id 
             WHERE i.id = ?",
            [$invoiceId]
        );

        if (!$invoice) return null;

        $items = $db->fetchAll("SELECT * FROM invoice_items WHERE invoice_id = ?", [$invoiceId]);
        $allocations = $db->fetchAll(
            "SELECT pa.*, p.payment_number, p.payment_date, p.payment_mode 
             FROM payment_allocations pa 
             JOIN payments p ON pa.payment_id = p.id 
             WHERE pa.invoice_id = ?",
            [$invoiceId]
        );

        // Previous balance before this invoice
        $prevBalance = AccountingService::getCustomerBalance((int)$invoice['customer_id']);

        return [
            'invoice' => $invoice,
            'items' => $items,
            'allocations' => $allocations,
            'customer_balance' => $prevBalance
        ];
    }
}
