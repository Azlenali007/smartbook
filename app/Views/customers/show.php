<?php
/**
 * Smart Khata - Customer 360 Profile & Ledger Summary
 */
$pageTitle = e($customer['name']) . ' — Customer Profile';
ob_start();
?>

<!-- Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div class="flex items-center space-x-4">
    <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white font-bold text-2xl flex items-center justify-center shadow-md">
      <?= strtoupper(substr($customer['name'], 0, 1)) ?>
    </div>
    <div>
      <div class="flex items-center space-x-2">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900"><?= e($customer['name']) ?></h1>
        <?php if ($customer['status'] === 'active'): ?>
          <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Active</span>
        <?php else: ?>
          <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200 text-slate-600">Inactive</span>
        <?php endif; ?>
      </div>
      <p class="text-xs text-slate-500 mt-1">
        Phone: <?= e($customer['phone'] ?: 'N/A') ?> &bull; <?= e($customer['address'] ?: 'No address specified') ?>
      </p>
    </div>
  </div>

  <div class="flex items-center space-x-2">
    <a href="/invoices/create?customer_id=<?= $customer['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3.5 py-2.5 rounded-xl transition shadow flex items-center space-x-1.5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      <span>New Sale</span>
    </a>
    <a href="/payments/create?customer_id=<?= $customer['id'] ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2.5 rounded-xl transition shadow flex items-center space-x-1.5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span>Receive Payment</span>
    </a>
    <a href="/ledger?id=<?= $customer['id'] ?>" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-3.5 py-2.5 rounded-xl transition shadow flex items-center space-x-1.5">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      <span>Full Ledger</span>
    </a>
    <a href="/customers/edit?id=<?= $customer['id'] ?>" class="p-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
    </a>
  </div>
</div>

<!-- Customer Financial Balance Summary Card -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Current Net Balance -->
    <div class="sm:col-span-2 lg:col-span-1 border-b sm:border-b-0 sm:border-r border-slate-100 pb-4 sm:pb-0 pr-4">
      <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Net Khata Balance</div>
      <?php if ($balance['outstanding_due'] > 0): ?>
        <div class="text-3xl font-extrabold text-rose-600 tracking-tight"><?= formatINR($balance['outstanding_due']) ?></div>
        <span class="inline-block mt-2 px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Customer Owes (Udhaar)</span>
      <?php elseif ($balance['advance_credit'] > 0): ?>
        <div class="text-3xl font-extrabold text-emerald-600 tracking-tight"><?= formatINR($balance['advance_credit']) ?></div>
        <span class="inline-block mt-2 px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Customer Advance (Credit)</span>
      <?php else: ?>
        <div class="text-3xl font-bold text-slate-600 tracking-tight">₹0.00</div>
        <span class="inline-block mt-2 px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-600">All Accounts Settled</span>
      <?php endif; ?>
    </div>

    <!-- Opening Balance -->
    <div class="border-b sm:border-b-0 sm:border-r border-slate-100 pb-4 sm:pb-0 pr-4">
      <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Opening Balance</div>
      <div class="text-xl font-bold text-slate-900"><?= formatINR(abs($balance['opening_balance'])) ?></div>
      <div class="text-xs text-slate-500 mt-1">
        <?= $balance['opening_balance'] > 0 ? 'Debit (Debt)' : ($balance['opening_balance'] < 0 ? 'Credit (Advance)' : 'Nil') ?>
        as of <?= formatDate($balance['opening_balance_date']) ?>
      </div>
    </div>

    <!-- Total Invoiced -->
    <div class="border-b sm:border-b-0 sm:border-r border-slate-100 pb-4 sm:pb-0 pr-4">
      <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Invoiced (Debits)</div>
      <div class="text-xl font-bold text-slate-900"><?= formatINR($balance['total_invoiced']) ?></div>
      <div class="text-xs text-slate-500 mt-1">All active invoices</div>
    </div>

    <!-- Total Payments Received -->
    <div>
      <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Total Payments (Credits)</div>
      <div class="text-xl font-bold text-emerald-600"><?= formatINR($balance['total_paid']) ?></div>
      <div class="text-xs text-slate-500 mt-1">All verified payments</div>
    </div>
  </div>
</div>

<!-- Two Columns: Customer Invoices & Customer Payments -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
  <!-- Invoices -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-bold text-slate-900 text-base">Invoices</h3>
      <a href="/invoices/create?customer_id=<?= $customer['id'] ?>" class="text-xs font-semibold text-blue-600 hover:underline">+ New Invoice</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-100">
          <tr>
            <th class="py-3 px-4">Invoice #</th>
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4 text-right">Amount</th>
            <th class="py-3 px-4 text-right">Due</th>
            <th class="py-3 px-4 text-center">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($invoices)): ?>
            <tr><td colspan="5" class="py-6 text-center text-slate-400">No invoices for this customer yet.</td></tr>
          <?php else: ?>
            <?php foreach ($invoices as $inv): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-semibold text-blue-600">
                  <a href="/invoices/show?id=<?= $inv['id'] ?>" class="hover:underline"><?= e($inv['invoice_number']) ?></a>
                </td>
                <td class="py-3 px-4"><?= formatDate($inv['invoice_date']) ?></td>
                <td class="py-3 px-4 text-right font-bold text-slate-900"><?= formatINR($inv['total_amount']) ?></td>
                <td class="py-3 px-4 text-right font-bold text-rose-600"><?= formatINR($inv['due_amount']) ?></td>
                <td class="py-3 px-4 text-center">
                  <?php if ($inv['is_reversed']): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-500 line-through">Reversed</span>
                  <?php elseif ($inv['status'] === 'paid'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-50 text-emerald-700">Paid</span>
                  <?php elseif ($inv['status'] === 'partial'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] bg-amber-50 text-amber-700">Partial</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-[10px] bg-rose-50 text-rose-700">Unpaid</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Payments -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-bold text-slate-900 text-base">Payments Received</h3>
      <a href="/payments/create?customer_id=<?= $customer['id'] ?>" class="text-xs font-semibold text-emerald-600 hover:underline">+ Receive Payment</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-100">
          <tr>
            <th class="py-3 px-4">Receipt #</th>
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4">Mode</th>
            <th class="py-3 px-4 text-right">Amount</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($payments)): ?>
            <tr><td colspan="5" class="py-6 text-center text-slate-400">No payments received yet.</td></tr>
          <?php else: ?>
            <?php foreach ($payments as $pay): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-semibold text-blue-600">
                  <a href="/payments/show?id=<?= $pay['id'] ?>" class="hover:underline"><?= e($pay['payment_number']) ?></a>
                </td>
                <td class="py-3 px-4"><?= formatDate($pay['payment_date']) ?></td>
                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 font-medium"><?= e($pay['payment_mode']) ?></span>
                </td>
                <td class="py-3 px-4 text-right font-bold text-emerald-600"><?= formatINR($pay['amount']) ?></td>
                <td class="py-3 px-4 text-right">
                  <a href="/payments/pdf?id=<?= $pay['id'] ?>" class="text-slate-400 hover:text-emerald-600 p-1">
                    Receipt PDF
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
