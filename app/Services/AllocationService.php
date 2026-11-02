<?php
/**
 * Smart Khata - Payment Allocation & Advance Settlement Service
 * Manages FIFO automatic allocation, manual allocation, advance credit retention,
 * and advance adjustments against future or existing unpaid invoices.
 */

namespace SmartKhata\Services;

use SmartKhata\Database;
use DateTime;
use Exception;

class AllocationService {
    /**
     * Allocate payment automatically (FIFO - oldest unpaid first)
     */
    public static function allocatePaymentAutomatic(int $paymentId, int $customerId, int $totalPaymentPaise): array {
        $db = Database::getInstance();

        // Fetch all unpaid or partially paid invoices ordered by oldest first
        $invoices = $db->fetchAll(
            "SELECT id, invoice_number, total_amount, paid_amount, due_amount 
             FROM invoices 
             WHERE customer_id = ? AND is_reversed = 0 AND due_amount > 0 
             ORDER BY invoice_date ASC, id ASC",
            [$customerId]
        );

        $remainingToAllocate = $totalPaymentPaise;
        $allocationsMade = [];
        $totalAllocated = 0;

        foreach ($invoices as $inv) {
            if ($remainingToAllocate <= 0) break;

            $invDue = (int)$inv['due_amount'];
            $allocateNow = min($remainingToAllocate, $invDue);

            if ($allocateNow > 0) {
                // Record allocation
                $db->execute(
                    "INSERT INTO payment_allocations (payment_id, invoice_id, allocated_amount, created_at) VALUES (?, ?, ?, ?)",
                    [$paymentId, $inv['id'], $allocateNow, (new DateTime())->format('Y-m-d H:i:s')]
                );

                $newPaid = (int)$inv['paid_amount'] + $allocateNow;
                $newDue = $invDue - $allocateNow;
                $newStatus = ($newDue === 0) ? 'paid' : 'partial';

                $db->execute(
                    "UPDATE invoices SET paid_amount = ?, due_amount = ?, status = ?, updated_at = ? WHERE id = ?",
                    [$newPaid, $newDue, $newStatus, (new DateTime())->format('Y-m-d H:i:s'), $inv['id']]
                );

                $allocationsMade[] = [
                    'invoice_id' => $inv['id'],
                    'invoice_number' => $inv['invoice_number'],
                    'allocated' => $allocateNow,
                    'remaining_invoice_due' => $newDue
                ];

                $totalAllocated += $allocateNow;
                $remainingToAllocate -= $allocateNow;
            }
        }

        // Any excess is customer advance
        $advanceRetained = $remainingToAllocate;

        // Update payment with allocated and advance amounts
        $db->execute(
            "UPDATE payments SET allocated_amount = ?, advance_amount = ?, updated_at = ? WHERE id = ?",
            [$totalAllocated, $advanceRetained, (new DateTime())->format('Y-m-d H:i:s'), $paymentId]
        );

        return [
            'total_payment' => $totalPaymentPaise,
            'total_allocated' => $totalAllocated,
            'advance_retained' => $advanceRetained,
            'allocations' => $allocationsMade
        ];
    }

