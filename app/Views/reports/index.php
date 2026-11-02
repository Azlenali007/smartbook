<?php
/**
 * Smart Khata - Reports & Reconciliation Dashboard
 */
$pageTitle = 'Reports & Audit — Smart Khata';
ob_start();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Financial Reports & Reconciliation</h1>
    <p class="text-sm text-slate-500 mt-1">Audit sales, collections, debtors, payment methods, and ledger integrity</p>
  </div>
</div>

<!-- Report Tabs -->
<div class="mb-6 border-b border-slate-200 overflow-x-auto">
  <div class="flex space-x-2 text-xs font-semibold whitespace-nowrap pb-px">
    <a href="/reports?tab=sales" class="px-4 py-2.5 rounded-t-xl transition <?= $tab === 'sales' ? 'bg-white border-t border-l border-r border-slate-200 text-blue-600 font-bold -mb-px' : 'text-slate-500 hover:text-slate-800' ?>">Sales Report</a>
    <a href="/reports?tab=collections" class="px-4 py-2.5 rounded-t-xl transition <?= $tab === 'collections' ? 'bg-white border-t border-l border-r border-slate-200 text-blue-600 font-bold -mb-px' : 'text-slate-500 hover:text-slate-800' ?>">Collections Report</a>
    <a href="/reports?tab=outstanding" class="px-4 py-2.5 rounded-t-xl transition <?= $tab === 'outstanding' ? 'bg-white border-t border-l border-r border-slate-200 text-blue-600 font-bold -mb-px' : 'text-slate-500 hover:text-slate-800' ?>">Outstanding Debtors</a>
    <a href="/reports?tab=advances" class="px-4 py-2.5 rounded-t-xl transition <?= $tab === 'advances' ? 'bg-white border-t border-l border-r border-slate-200 text-blue-600 font-bold -mb-px' : 'text-slate-500 hover:text-slate-800' ?>">Customer Advances</a>
    <a href="/reports?tab=invoicedue" class="px-4 py-2.5 rounded-t-xl transition <?= $tab === 'invoicedue' ? 'bg-white border-t border-l border-r border-slate-200 text-blue-600 font-bold -mb-px' : 'text-slate-500 hover:text-slate-800' ?>">Invoice Dues</a>
    <a href="/reports?tab=reconciliation" class="px-4 py-2.5 rounded-t-xl transition <?= $tab === 'reconciliation' ? 'bg-white border-t border-l border-r border-slate-200 text-blue-600 font-bold -mb-px' : 'text-slate-500 hover:text-slate-800' ?>">Audit Reconciliation</a>
  </div>
</div>

