<?php
/**
 * Smart Khata - Invoices & Billing List
 */
$pageTitle = 'Invoices & Billing — Smart Khata';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Invoices & Billing</h1>
    <p class="text-sm text-slate-500 mt-1">Generate sales bills, manage payment statuses, print and download PDFs</p>
  </div>
  <a href="/invoices/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow flex items-center space-x-2 self-start sm:self-auto">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span>Create Invoice</span>
  </a>
</div>

<!-- Filters -->
<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row gap-3 items-center justify-between">
  <form action="/invoices" method="GET" class="w-full md:w-auto flex flex-col sm:flex-row gap-3 flex-1">
    <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus-within:ring-2 focus-within:ring-blue-500 focus-within:bg-white transition flex-1">
      <svg class="w-4 h-4 text-slate-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search invoice # or customer..." class="w-full bg-transparent focus:outline-none text-slate-800 placeholder-slate-400">
    </div>

    <select name="customer_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="0">All Customers</option>
      <?php foreach ($allCustomers as $cust): ?>
        <option value="<?= $cust['id'] ?>" <?= $customerId == $cust['id'] ? 'selected' : '' ?>><?= e($cust['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
      <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
      <option value="partial" <?= $status === 'partial' ? 'selected' : '' ?>>Partially Paid</option>
      <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Paid</option>
      <option value="reversed" <?= $status === 'reversed' ? 'selected' : '' ?>>Reversed</option>
    </select>

    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
      Filter
    </button>
  </form>
</div>

<!-- Invoices Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
        <tr>
          <th class="py-3.5 px-4">Invoice #</th>
          <th class="py-3.5 px-4">Date</th>
          <th class="py-3.5 px-4">Customer</th>
          <th class="py-3.5 px-4 text-right">Invoice Total</th>
          <th class="py-3.5 px-4 text-right">Paid</th>
          <th class="py-3.5 px-4 text-right">Remaining Due</th>
          <th class="py-3.5 px-4 text-center">Status</th>
          <th class="py-3.5 px-4 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <?php if (empty($invoices)): ?>
          <tr><td colspan="8" class="py-12 text-center text-slate-400">No invoices match your search.</td></tr>
        <?php else: ?>
          <?php foreach ($invoices as $inv): ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="py-3.5 px-4 font-bold text-slate-900">
                <a href="/invoices/show?id=<?= $inv['id'] ?>" class="text-blue-600 hover:underline"><?= e($inv['invoice_number']) ?></a>
              </td>
              <td class="py-3.5 px-4 text-slate-600"><?= formatDate($inv['invoice_date']) ?></td>
              <td class="py-3.5 px-4 font-medium text-slate-900">
                <a href="/customers/show?id=<?= $inv['customer_id'] ?>" class="hover:underline"><?= e($inv['customer_name']) ?></a>
              </td>
              <td class="py-3.5 px-4 text-right font-extrabold text-slate-900"><?= formatINR($inv['total_amount']) ?></td>
              <td class="py-3.5 px-4 text-right font-semibold text-emerald-600"><?= formatINR($inv['paid_amount']) ?></td>
              <td class="py-3.5 px-4 text-right font-extrabold <?= $inv['due_amount'] > 0 ? 'text-rose-600' : 'text-slate-400' ?>">
                <?= formatINR($inv['due_amount']) ?>
              </td>
              <td class="py-3.5 px-4 text-center">
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
              <td class="py-3.5 px-4 text-right space-x-1">
                <a href="/invoices/show?id=<?= $inv['id'] ?>" class="p-1 text-slate-400 hover:text-blue-600 inline-block" title="View Details">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <a href="/invoices/pdf?id=<?= $inv['id'] ?>" class="p-1 text-slate-400 hover:text-blue-600 inline-block" title="Download PDF">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </a>
                <a href="/invoices/print?id=<?= $inv['id'] ?>" target="_blank" class="p-1 text-slate-400 hover:text-slate-700 inline-block" title="Print">
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
