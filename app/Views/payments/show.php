<?php
/**
 * Smart Khata - Payment Receipt Detail View
 */
$pageTitle = 'Receipt #' . e($payment['payment_number']) . ' — Smart Khata';
ob_start();
?>

<div class="max-w-3xl mx-auto">
  <!-- Top Bar Actions -->
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center space-x-3">
      <a href="/payments" class="text-sm font-semibold text-slate-500 hover:text-slate-800">&larr; Payments</a>
      <span class="text-slate-300">/</span>
      <h1 class="text-xl font-bold tracking-tight text-slate-900"><?= e($payment['payment_number']) ?></h1>
    </div>

    <div class="flex items-center space-x-2">
      <a href="/payments/pdf?id=<?= $payment['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition shadow flex items-center space-x-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>Download Receipt PDF</span>
      </a>
      <a href="/payments/print?id=<?= $payment['id'] ?>" target="_blank" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-xl transition shadow flex items-center space-x-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        <span>Print Receipt</span>
      </a>
    </div>
  </div>

  <!-- Receipt Card -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 mb-8">
    <div class="flex flex-col sm:flex-row justify-between pb-8 border-b border-slate-100 gap-6">
      <div>
        <h2 class="text-2xl font-black text-slate-900"><?= e($settings['shop_name'] ?? 'Smart Khata') ?></h2>
        <p class="text-xs text-slate-500 mt-1"><?= e($settings['shop_address'] ?? '') ?></p>
        <?php if (!empty($settings['shop_phone'])): ?>
          <p class="text-xs text-slate-500">Phone: <?= e($settings['shop_phone']) ?></p>
        <?php endif; ?>
      </div>

      <div class="sm:text-right">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">OFFICIAL PAYMENT RECEIPT</div>
        <div class="text-xl font-extrabold text-emerald-600 mt-0.5"><?= e($payment['payment_number']) ?></div>
        <div class="text-xs text-slate-600 mt-1">Date: <strong><?= formatDate($payment['payment_date']) ?></strong></div>
        <div class="text-xs text-slate-400">Mode: <?= e($payment['payment_mode']) ?></div>
      </div>
    </div>

    <!-- Payer Info & Amount Banner -->
    <div class="py-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start gap-4">
      <div>
        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Received From</div>
        <a href="/customers/show?id=<?= $customer['id'] ?>" class="text-base font-bold text-slate-900 hover:text-blue-600 hover:underline">
          <?= e($customer['name']) ?>
        </a>
        <div class="text-xs text-slate-600 mt-0.5"><?= e($customer['phone'] ?: 'No phone') ?></div>
        <div class="text-xs text-slate-500"><?= e($customer['address'] ?: '') ?></div>
        <?php if (!empty($payment['reference_no'])): ?>
          <div class="mt-2 text-xs font-medium text-slate-700 bg-slate-50 px-2.5 py-1 rounded-md border border-slate-100 inline-block">
            Ref / UTR: <?= e($payment['reference_no']) ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="w-full sm:w-auto bg-emerald-50 border border-emerald-100 p-5 rounded-2xl text-left sm:text-right">
        <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">Total Amount Received</div>
        <div class="text-3xl font-black text-emerald-700 mt-1"><?= formatINR($payment['amount']) ?></div>
      </div>
    </div>

    <!-- Allocation Breakdown -->
    <div class="py-6 border-b border-slate-100">
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Payment Allocation Breakdown</h3>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
          <div class="text-xs font-semibold text-slate-500 uppercase">Allocated to Invoices</div>
          <div class="text-xl font-bold text-slate-900 mt-1"><?= formatINR($payment['allocated_amount']) ?></div>
          <p class="text-[11px] text-slate-500 mt-1">Cleared against outstanding sales</p>
        </div>

        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
          <div class="text-xs font-semibold text-slate-500 uppercase">Retained as Advance Credit</div>
          <div class="text-xl font-bold text-blue-600 mt-1"><?= formatINR($payment['advance_amount']) ?></div>
          <p class="text-[11px] text-slate-500 mt-1">Available to adjust against future invoices</p>
        </div>
      </div>

      <?php if (!empty($allocations)): ?>
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
            <tr>
              <th class="py-2.5 px-3">Invoice #</th>
              <th class="py-2.5 px-3">Invoice Date</th>
              <th class="py-2.5 px-3 text-right">Invoice Amount</th>
              <th class="py-2.5 px-3 text-right">Amount Allocated</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php foreach ($allocations as $al): ?>
              <tr>
                <td class="py-2.5 px-3 font-semibold text-blue-600">
                  <a href="/invoices/show?id=<?= $al['invoice_id'] ?>" class="hover:underline"><?= e($al['invoice_number']) ?></a>
                </td>
                <td class="py-2.5 px-3"><?= formatDate($al['invoice_date']) ?></td>
                <td class="py-2.5 px-3 text-right"><?= formatINR($al['total_amount']) ?></td>
                <td class="py-2.5 px-3 text-right font-bold text-emerald-700"><?= formatINR($al['allocated_amount']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- Notes & Ledger link -->
    <div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
      <div>
        <?php if (!empty($payment['notes'])): ?>
          <span class="font-semibold text-slate-700">Notes:</span> <?= e($payment['notes']) ?>
        <?php endif; ?>
      </div>
      <a href="/ledger?id=<?= $customer['id'] ?>" class="font-semibold text-blue-600 hover:underline">
        Open Customer Ledger Statement &rarr;
      </a>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
