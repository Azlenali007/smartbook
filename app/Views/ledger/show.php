<?php
/**
 * Smart Khata - Customer Account Ledger View
 */
$pageTitle = 'Ledger: ' . e($customer['name']) . ' — Smart Khata';
ob_start();
?>

<div class="max-w-5xl mx-auto" x-data="{ showAdvanceModal: false }">
  <!-- Top Navigation -->
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center space-x-3">
      <a href="/customers/show?id=<?= $customer['id'] ?>" class="text-sm font-semibold text-slate-500 hover:text-slate-800">&larr; Customer Profile</a>
      <span class="text-slate-300">/</span>
      <h1 class="text-xl font-bold tracking-tight text-slate-900">Khata Ledger Statement</h1>
    </div>

    <div class="flex items-center space-x-2">
      <!-- Advance Adjustment Button if customer has advance credit and unpaid invoices -->
      <?php if ($balance['advance_credit'] > 0 && !empty($unpaidInvoices)): ?>
        <button type="button" @click="showAdvanceModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-2 rounded-xl transition shadow flex items-center space-x-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          <span>Apply Advance Credit</span>
        </button>
      <?php endif; ?>

      <a href="/ledger/pdf?id=<?= $customer['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition shadow flex items-center space-x-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>Statement PDF</span>
      </a>
      <a href="/ledger/print?id=<?= $customer['id'] ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>" target="_blank" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-3.5 py-2 rounded-xl transition shadow flex items-center space-x-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        <span>Print</span>
      </a>
    </div>
  </div>

  <!-- Customer Ledger Header Banner -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
      <div>
        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Customer Account</div>
        <h2 class="text-2xl font-bold text-slate-900"><?= e($customer['name']) ?></h2>
        <p class="text-xs text-slate-600 mt-0.5">Phone: <?= e($customer['phone'] ?: 'N/A') ?> &bull; <?= e($customer['address'] ?: '') ?></p>
      </div>

      <div class="sm:text-right bg-slate-50 p-4 rounded-xl border border-slate-100 min-w-[200px]">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Current Balance</div>
        <?php if ($balance['outstanding_due'] > 0): ?>
          <div class="text-2xl font-black text-rose-600"><?= formatINR($balance['outstanding_due']) ?> Due</div>
          <span class="text-[11px] font-semibold text-rose-700">Customer Owes (Udhaar)</span>
        <?php elseif ($balance['advance_credit'] > 0): ?>
          <div class="text-2xl font-black text-emerald-600"><?= formatINR($balance['advance_credit']) ?> Advance</div>
          <span class="text-[11px] font-semibold text-emerald-700">Customer Credit Available</span>
        <?php else: ?>
          <div class="text-2xl font-bold text-slate-600">₹0.00</div>
          <span class="text-[11px] font-semibold text-slate-500">Account Settled</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Date Range Filter -->
    <div class="mt-6 pt-6 border-t border-slate-100">
      <form action="/ledger" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
        <input type="hidden" name="id" value="<?= $customer['id'] ?>">

        <div class="flex items-center space-x-2 w-full sm:w-auto">
          <label class="text-xs font-semibold text-slate-600">From:</label>
          <input type="date" name="start_date" value="<?= e($startDate) ?>" class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div class="flex items-center space-x-2 w-full sm:w-auto">
          <label class="text-xs font-semibold text-slate-600">To:</label>
          <input type="date" name="end_date" value="<?= e($endDate) ?>" class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition w-full sm:w-auto">
          Filter Statement
        </button>

        <?php if ($startDate !== '' || $endDate !== ''): ?>
          <a href="/ledger?id=<?= $customer['id'] ?>" class="text-xs text-slate-500 hover:text-slate-800">Clear Filter</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- Chronological Ledger Entries Table -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
          <tr>
            <th class="py-3 px-3.5">Date</th>
            <th class="py-3 px-3.5">Type</th>
            <th class="py-3 px-3.5">Ref / Transaction #</th>
            <th class="py-3 px-3.5">Description</th>
            <th class="py-3 px-3.5 text-right">Debit (+)</th>
            <th class="py-3 px-3.5 text-right">Credit (-)</th>
            <th class="py-3 px-3.5 text-right">Running Balance</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($entries)): ?>
            <tr><td colspan="7" class="py-12 text-center text-slate-400">No transactions recorded in this date range.</td></tr>
          <?php else: ?>
            <?php foreach ($entries as $e): ?>
              <?php
                $isDr = ($e['running_balance'] > 0);
                $isCr = ($e['running_balance'] < 0);
                $balLabel = $isDr ? ' Dr' : ($isCr ? ' Cr' : '');
              ?>
              <tr class="hover:bg-slate-50/70 transition">
                <td class="py-3 px-3.5 font-medium text-slate-900 whitespace-nowrap"><?= formatDate($e['entry_date']) ?></td>
                <td class="py-3 px-3.5 whitespace-nowrap">
                  <?php if ($e['entry_type'] === 'invoice'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Invoice</span>
                  <?php elseif ($e['entry_type'] === 'payment'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Payment</span>
                  <?php elseif ($e['entry_type'] === 'opening_balance'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">Opening</span>
                  <?php elseif ($e['entry_type'] === 'advance_adjustment'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">Advance Adj</span>
                  <?php elseif ($e['entry_type'] === 'reversal'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Reversal</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600"><?= e($e['entry_type']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-3.5 font-semibold text-slate-800 whitespace-nowrap">
                  <?php if ($e['entry_type'] === 'invoice' && !empty($e['reference_id'])): ?>
                    <a href="/invoices/show?id=<?= $e['reference_id'] ?>" class="text-blue-600 hover:underline"><?= e($e['reference_no']) ?></a>
                  <?php elseif ($e['entry_type'] === 'payment' && !empty($e['reference_id'])): ?>
                    <a href="/payments/show?id=<?= $e['reference_id'] ?>" class="text-emerald-600 hover:underline"><?= e($e['reference_no']) ?></a>
                  <?php else: ?>
                    <?= e($e['reference_no'] ?: '—') ?>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-3.5 text-slate-600"><?= e($e['description']) ?></td>
                <td class="py-3 px-3.5 text-right font-semibold text-rose-600 whitespace-nowrap">
                  <?= $e['debit'] > 0 ? formatINR($e['debit']) : '—' ?>
                </td>
                <td class="py-3 px-3.5 text-right font-semibold text-emerald-600 whitespace-nowrap">
                  <?= $e['credit'] > 0 ? formatINR($e['credit']) : '—' ?>
                </td>
                <td class="py-3 px-3.5 text-right font-extrabold whitespace-nowrap <?= $isDr ? 'text-rose-600' : ($isCr ? 'text-emerald-600' : 'text-slate-400') ?>">
                  <?= formatINR(abs($e['running_balance'])) ?><?= $balLabel ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Advance Adjustment Modal -->
  <?php if ($balance['advance_credit'] > 0 && !empty($unpaidInvoices)): ?>
    <div x-show="showAdvanceModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="showAdvanceModal = false">
        <h3 class="font-bold text-slate-900 text-lg mb-1">Apply Advance Credit to Invoice</h3>
        <p class="text-xs text-slate-500 mb-4">
          Available Advance: <strong class="text-emerald-600 font-bold"><?= formatINR($balance['advance_credit']) ?></strong>
        </p>

        <form action="/ledger/apply-advance" method="POST" class="space-y-4">
          <?= \SmartKhata\CSRF::getField() ?>
          <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Unpaid Invoice</label>
            <select name="invoice_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
              <?php foreach ($unpaidInvoices as $u): ?>
                <option value="<?= $u['id'] ?>">
                  <?= e($u['invoice_number']) ?> &bull; Due: <?= formatINR($u['due_amount']) ?> (<?= formatDate($u['invoice_date']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Amount to Settle from Advance (₹)</label>
            <input type="number" step="0.01" min="0.01" max="<?= (float)($balance['advance_credit'] / 100) ?>"
                   name="amount" required
                   value="<?= number_format($balance['advance_credit'] / 100, 2, '.', '') ?>"
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
          </div>

          <div class="flex items-center justify-end space-x-2 pt-2">
            <button type="button" @click="showAdvanceModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">Cancel</button>
            <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow">Apply Adjustment</button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
