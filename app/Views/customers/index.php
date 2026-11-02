<?php
/**
 * Smart Khata - Customer Directory & Udhaar List
 */
$pageTitle = 'Customers & Udhaar — Smart Khata';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Customer Directory & Khata</h1>
    <p class="text-sm text-slate-500 mt-1">Manage accounts, customer udhaar balances, advances, and contact profiles</p>
  </div>
  <a href="/customers/create" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow flex items-center space-x-2 self-start sm:self-auto">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
    <span>Add Customer</span>
  </a>
</div>

<!-- Search & Filters -->
<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col sm:flex-row gap-3 items-center justify-between">
  <form action="/customers" method="GET" class="w-full sm:w-80 flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm focus-within:ring-2 focus-within:ring-blue-500 focus-within:bg-white transition">
    <svg class="w-4 h-4 text-slate-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search name or phone..." class="w-full bg-transparent focus:outline-none text-slate-800 placeholder-slate-400">
    <?php if ($search !== ''): ?>
      <a href="/customers" class="text-slate-400 hover:text-slate-600 text-xs ml-1">&times;</a>
    <?php endif; ?>
  </form>

  <div class="flex items-center space-x-2 w-full sm:w-auto">
    <span class="text-xs text-slate-500 font-medium">Status:</span>
    <a href="/customers?q=<?= urlencode($search) ?>&status=all" class="text-xs px-3 py-1.5 rounded-lg font-medium transition <?= $status === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">All</a>
    <a href="/customers?q=<?= urlencode($search) ?>&status=active" class="text-xs px-3 py-1.5 rounded-lg font-medium transition <?= $status === 'active' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">Active</a>
    <a href="/customers?q=<?= urlencode($search) ?>&status=inactive" class="text-xs px-3 py-1.5 rounded-lg font-medium transition <?= $status === 'inactive' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">Inactive</a>
  </div>
</div>

<!-- Customer Directory Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
        <tr>
          <th class="py-3.5 px-4">Customer Name</th>
          <th class="py-3.5 px-4">Contact & Address</th>
          <th class="py-3.5 px-4 text-right">Opening Balance</th>
          <th class="py-3.5 px-4 text-right">Current Net Balance</th>
          <th class="py-3.5 px-4 text-center">Status</th>
          <th class="py-3.5 px-4 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <?php if (empty($customers)): ?>
          <tr>
            <td colspan="6" class="py-12 text-center text-slate-400">
              No customers found. <a href="/customers/create" class="text-blue-600 font-medium hover:underline">Add your first customer</a>.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($customers as $c): ?>
            <?php $bal = $c['balance']; ?>
            <tr class="hover:bg-slate-50/80 transition">
              <td class="py-3.5 px-4 font-semibold text-slate-900">
                <a href="/customers/show?id=<?= $c['id'] ?>" class="text-blue-600 hover:underline text-sm"><?= e($c['name']) ?></a>
                <div class="text-[10px] text-slate-400 font-normal">ID #<?= $c['id'] ?></div>
              </td>

              <td class="py-3.5 px-4">
                <div class="text-slate-800 font-medium"><?= e($c['phone'] ?: 'No phone') ?></div>
                <div class="text-[11px] text-slate-400 truncate max-w-[180px]"><?= e($c['address'] ?: '—') ?></div>
              </td>

              <td class="py-3.5 px-4 text-right font-medium">
                <?= formatINR(abs($c['opening_balance'])) ?>
                <div class="text-[10px] text-slate-400">
                  <?= $c['opening_balance'] > 0 ? 'Dr (Due)' : ($c['opening_balance'] < 0 ? 'Cr (Adv)' : 'Nil') ?>
                </div>
              </td>

              <td class="py-3.5 px-4 text-right">
                <?php if ($bal['outstanding_due'] > 0): ?>
                  <span class="text-sm font-extrabold text-rose-600 block"><?= formatINR($bal['outstanding_due']) ?></span>
                  <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Udhaar / Due</span>
                <?php elseif ($bal['advance_credit'] > 0): ?>
                  <span class="text-sm font-extrabold text-emerald-600 block"><?= formatINR($bal['advance_credit']) ?></span>
                  <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Advance Credit</span>
                <?php else: ?>
                  <span class="text-sm font-bold text-slate-500 block">₹0.00</span>
                  <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Settled</span>
                <?php endif; ?>
              </td>

              <td class="py-3.5 px-4 text-center">
                <?php if ($c['status'] === 'active'): ?>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Active</span>
                <?php else: ?>
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-600">Inactive</span>
                <?php endif; ?>
              </td>

              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end space-x-1.5">
                  <a href="/ledger?id=<?= $c['id'] ?>" title="Open Customer Ledger" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-[11px] transition">
                    Ledger
                  </a>
                  <a href="/invoices/create?customer_id=<?= $c['id'] ?>" title="Create Invoice" class="px-2.5 py-1 rounded bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium text-[11px] transition">
                    + Sale
                  </a>
                  <a href="/payments/create?customer_id=<?= $c['id'] ?>" title="Receive Payment" class="px-2.5 py-1 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium text-[11px] transition">
                    + Pay
                  </a>
                  <a href="/customers/edit?id=<?= $c['id'] ?>" title="Edit Profile" class="p-1 rounded text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  </a>
                </div>
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
