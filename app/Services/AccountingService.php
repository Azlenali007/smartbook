<?php
/**
 * Smart Khata - Authoritative Accounting & Financial Calculation Engine
 * Strictly uses integer paise arithmetic. 100 paise = ₹1.00.
 * Implements Section 6 financial rules and customer balance tracking.
 */

namespace SmartKhata\Services;

use SmartKhata\Database;
use DateTime;
use Exception;

class AccountingService {
    /**
     * Convert float/string rupees into integer paise (e.g. "120.50" => 12050)
     */
    public static function rupeesToPaise(float|string|int $rupees): int {
        if (is_int($rupees)) {
            // Already integer rupees? If passed as whole rupee, e.g. 500 => 50000
            return (int)round((float)$rupees * 100);
        }
        $cleaned = trim((string)$rupees);
        if ($cleaned === '') return 0;
        // Clean currency signs, commas
        $cleaned = str_replace(['₹', ',', ' '], '', $cleaned);
        return (int)round(((float)$cleaned) * 100);
    }

    /**
     * Convert integer paise to display string (e.g. 12050 => "₹120.50")
     */
    public static function paiseToRupees(int $paise, bool $withSymbol = true): string {
        $sign = $paise < 0 ? '-' : '';
        $absPaise = abs($paise);
        $rupees = (int)($absPaise / 100);
        $remainder = $absPaise % 100;
        $cents = str_pad((string)$remainder, 2, '0', STR_PAD_LEFT);

        // Indian currency comma grouping
        $rupeeStr = (string)$rupees;
        if (strlen($rupeeStr) > 3) {
            $lastThree = substr($rupeeStr, -3);
            $rest = substr($rupeeStr, 0, -3);
            $restFormatted = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
            $formattedNumber = $restFormatted . ',' . $lastThree;
        } else {
            $formattedNumber = $rupeeStr;
        }

        $formatted = $sign . ($withSymbol ? '₹' : '') . $formattedNumber . '.' . $cents;
        return $formatted;
    }

    /**
     * Calculate and return customer's financial balance snapshot
     */
    public static function getCustomerBalance(int $customerId): array {
        $db = Database::getInstance();
        $customer = $db->fetchOne("SELECT * FROM customers WHERE id = ?", [$customerId]);
        if (!$customer) {
            throw new Exception("Customer not found.");
        }

        // Opening balance in paise (positive = debit/due, negative = credit/advance)
        $openingBalance = (int)($customer['opening_balance'] ?? 0);

        // Sum of all active non-reversed invoices
        $invoiceTotal = (int)($db->fetchOne(
            "SELECT COALESCE(SUM(total_amount), 0) as total FROM invoices WHERE customer_id = ? AND is_reversed = 0",
            [$customerId]
        )['total'] ?? 0);

        // Sum of all active non-reversed payments
        $paymentTotal = (int)($db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE customer_id = ? AND is_reversed = 0",
            [$customerId]
        )['total'] ?? 0);

        // Net balance formula:
        // Opening balance (debit) + Invoices (debit) - Payments (credit)
        $netBalance = $openingBalance + $invoiceTotal - $paymentTotal;

        // Breakdown:
        $outstandingDue = max(0, $netBalance);
        $advanceCredit = $netBalance < 0 ? abs($netBalance) : 0;
        $isSettled = ($netBalance === 0);

        return [
            'customer_id' => $customerId,
            'customer_name' => $customer['name'],
            'opening_balance' => $openingBalance,
            'opening_balance_date' => $customer['opening_balance_date'],
            'total_invoiced' => $invoiceTotal,
            'total_paid' => $paymentTotal,
            'net_balance' => $netBalance,
            'outstanding_due' => $outstandingDue,
            'advance_credit' => $advanceCredit,
            'is_settled' => $isSettled,
            'status_label' => $outstandingDue > 0 ? 'Due' : ($advanceCredit > 0 ? 'Advance' : 'Settled')
        ];
    }

    /**
     * Append a ledger entry and calculate current running balance
     */
    public static function recordLedgerEntry(
        int $customerId,
        string $entryDate,
        string $entryType,
        ?int $referenceId,
        ?string $referenceNo,
        string $description,
        int $debit,
        int $credit
    ): int {
        $db = Database::getInstance();

        // Get previous latest running balance
        $lastEntry = $db->fetchOne(
            "SELECT running_balance FROM ledger_entries WHERE customer_id = ? ORDER BY entry_date DESC, id DESC LIMIT 1",
            [$customerId]
        );

        $previousBalance = $lastEntry ? (int)$lastEntry['running_balance'] : 0;
        // Running balance = Previous + Debit - Credit
        $newRunningBalance = $previousBalance + $debit - $credit;

        $db->execute(
            "INSERT INTO ledger_entries (
                customer_id, entry_date, entry_type, reference_id, reference_no,
                description, debit, credit, running_balance, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $customerId,
                $entryDate,
                $entryType,
                $referenceId,
                $referenceNo,
                $description,
                $debit,
                $credit,
                $newRunningBalance,
                (new DateTime())->format('Y-m-d H:i:s')
            ]
        );

        return (int)$db->lastInsertId();
    }

    /**
     * Full customer ledger recalculation and audit verification
     */
    public static function recalculateCustomerLedger(int $customerId): void {
        $db = Database::getInstance();
        $entries = $db->fetchAll(
            "SELECT id, debit, credit FROM ledger_entries WHERE customer_id = ? ORDER BY entry_date ASC, id ASC",
            [$customerId]
        );

        $running = 0;
        foreach ($entries as $entry) {
            $running = $running + (int)$entry['debit'] - (int)$entry['credit'];
            $db->execute("UPDATE ledger_entries SET running_balance = ? WHERE id = ?", [$running, $entry['id']]);
        }
    }
}
