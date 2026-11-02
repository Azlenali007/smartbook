<?php
/**
 * Smart Khata - Dashboard View
 */
$pageTitle = 'Dashboard — Smart Khata';
ob_start();
?>

<!-- Header & Quick Actions -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Financial Dashboard</h1>
    <p class="text-sm text-slate-500 mt-1">Real-time ledger overview & daily transaction totals</p>
  </div>
  <div class="flex items-center space-x-3">
    <a href="/invoices/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow flex items-center space-x-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      <span>New Invoice</span>
    </a>
    <a href="/payments/create" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow flex items-center space-x-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span>Receive Payment</span>
    </a>
  </div>
</div>

<!-- Key Financial KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
  <!-- Total Outstanding Debt / Udhaar -->
  <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm relative overflow-hidden">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider bg-rose-50 px-2.5 py-1 rounded-md border border-rose-100">Total Udhaar / Due</span>
      <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
      </div>
    </div>
    <div class="text-3xl font-extrabold text-slate-900 tracking-tight"><?= formatINR($totalOutstandingDue) ?></div>
    <p class="text-xs text-slate-500 mt-2">Receivable from active customer ledger balances</p>
  </div>

  <!-- Total Customer Advances -->
  <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm relative overflow-hidden">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">Customer Advances</span>
      <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
    </div>
    <div class="text-3xl font-extrabold text-emerald-600 tracking-tight"><?= formatINR($totalCustomerAdvances) ?></div>
    <p class="text-xs text-slate-500 mt-2">Unallocated advance credits in customer khata</p>
  </div>

  <!-- Active Customers -->
  <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm relative overflow-hidden">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">Customer Accounts</span>
      <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      </div>
    </div>
    <div class="text-3xl font-extrabold text-slate-900 tracking-tight"><?= number_format($customerCount) ?></div>
    <p class="text-xs text-slate-500 mt-2"><a href="/customers/create" class="text-blue-600 font-medium hover:underline">+ Add new customer</a></p>
  </div>
</div>

<!-- Today's Activity Strip -->
<div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-6 text-white mb-8 shadow-sm">
  <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-700/60">
    <div class="flex items-center space-x-2">
      <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
      <h2 class="font-bold text-base">Today's Transactions (<?= date('d-m-Y') ?>)</h2>
    </div>
    <span class="text-xs text-slate-400">Live Server Reconciled</span>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div>
      <div class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Today's Sales (Billed)</div>
      <div class="text-2xl font-extrabold text-white"><?= formatINR($todaySales) ?></div>
      <div class="text-xs text-slate-400 mt-1"><?= $todayInvoiceCount ?> invoices issued today</div>
    </div>

    <div>
      <div class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Today's Payments Received</div>
      <div class="text-2xl font-extrabold text-emerald-400"><?= formatINR($todayPayments) ?></div>
      <div class="text-xs text-slate-400 mt-1">Cash, UPI & Bank collections</div>
    </div>

    <div>
      <div class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Net Flow Today</div>
      <?php $netToday = $todayPayments - $todaySales; ?>
      <div class="text-2xl font-extrabold <?= $netToday >= 0 ? 'text-blue-400' : 'text-amber-400' ?>">
        <?= formatINR($netToday) ?>
      </div>
      <div class="text-xs text-slate-400 mt-1"><?= $netToday >= 0 ? 'Net positive inflow' : 'Receivables expanded' ?></div>
    </div>
  </div>
</div>