<?php if ($tab === 'sales'): ?>
  <!-- Date Filter Bar for Sales -->
  <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
    <form action="/reports" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
      <input type="hidden" name="tab" value="sales">
      <div class="flex items-center space-x-1.5">
        <span class="font-semibold text-slate-600">From:</span>
        <input type="date" name="start_date" value="<?= e($startDate) ?>" class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800">
      </div>
      <div class="flex items-center space-x-1.5">
        <span class="font-semibold text-slate-600">To:</span>
        <input type="date" name="end_date" value="<?= e($endDate) ?>" class="bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-800">
      </div>
      <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-1.5 rounded-lg transition">Apply</button>
    </form>
  </div>

  <div class="space-y-8">
    <!-- Daily Sales Breakdown -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-bold text-slate-900 text-base">Daily Sales Summary</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
            <tr>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4 text-center">Invoices Issued</th>
              <th class="py-3 px-4 text-right">Subtotal</th>
              <th class="py-3 px-4 text-right">Tax / GST</th>
              <th class="py-3 px-4 text-right">Total Billed</th>
              <th class="py-3 px-4 text-right">Allocated Paid</th>
              <th class="py-3 px-4 text-right">Remaining Due</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php if (empty($dailySales)): ?>
              <tr><td colspan="7" class="py-8 text-center text-slate-400">No sales recorded in selected date range.</td></tr>
            <?php else: ?>
              <?php foreach ($dailySales as $ds): ?>
                <tr class="hover:bg-slate-50 transition">
                  <td class="py-3 px-4 font-semibold text-slate-900"><?= formatDate($ds['invoice_date']) ?></td>
                  <td class="py-3 px-4 text-center font-bold text-slate-800"><?= $ds['invoice_count'] ?></td>
                  <td class="py-3 px-4 text-right"><?= formatINR($ds['subtotal']) ?></td>
                  <td class="py-3 px-4 text-right"><?= formatINR($ds['tax_amount']) ?></td>
                  <td class="py-3 px-4 text-right font-extrabold text-blue-600"><?= formatINR($ds['total_amount']) ?></td>
                  <td class="py-3 px-4 text-right font-semibold text-emerald-600"><?= formatINR($ds['paid_amount']) ?></td>
                  <td class="py-3 px-4 text-right font-bold text-rose-600"><?= formatINR($ds['due_amount']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Customer-wise Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-slate-100">
        <h3 class="font-bold text-slate-900 text-base">Customer-Wise Total Sales Performance</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
            <tr>
              <th class="py-3 px-4">Customer</th>
              <th class="py-3 px-4 text-center">Invoices</th>
              <th class="py-3 px-4 text-right">Total Sales</th>
              <th class="py-3 px-4 text-right">Total Payments</th>
              <th class="py-3 px-4 text-right">Invoice Due</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php foreach ($customerSales as $cs): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-semibold text-slate-900">
                  <a href="/customers/show?id=<?= $cs['id'] ?>" class="text-blue-600 hover:underline"><?= e($cs['name']) ?></a>
                </td>
                <td class="py-3 px-4 text-center"><?= $cs['invoice_count'] ?></td>
                <td class="py-3 px-4 text-right font-extrabold text-slate-900"><?= formatINR($cs['total_sales']) ?></td>
                <td class="py-3 px-4 text-right font-semibold text-emerald-600"><?= formatINR($cs['total_received']) ?></td>
                <td class="py-3 px-4 text-right font-bold text-rose-600"><?= formatINR($cs['total_due']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php elseif ($tab === 'collections'): ?>
  <div class="space-y-8">
    <!-- Payment Modes Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($paymentModes as $pm): ?>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1"><?= e($pm['payment_mode']) ?></div>
          <div class="text-2xl font-black text-slate-900"><?= formatINR($pm['total_amount']) ?></div>
          <div class="text-xs text-slate-500 mt-2"><?= $pm['count'] ?> transactions recorded</div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Daily Collections Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-bold text-slate-900 text-base">Collections by Date & Mode</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
            <tr>
              <th class="py-3 px-4">Date</th>
              <th class="py-3 px-4">Mode</th>
              <th class="py-3 px-4 text-center">Receipts</th>
              <th class="py-3 px-4 text-right">Total Collected</th>
              <th class="py-3 px-4 text-right">Allocated to Bills</th>
              <th class="py-3 px-4 text-right">Advance Retained</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <?php if (empty($dailyCollections)): ?>
              <tr><td colspan="6" class="py-8 text-center text-slate-400">No collections found.</td></tr>
            <?php else: ?>
              <?php foreach ($dailyCollections as $dc): ?>
                <tr class="hover:bg-slate-50 transition">
                  <td class="py-3 px-4 font-semibold text-slate-900"><?= formatDate($dc['payment_date']) ?></td>
                  <td class="py-3 px-4 font-medium"><span class="px-2 py-0.5 rounded text-[10px] bg-slate-100"><?= e($dc['payment_mode']) ?></span></td>
                  <td class="py-3 px-4 text-center"><?= $dc['payment_count'] ?></td>
                  <td class="py-3 px-4 text-right font-extrabold text-emerald-600"><?= formatINR($dc['total_amount']) ?></td>
                  <td class="py-3 px-4 text-right font-medium text-slate-800"><?= formatINR($dc['allocated_amount']) ?></td>
                  <td class="py-3 px-4 text-right font-medium text-blue-600"><?= formatINR($dc['advance_amount']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php elseif ($tab === 'outstanding'): ?>
  <!-- Outstanding Customers (Debtors) -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h3 class="font-bold text-slate-900 text-base">Outstanding Customer Udhaar List</h3>
        <p class="text-xs text-slate-500">Customers with positive net balance (ranked highest debtor first)</p>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">#</th>
            <th class="py-3 px-4">Customer Name</th>
            <th class="py-3 px-4">Phone</th>
            <th class="py-3 px-4 text-right">Total Invoiced</th>
            <th class="py-3 px-4 text-right">Total Paid</th>
            <th class="py-3 px-4 text-right">Outstanding Due (Udhaar)</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($outstanding)): ?>
            <tr><td colspan="7" class="py-12 text-center text-slate-400">All customer accounts are completely settled! Zero outstanding udhaar.</td></tr>
          <?php else: ?>
            <?php foreach ($outstanding as $idx => $o): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 text-slate-400"><?= $idx + 1 ?></td>
                <td class="py-3 px-4 font-bold text-slate-900">
                  <a href="/customers/show?id=<?= $o['customer_id'] ?>" class="text-blue-600 hover:underline"><?= e($o['name']) ?></a>
                </td>
                <td class="py-3 px-4"><?= e($o['phone'] ?: '—') ?></td>
                <td class="py-3 px-4 text-right"><?= formatINR($o['total_invoiced']) ?></td>
                <td class="py-3 px-4 text-right font-medium text-emerald-600"><?= formatINR($o['total_paid']) ?></td>
                <td class="py-3 px-4 text-right font-black text-rose-600 text-sm"><?= formatINR($o['outstanding_due']) ?></td>
                <td class="py-3 px-4 text-right space-x-1">
                  <a href="/payments/create?customer_id=<?= $o['customer_id'] ?>" class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 font-semibold hover:bg-emerald-100">
                    + Receive Pay
                  </a>
                  <a href="/ledger?id=<?= $o['customer_id'] ?>" class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200">
                    Ledger
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php elseif ($tab === 'advances'): ?>
  <!-- Customer Advances List -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100">
      <h3 class="font-bold text-slate-900 text-base">Customer Advance Credits</h3>
      <p class="text-xs text-slate-500">Unallocated payments / prepaid credit stored in customer accounts</p>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">#</th>
            <th class="py-3 px-4">Customer Name</th>
            <th class="py-3 px-4">Phone</th>
            <th class="py-3 px-4 text-right">Advance Balance</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($advances)): ?>
            <tr><td colspan="5" class="py-12 text-center text-slate-400">No customer advance credits recorded.</td></tr>
          <?php else: ?>
            <?php foreach ($advances as $idx => $a): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 text-slate-400"><?= $idx + 1 ?></td>
                <td class="py-3 px-4 font-bold text-slate-900">
                  <a href="/customers/show?id=<?= $a['customer_id'] ?>" class="text-blue-600 hover:underline"><?= e($a['name']) ?></a>
                </td>
                <td class="py-3 px-4"><?= e($a['phone'] ?: '—') ?></td>
                <td class="py-3 px-4 text-right font-black text-emerald-600 text-sm"><?= formatINR($a['advance_credit']) ?></td>
                <td class="py-3 px-4 text-right">
                  <a href="/invoices/create?customer_id=<?= $a['customer_id'] ?>" class="px-2.5 py-1 rounded bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100">
                    + New Sale
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php elseif ($tab === 'invoicedue'): ?>
  <!-- Invoice Wise Dues -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100">
      <h3 class="font-bold text-slate-900 text-base">Unpaid & Partially Paid Invoices</h3>
      <p class="text-xs text-slate-500">Every bill with remaining due amount</p>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">Invoice #</th>
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4 text-right">Total Bill</th>
            <th class="py-3 px-4 text-right">Paid</th>
            <th class="py-3 px-4 text-right">Remaining Due</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($invoiceDues)): ?>
            <tr><td colspan="7" class="py-12 text-center text-slate-400">All invoices are 100% paid!</td></tr>
          <?php else: ?>
            <?php foreach ($invoiceDues as $idue): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-bold text-blue-600">
                  <a href="/invoices/show?id=<?= $idue['id'] ?>" class="hover:underline"><?= e($idue['invoice_number']) ?></a>
                </td>
                <td class="py-3 px-4"><?= formatDate($idue['invoice_date']) ?></td>
                <td class="py-3 px-4 font-semibold text-slate-900"><?= e($idue['customer_name']) ?></td>
                <td class="py-3 px-4 text-right font-bold text-slate-900"><?= formatINR($idue['total_amount']) ?></td>
                <td class="py-3 px-4 text-right font-semibold text-emerald-600"><?= formatINR($idue['paid_amount']) ?></td>
                <td class="py-3 px-4 text-right font-black text-rose-600"><?= formatINR($idue['due_amount']) ?></td>
                <td class="py-3 px-4 text-right">
                  <a href="/payments/create?customer_id=<?= $idue['customer_id'] ?>" class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 font-semibold hover:bg-emerald-100">
                    Receive
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php elseif ($tab === 'reconciliation'): ?>
  <!-- Full System Financial Reconciliation Check -->
  <div class="space-y-6">
    <div class="p-6 rounded-2xl border <?= $reconciliation['is_healthy'] ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' ?>">
      <div class="flex items-center space-x-3 mb-2">
        <?php if ($reconciliation['is_healthy']): ?>
          <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold">✓</div>
          <div>
            <h3 class="font-bold text-base text-emerald-900">System Fully Balanced & Reconciled</h3>
            <p class="text-xs text-emerald-700">All invoices, payments, allocations, and customer ledger running balances verify identically.</p>
          </div>
        <?php else: ?>
          <div class="w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold">!</div>
          <div>
            <h3 class="font-bold text-base text-rose-900">Discrepancy Detected</h3>
            <p class="text-xs text-rose-700">Audit logs indicate an unbalanced transaction formula.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Verification Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-3">
        <h4 class="font-bold text-sm text-slate-900">Invoice Reconciliation</h4>
        <div class="text-xs space-y-1.5 text-slate-600">
          <div class="flex justify-between"><span>Sum of Invoices:</span> <strong><?= formatINR($reconciliation['sum_invoices']) ?></strong></div>
          <div class="flex justify-between"><span>Sum Paid:</span> <strong><?= formatINR($reconciliation['sum_invoice_paids']) ?></strong></div>
          <div class="flex justify-between"><span>Sum Due:</span> <strong><?= formatINR($reconciliation['sum_invoice_dues']) ?></strong></div>
          <div class="pt-2 border-t border-slate-100 flex justify-between font-bold text-slate-800">
            <span>Invoices Match:</span>
            <span class="<?= $reconciliation['invoices_reconciled'] ? 'text-emerald-600' : 'text-rose-600' ?>">
              <?= $reconciliation['invoices_reconciled'] ? 'Exact Match' : 'Discrepancy' ?>
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-3">
        <h4 class="font-bold text-sm text-slate-900">Payment Allocations</h4>
        <div class="text-xs space-y-1.5 text-slate-600">
          <div class="flex justify-between"><span>Total Payments:</span> <strong><?= formatINR($reconciliation['sum_payments']) ?></strong></div>
          <div class="flex justify-between"><span>Total Allocated:</span> <strong><?= formatINR($reconciliation['sum_allocations']) ?></strong></div>
          <div class="flex justify-between"><span>Retained Advances:</span> <strong><?= formatINR($reconciliation['total_customer_advances']) ?></strong></div>
          <div class="pt-2 border-t border-slate-100 flex justify-between font-bold text-slate-800">
            <span>Allocations Match:</span>
            <span class="<?= $reconciliation['allocations_reconciled'] ? 'text-emerald-600' : 'text-rose-600' ?>">
              <?= $reconciliation['allocations_reconciled'] ? 'Exact Match' : 'Discrepancy' ?>
            </span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm space-y-3">
        <h4 class="font-bold text-sm text-slate-900">Customer Ledgers</h4>
        <div class="text-xs space-y-1.5 text-slate-600">
          <div class="flex justify-between"><span>Opening Balances:</span> <strong><?= formatINR($reconciliation['sum_opening']) ?></strong></div>
          <div class="flex justify-between"><span>Customer Debts:</span> <strong><?= formatINR($reconciliation['total_customer_dues']) ?></strong></div>
          <div class="flex justify-between"><span>Customer Advances:</span> <strong><?= formatINR($reconciliation['total_customer_advances']) ?></strong></div>
          <div class="pt-2 border-t border-slate-100 flex justify-between font-bold text-slate-800">
            <span>Ledger Integrity:</span>
            <span class="<?= $reconciliation['ledger_reconciled'] ? 'text-emerald-600' : 'text-rose-600' ?>">
              <?= $reconciliation['ledger_reconciled'] ? 'Verified Balanced' : 'Unbalanced' ?>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
