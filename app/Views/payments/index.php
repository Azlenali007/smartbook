<?php
/**
 * Smart Khata - Payments Received Directory
 */
$pageTitle = 'Payments Received — Smart Khata';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Payments Received</h1>
    <p class="text-sm text-slate-500 mt-1">Track collections, invoice allocations, customer advances, and receipts</p>
  </div>
  <a href="/payments/create" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow flex items-center space-x-2 self-start sm:self-auto">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span>Receive Payment</span>
  </a>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row gap-3 items-center justify-between">
  <form action="/payments" method="GET" class="w-full md:w-auto flex flex-col sm:flex-row gap-3 flex-1">
    <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus-within:ring-2 focus-within:ring-blue-500 focus-within:bg-white transition flex-1">
      <svg class="w-4 h-4 text-slate-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search payment #, ref or customer..." class="w-full bg-transparent focus:outline-none text-slate-800 placeholder-slate-400">
    </div>

    <select name="customer_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="0">All Customers</option>
      <?php foreach ($allCustomers as $cust): ?>
        <option value="<?= $cust['id'] ?>" <?= $customerId == $cust['id'] ? 'selected' : '' ?>><?= e($cust['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
      Filter
    </button>
  </form>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
        <tr>
          <th class="py-3.5 px-4">Receipt #</th>
          <th class="py-3.5 px-4">Date</th>
          <th class="py-3.5 px-4">Customer</th>
          <th class="py-3.5 px-4">Mode / Ref</th>
          <th class="py-3.5 px-4 text-right">Total Amount</th>
          <th class="py-3.5 px-4 text-right">Allocated</th>
          <th class="py-3.5 px-4 text-right">Retained Advance</th>
          <th class="py-3.5 px-4 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <?php if (empty($payments)): ?>
          <tr><td colspan="8" class="py-12 text-center text-slate-400">No payments found.</td></tr>
        <?php else: ?>
          <?php foreach ($payments as $pay): ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="py-3.5 px-4 font-bold text-slate-900">
                <a href="/payments/show?id=<?= $pay['id'] ?>" class="text-blue-600 hover:underline"><?= e($pay['payment_number']) ?></a>
              </td>
              <td class="py-3.5 px-4 text-slate-600"><?= formatDate($pay['payment_date']) ?></td>
              <td class="py-3.5 px-4 font-medium text-slate-900">
                <a href="/customers/show?id=<?= $pay['customer_id'] ?>" class="hover:underline"><?= e($pay['customer_name']) ?></a>
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-800"><?= e($pay['payment_mode']) ?></span>
                <?php if (!empty($pay['reference_no'])): ?>
                  <div class="text-[10px] text-slate-400 truncate max-w-[120px]">Ref: <?= e($pay['reference_no']) ?></div>
                <?php endif; ?>
              </td>
              <td class="py-3.5 px-4 text-right font-extrabold text-emerald-600"><?= formatINR($pay['amount']) ?></td>
              <td class="py-3.5 px-4 text-right font-semibold text-slate-800"><?= formatINR($pay['allocated_amount']) ?></td>
              <td class="py-3.5 px-4 text-right font-semibold <?= $pay['advance_amount'] > 0 ? 'text-blue-600' : 'text-slate-400' ?>">
                <?= formatINR($pay['advance_amount']) ?>
              </td>
              <td class="py-3.5 px-4 text-right space-x-1">
                <a href="/payments/show?id=<?= $pay['id'] ?>" class="p-1 text-slate-400 hover:text-blue-600 inline-block" title="View Receipt">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <a href="/payments/pdf?id=<?= $pay['id'] ?>" class="p-1 text-slate-400 hover:text-emerald-600 inline-block" title="Receipt PDF">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </a>
                <a href="/payments/print?id=<?= $pay['id'] ?>" target="_blank" class="p-1 text-slate-400 hover:text-slate-700 inline-block" title="Print Receipt">
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

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