<!-- Important Alerts (High Outstanding Customers) -->
<?php if (!empty($highDueCustomers)): ?>
  <div class="mb-8 p-5 bg-amber-50 rounded-2xl border border-amber-200">
    <div class="flex items-center space-x-2 mb-3">
      <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      <h3 class="font-bold text-amber-900 text-sm">Attention: High Outstanding Customer Accounts (≥ ₹10,000)</h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
      <?php foreach (array_slice($highDueCustomers, 0, 6) as $hdc): ?>
        <a href="/ledger?id=<?= $hdc['id'] ?>" class="p-3 bg-white rounded-xl border border-amber-200/80 hover:border-amber-400 transition flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-800 truncate"><?= e($hdc['name']) ?></span>
          <span class="text-xs font-bold text-rose-600 ml-2"><?= formatINR($hdc['due']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Two Columns: Recent Invoices & Recent Payments -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
  <!-- Recent Invoices Table -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-bold text-slate-900 text-base">Recent Invoices</h3>
      <a href="/invoices" class="text-xs font-semibold text-blue-600 hover:underline">View All &rarr;</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-100">
          <tr>
            <th class="py-3 px-4">Invoice #</th>
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4 text-right">Amount</th>
            <th class="py-3 px-4 text-center">Status</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($recentInvoices)): ?>
            <tr><td colspan="5" class="py-6 text-center text-slate-400">No invoices generated yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentInvoices as $inv): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-semibold text-slate-900">
                  <a href="/invoices/show?id=<?= $inv['id'] ?>" class="text-blue-600 hover:underline"><?= e($inv['invoice_number']) ?></a>
                  <div class="text-[10px] text-slate-400 font-normal"><?= formatDate($inv['invoice_date']) ?></div>
                </td>
                <td class="py-3 px-4 font-medium text-slate-800 truncate max-w-[140px]"><?= e($inv['customer_name']) ?></td>
                <td class="py-3 px-4 text-right font-bold text-slate-900"><?= formatINR($inv['total_amount']) ?></td>
                <td class="py-3 px-4 text-center">
                  <?php if ($inv['is_reversed']): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-500 line-through">Reversed</span>
                  <?php elseif ($inv['status'] === 'paid'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Paid</span>
                  <?php elseif ($inv['status'] === 'partial'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Partial</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Unpaid</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4 text-right space-x-1">
                  <a href="/invoices/pdf?id=<?= $inv['id'] ?>" title="Download PDF" class="text-slate-400 hover:text-blue-600 p-1 inline-block">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </a>
                  <a href="/invoices/print?id=<?= $inv['id'] ?>" target="_blank" title="Print" class="text-slate-400 hover:text-slate-700 p-1 inline-block">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Payments Received Table -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-bold text-slate-900 text-base">Recent Payments Received</h3>
      <a href="/payments" class="text-xs font-semibold text-blue-600 hover:underline">View All &rarr;</a>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-100">
          <tr>
            <th class="py-3 px-4">Receipt #</th>
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4">Mode</th>
            <th class="py-3 px-4 text-right">Amount</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($recentPayments)): ?>
            <tr><td colspan="5" class="py-6 text-center text-slate-400">No payments recorded yet.</td></tr>
          <?php else: ?>
            <?php foreach ($recentPayments as $pay): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-semibold text-slate-900">
                  <a href="/payments/show?id=<?= $pay['id'] ?>" class="text-blue-600 hover:underline"><?= e($pay['payment_number']) ?></a>
                  <div class="text-[10px] text-slate-400 font-normal"><?= formatDate($pay['payment_date']) ?></div>
                </td>
                <td class="py-3 px-4 font-medium text-slate-800 truncate max-w-[140px]"><?= e($pay['customer_name']) ?></td>
                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700"><?= e($pay['payment_mode']) ?></span>
                </td>
                <td class="py-3 px-4 text-right font-bold text-emerald-600"><?= formatINR($pay['amount']) ?></td>
                <td class="py-3 px-4 text-right space-x-1">
                  <a href="/payments/pdf?id=<?= $pay['id'] ?>" title="Download Receipt PDF" class="text-slate-400 hover:text-emerald-600 p-1 inline-block">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </a>
                  <a href="/payments/print?id=<?= $pay['id'] ?>" target="_blank" title="Print Receipt" class="text-slate-400 hover:text-slate-700 p-1 inline-block">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
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