    /**
     * Allocate payment manually with explicit amounts per invoice
     * $allocationsMap is [ invoice_id => paise_amount ]
     */
    public static function allocatePaymentManual(int $paymentId, int $customerId, int $totalPaymentPaise, array $allocationsMap): array {
        $db = Database::getInstance();

        $totalAllocated = 0;
        $allocationsMade = [];

        foreach ($allocationsMap as $invoiceId => $allocAmountPaise) {
            $allocAmount = (int)$allocAmountPaise;
            if ($allocAmount <= 0) continue;

            $inv = $db->fetchOne(
                "SELECT id, invoice_number, due_amount, paid_amount FROM invoices WHERE id = ? AND customer_id = ? AND is_reversed = 0",
                [$invoiceId, $customerId]
            );

            if (!$inv) {
                throw new Exception("Invoice ID {$invoiceId} not found or does not belong to this customer.");
            }

            if ($allocAmount > (int)$inv['due_amount']) {
                $maxRupees = AccountingService::paiseToRupees((int)$inv['due_amount']);
                throw new Exception("Cannot allocate {$allocAmount} paise to invoice {$inv['invoice_number']}. Maximum due is {$maxRupees}.");
            }

            $totalAllocated += $allocAmount;

            if ($totalAllocated > $totalPaymentPaise) {
                throw new Exception("Sum of allocations exceeds total payment amount.");
            }

            // Record allocation
            $db->execute(
                "INSERT INTO payment_allocations (payment_id, invoice_id, allocated_amount, created_at) VALUES (?, ?, ?, ?)",
                [$paymentId, $inv['id'], $allocAmount, (new DateTime())->format('Y-m-d H:i:s')]
            );

            $newPaid = (int)$inv['paid_amount'] + $allocAmount;
            $newDue = (int)$inv['due_amount'] - $allocAmount;
            $newStatus = ($newDue === 0) ? 'paid' : 'partial';

            $db->execute(
                "UPDATE invoices SET paid_amount = ?, due_amount = ?, status = ?, updated_at = ? WHERE id = ?",
                [$newPaid, $newDue, $newStatus, (new DateTime())->format('Y-m-d H:i:s'), $inv['id']]
            );

            $allocationsMade[] = [
                'invoice_id' => $inv['id'],
                'invoice_number' => $inv['invoice_number'],
                'allocated' => $allocAmount,
                'remaining_invoice_due' => $newDue
            ];
        }

        $advanceRetained = $totalPaymentPaise - $totalAllocated;

        $db->execute(
            "UPDATE payments SET allocated_amount = ?, advance_amount = ?, updated_at = ? WHERE id = ?",
            [$totalAllocated, $advanceRetained, (new DateTime())->format('Y-m-d H:i:s'), $paymentId]
        );

        return [
            'total_payment' => $totalPaymentPaise,
            'total_allocated' => $totalAllocated,
            'advance_retained' => $advanceRetained,
            'allocations' => $allocationsMade
        ];
    }

    /**
     * Apply customer's available advance credit towards an unpaid invoice
     */
    public static function applyAdvanceToInvoice(int $customerId, int $invoiceId, int $amountToApplyPaise): array {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $balance = AccountingService::getCustomerBalance($customerId);
            $availableAdvance = $balance['advance_credit'];

            if ($amountToApplyPaise <= 0 || $amountToApplyPaise > $availableAdvance) {
                $maxAdv = AccountingService::paiseToRupees($availableAdvance);
                throw new Exception("Invalid amount. Available advance credit is {$maxAdv}.");
            }

            $inv = $db->fetchOne(
                "SELECT id, invoice_number, due_amount, paid_amount FROM invoices WHERE id = ? AND customer_id = ? AND is_reversed = 0",
                [$invoiceId, $customerId]
            );

            if (!$inv) {
                throw new Exception("Invoice not found.");
            }

            if ($amountToApplyPaise > (int)$inv['due_amount']) {
                $maxDue = AccountingService::paiseToRupees((int)$inv['due_amount']);
                throw new Exception("Cannot apply more than invoice due amount ({$maxDue}).");
            }

            // Generate adjustment record
            $adjNum = 'ADJ-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $db->execute(
                "INSERT INTO adjustments (customer_id, adjustment_number, adjustment_date, invoice_id, amount, reason, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $customerId,
                    $adjNum,
                    date('Y-m-d'),
                    $invoiceId,
                    $amountToApplyPaise,
                    "Advance credit applied to invoice {$inv['invoice_number']}",
                    (new DateTime())->format('Y-m-d H:i:s')
                ]
            );
            $adjId = (int)$db->lastInsertId();

            // Update invoice paid & due
            $newPaid = (int)$inv['paid_amount'] + $amountToApplyPaise;
            $newDue = (int)$inv['due_amount'] - $amountToApplyPaise;
            $newStatus = ($newDue === 0) ? 'paid' : 'partial';

            $db->execute(
                "UPDATE invoices SET paid_amount = ?, due_amount = ?, status = ?, updated_at = ? WHERE id = ?",
                [$newPaid, $newDue, $newStatus, (new DateTime())->format('Y-m-d H:i:s'), $invoiceId]
            );

            // Record in ledger: Advance Adjustment
            // Note: Since advance is already reflected in customer's overall credit balance,
            // this links the advance to the invoice. In customer's running balance, applying advance
            // to an unpaid invoice does not alter net balance, but documents the settlement.
            AccountingService::recordLedgerEntry(
                $customerId,
                date('Y-m-d'),
                'advance_adjustment',
                $adjId,
                $adjNum,
                "Advance credit ₹" . number_format($amountToApplyPaise / 100, 2) . " applied to Invoice {$inv['invoice_number']}",
                0,
                0 // zero debit/credit on customer net, but recorded in chronological ledger
            );

            $db->commit();
            return ['success' => true, 'adjustment_number' => $adjNum, 'applied' => $amountToApplyPaise];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
