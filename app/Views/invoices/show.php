<?php
/**
 * Smart Khata - Invoice Detail & Audit View
 */
$pageTitle = 'Invoice #' . e($invoice['invoice_number']) . ' — Smart Khata';
ob_start();
?>

<div class="max-w-4xl mx-auto">
  <!-- Top Bar Actions -->
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center space-x-3">
      <a href="/invoices" class="text-sm font-semibold text-slate-500 hover:text-slate-800">&larr; Invoices</a>
      <span class="text-slate-300">/</span>
      <h1 class="text-xl font-bold tracking-tight text-slate-900"><?= e($invoice['invoice_number']) ?></h1>
      <?php if ($invoice['is_reversed']): ?>
        <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-slate-200 text-slate-600 line-through">Reversed / Cancelled</span>
      <?php elseif ($invoice['status'] === 'paid'): ?>
        <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">Paid in Full</span>
      <?php elseif ($invoice['status'] === 'partial'): ?>
        <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">Partially Paid</span>
      <?php else: ?>
        <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800">Unpaid Due</span>
      <?php endif; ?>
    </div>

    <div class="flex items-center space-x-2">
      <a href="/invoices/pdf?id=<?= $invoice['id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition shadow flex items-center space-x-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span>Download PDF</span>
      </a>
      <a href="/invoices/print?id=<?= $invoice['id'] ?>" target="_blank" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-xl transition shadow flex items-center space-x-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        <span>Print Bill</span>
      </a>
    </div>
  </div>

  <!-- Main Invoice Card -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-10 mb-8">
    <!-- Header: Shop & Invoice Meta -->
    <div class="flex flex-col sm:flex-row justify-between pb-8 border-b border-slate-100 gap-6">
      <div>
        <h2 class="text-2xl font-black tracking-tight text-slate-900"><?= e($settings['shop_name'] ?? 'Smart Khata') ?></h2>
        <p class="text-xs text-slate-500 mt-1"><?= e($settings['shop_address'] ?? '') ?></p>
        <?php if (!empty($settings['shop_phone'])): ?>
          <p class="text-xs text-slate-500">Phone: <?= e($settings['shop_phone']) ?></p>
        <?php endif; ?>
        <?php if (!empty($settings['shop_gstin'])): ?>
          <p class="text-xs text-slate-500 font-medium mt-0.5">GSTIN: <?= e($settings['shop_gstin']) ?></p>
        <?php endif; ?>
      </div>

      <div class="sm:text-right">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">TAX INVOICE</div>
        <div class="text-xl font-extrabold text-blue-600 mt-0.5"><?= e($invoice['invoice_number']) ?></div>
        <div class="text-xs text-slate-600 mt-1">Date: <strong class="font-semibold text-slate-800"><?= formatDate($invoice['invoice_date']) ?></strong></div>
        <div class="text-xs text-slate-500">Issued: <?= date('d-m-Y H:i', strtotime($invoice['created_at'])) ?></div>
      </div>
    </div>

    <!-- Customer Billed To -->
    <div class="py-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between gap-4">
      <div>
        <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Billed To</div>
        <a href="/customers/show?id=<?= $invoice['customer_id'] ?>" class="text-base font-bold text-slate-900 hover:text-blue-600 hover:underline">
          <?= e($invoice['customer_name']) ?>
        </a>
        <div class="text-xs text-slate-600 mt-0.5"><?= e($invoice['customer_phone'] ?: 'No phone') ?></div>
        <div class="text-xs text-slate-500"><?= e($invoice['customer_address'] ?: '') ?></div>
      </div>

      <div class="sm:text-right bg-slate-50 p-4 rounded-xl border border-slate-100">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Customer Balance Snapshot</div>
        <?php if ($customerBalance['outstanding_due'] > 0): ?>
          <div class="text-base font-extrabold text-rose-600"><?= formatINR($customerBalance['outstanding_due']) ?> Due</div>
        <?php elseif ($customerBalance['advance_credit'] > 0): ?>
          <div class="text-base font-extrabold text-emerald-600"><?= formatINR($customerBalance['advance_credit']) ?> Advance</div>
        <?php else: ?>
          <div class="text-base font-bold text-slate-600">Settled</div>
        <?php endif; ?>
        <a href="/ledger?id=<?= $invoice['customer_id'] ?>" class="text-[11px] text-blue-600 font-semibold hover:underline mt-1 block">View Customer Ledger &rarr;</a>
      </div>
    </div>

    <!-- Line Items Table -->
    <div class="py-6 border-b border-slate-100 overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
          <tr>
            <th class="pb-3 px-2">#</th>
            <th class="pb-3 px-2">Item Description</th>
            <th class="pb-3 px-2 text-center">Quantity</th>
            <th class="pb-3 px-2 text-right">Rate</th>
            <th class="pb-3 px-2 text-right">Total Amount</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php foreach ($items as $idx => $item): ?>
            <tr>
              <td class="py-3 px-2 text-slate-400 font-medium"><?= $idx + 1 ?></td>
              <td class="py-3 px-2 font-semibold text-slate-900"><?= e($item['item_name']) ?></td>
              <td class="py-3 px-2 text-center font-medium"><?= e($item['quantity']) ?> Piece</td>
              <td class="py-3 px-2 text-right font-medium"><?= formatINR($item['unit_price']) ?></td>
              <td class="py-3 px-2 text-right font-bold text-slate-900"><?= formatINR($item['line_total']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Invoice Totals & Balance Breakdown -->
    <div class="py-6 flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-slate-100">
      <div class="text-xs text-slate-500 max-w-sm">
        <?php if (!empty($invoice['notes'])): ?>
          <div class="font-semibold text-slate-700 mb-1">Invoice Notes:</div>
          <p class="leading-relaxed"><?= nl2br(e($invoice['notes'])) ?></p>
        <?php endif; ?>
      </div>

      <div class="w-full sm:w-80 space-y-2 text-xs">
        <div class="flex justify-between text-slate-600">
          <span>Subtotal:</span>
          <span class="font-semibold text-slate-900"><?= formatINR($invoice['subtotal']) ?></span>
        </div>
        <?php if ((int)$invoice['tax_amount'] > 0): ?>
          <div class="flex justify-between text-slate-600">
            <span>Tax / GST:</span>
            <span class="font-semibold text-slate-900"><?= formatINR($invoice['tax_amount']) ?></span>
          </div>
        <?php endif; ?>
        <div class="pt-2 border-t border-slate-200 flex justify-between font-bold text-sm text-slate-900">
          <span>Total Invoice Amount:</span>
          <span class="font-extrabold text-blue-600 text-base"><?= formatINR($invoice['total_amount']) ?></span>
        </div>
        <div class="flex justify-between text-emerald-700 font-medium">
          <span>Amount Paid:</span>
          <span><?= formatINR($invoice['paid_amount']) ?></span>
        </div>
        <div class="pt-2 border-t border-slate-200 flex justify-between font-extrabold text-sm">
          <span class="text-slate-800">Remaining Due:</span>
          <span class="<?= $invoice['due_amount'] > 0 ? 'text-rose-600' : 'text-slate-500' ?>"><?= formatINR($invoice['due_amount']) ?></span>
        </div>
      </div>
    </div>

    <!-- Payment Allocations Section -->
    <?php if (!empty($allocations)): ?>
      <div class="pt-6">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3">Linked Payment Allocations</h3>
        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 space-y-2">
          <?php foreach ($allocations as $alloc): ?>
            <div class="flex items-center justify-between text-xs">
              <div class="flex items-center space-x-2">
                <a href="/payments/show?id=<?= $alloc['payment_id'] ?>" class="font-semibold text-blue-600 hover:underline">
                  <?= e($alloc['payment_number']) ?>
                </a>
                <span class="text-slate-400">&bull; <?= formatDate($alloc['payment_date']) ?> &bull; <?= e($alloc['payment_mode']) ?></span>
              </div>
              <span class="font-bold text-emerald-700"><?= formatINR($alloc['allocated_amount']) ?> allocated</span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Footer Note -->
    <?php if (!empty($settings['invoice_footer'])): ?>
      <div class="mt-8 pt-4 border-t border-slate-100 text-center text-xs text-slate-400">
        <?= e($settings['invoice_footer']) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Reversal & Audit Protection Panel -->
  <?php if (!$invoice['is_reversed']): ?>
    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6" x-data="{ showModal: false }">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="font-bold text-slate-900 text-sm">Invoice Audit & Reversal</h3>
          <p class="text-xs text-slate-500 mt-0.5">Issued invoices cannot be deleted. If issued by error, record a linked reversal.</p>
        </div>
        <button type="button" @click="showModal = true" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold px-4 py-2 rounded-xl transition">
          Reverse / Cancel Invoice
        </button>
      </div>

      <!-- Reversal Modal -->
      <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="showModal = false">
          <h3 class="font-bold text-slate-900 text-lg mb-2">Confirm Invoice Reversal</h3>
          <p class="text-xs text-slate-600 mb-4 leading-relaxed">
            Reversing this invoice will mark it as cancelled, reverse its debit impact on customer's khata ledger, and un-allocate any payments.
          </p>

          <form action="/invoices/reverse" method="POST" class="space-y-4">
            <?= \SmartKhata\CSRF::getField() ?>
            <input type="hidden" name="id" value="<?= $invoice['id'] ?>">

            <div>
              <label for="reversal_reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Reversal Reason <span class="text-rose-500">*</span></label>
              <textarea id="reversal_reason" name="reversal_reason" required rows="3"
                        placeholder="e.g. Billed to wrong customer / Order cancelled / Return"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
              <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                Abort
              </button>
              <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow">
                Confirm Reversal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="bg-rose-50 rounded-2xl border border-rose-200 p-6 text-rose-900 text-xs">
      <div class="font-bold text-sm mb-1">Invoice Reversed on <?= date('d-m-Y H:i', strtotime($invoice['reversed_at'])) ?></div>
      <p>Reason: <?= e($invoice['reversal_reason']) ?></p>
    </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
