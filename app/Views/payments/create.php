<?php
/**
 * Smart Khata - Receive Payment View
 */
$pageTitle = 'Receive Payment — Smart Khata';
ob_start();
?>

<div class="max-w-3xl mx-auto" x-data="paymentForm()">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Receive Customer Payment</h1>
      <p class="text-sm text-slate-500 mt-1">Automatic FIFO allocation or manual invoice-by-invoice settlement</p>
    </div>
    <a href="/payments" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to Payments</a>
  </div>

  <form action="/payments/store" method="POST" class="space-y-6">
    <?= \SmartKhata\CSRF::getField() ?>

    <!-- Main Payment Details Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Select Customer -->
        <div>
          <label for="customer_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Customer <span class="text-rose-500">*</span></label>
          <select id="customer_id" name="customer_id" required
                  onchange="window.location.href='/payments/create?customer_id=' + this.value"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
            <option value="">-- Choose a customer --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $selectedCustomerId == $c['id'] ? 'selected' : '' ?>>
                <?= e($c['name']) ?> <?= !empty($c['phone']) ? '(' . e($c['phone']) . ')' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Payment Date -->
        <div>
          <label for="payment_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Date <span class="text-rose-500">*</span></label>
          <input type="date" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
        </div>
      </div>

      <!-- Customer Balance Snapshot Banner -->
      <?php if ($customerBalance): ?>
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <span class="text-xs font-semibold text-slate-500">Customer Current Khata Balance:</span>
            <?php if ($customerBalance['outstanding_due'] > 0): ?>
              <span class="text-base font-extrabold text-rose-600 ml-2"><?= formatINR($customerBalance['outstanding_due']) ?> Due (Udhaar)</span>
            <?php elseif ($customerBalance['advance_credit'] > 0): ?>
              <span class="text-base font-extrabold text-emerald-600 ml-2"><?= formatINR($customerBalance['advance_credit']) ?> Advance Credit</span>
            <?php else: ?>
              <span class="text-sm font-bold text-slate-600 ml-2">₹0.00 Settled</span>
            <?php endif; ?>
          </div>
          <a href="/ledger?id=<?= $selectedCustomerId ?>" class="text-xs font-semibold text-blue-600 hover:underline">View Ledger Statement &rarr;</a>
        </div>
      <?php endif; ?>

      <!-- Payment Amount & Mode -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="amount" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Amount Received (₹) <span class="text-rose-500">*</span></label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-extrabold text-base">₹</span>
            <input type="number" step="0.01" min="0.01" id="amount" name="amount" x-model.number="paymentAmount" required
                   placeholder="0.00"
                   class="w-full pl-9 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-base font-extrabold text-emerald-600 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
          </div>
        </div>

        <div>
          <label for="payment_mode" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Mode <span class="text-rose-500">*</span></label>
          <select id="payment_mode" name="payment_mode" required
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
            <option value="Cash">Cash</option>
            <option value="UPI">UPI (GPay / PhonePe / Paytm / BHIM)</option>
            <option value="Bank Transfer">Bank Transfer (NEFT / IMPS / RTGS)</option>
            <option value="Cheque">Cheque</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>

      <!-- Reference & Notes -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="reference_no" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Reference / Transaction # (Optional)</label>
          <input type="text" id="reference_no" name="reference_no" placeholder="e.g. UTR / UPI Ref / Cheque No"
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
        </div>

        <div>
          <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Payment Notes (Optional)</label>
          <input type="text" id="notes" name="notes" placeholder="e.g. Deposited in shop counter"
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
        </div>
      </div>
    </div>

    <!-- Allocation Strategy Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div>
          <h2 class="text-base font-bold text-slate-900">Invoice Allocation Method</h2>
          <p class="text-xs text-slate-500">Choose how this payment is applied against unpaid invoices</p>
        </div>
        <div class="flex items-center space-x-3 text-xs">
          <label class="flex items-center space-x-1.5 cursor-pointer font-medium">
            <input type="radio" name="allocation_method" value="auto" x-model="allocationMethod" class="text-emerald-600 focus:ring-emerald-500">
            <span>Automatic (FIFO)</span>
          </label>
          <label class="flex items-center space-x-1.5 cursor-pointer font-medium">
            <input type="radio" name="allocation_method" value="manual" x-model="allocationMethod" class="text-emerald-600 focus:ring-emerald-500">
            <span>Manual Allocation</span>
          </label>
        </div>
      </div>

      <!-- Automatic Allocation Explainer -->
      <div x-show="allocationMethod === 'auto'" class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800 space-y-1">
        <div class="font-bold flex items-center space-x-1">
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span>Oldest Unpaid Invoice Cleared First (FIFO)</span>
        </div>
        <p>
          The system will automatically apply this payment towards unpaid invoices in chronological order. Any excess amount beyond total due invoices will be retained as <strong>Customer Advance Credit</strong>.
        </p>
      </div>

      <!-- Manual Allocation Table -->
      <div x-show="allocationMethod === 'manual'" class="space-y-4">
        <?php if (empty($customerInvoices)): ?>
          <div class="p-6 text-center text-slate-500 bg-slate-50 rounded-xl text-xs border border-slate-200">
            No unpaid invoices found for this customer. Any payment received will be retained entirely as <strong>Customer Advance Credit</strong>.
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
                <tr>
                  <th class="py-2.5 px-3">Invoice #</th>
                  <th class="py-2.5 px-3">Date</th>
                  <th class="py-2.5 px-3 text-right">Invoice Total</th>
                  <th class="py-2.5 px-3 text-right">Remaining Due</th>
                  <th class="py-2.5 px-3 text-right w-36">Allocate (₹)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php foreach ($customerInvoices as $inv): ?>
                  <?php $dueRs = (float)($inv['due_amount'] / 100); ?>
                  <tr>
                    <td class="py-2.5 px-3 font-semibold text-blue-600"><?= e($inv['invoice_number']) ?></td>
                    <td class="py-2.5 px-3"><?= formatDate($inv['invoice_date']) ?></td>
                    <td class="py-2.5 px-3 text-right"><?= formatINR($inv['total_amount']) ?></td>
                    <td class="py-2.5 px-3 text-right font-bold text-rose-600"><?= formatINR($inv['due_amount']) ?></td>
                    <td class="py-2.5 px-3 text-right">
                      <input type="number" step="0.01" min="0" max="<?= $dueRs ?>"
                             name="allocated_invoices[<?= $inv['id'] ?>]"
                             placeholder="0.00"
                             class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-right text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Submit Action -->
    <div class="flex items-center justify-end space-x-3">
      <a href="/payments" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">Cancel</a>
      <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition shadow-lg shadow-emerald-600/30 flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>Save Payment & Receipt</span>
      </button>
    </div>
  </form>
</div>

<script>
function paymentForm() {
  return {
    paymentAmount: '',
    allocationMethod: 'auto'
  };
}
</script>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
