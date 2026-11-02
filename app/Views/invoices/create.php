<?php
/**
 * Smart Khata - Create Invoice / New Sale View
 */
$pageTitle = 'New Sale / Invoice — Smart Khata';
ob_start();
?>

<div class="max-w-4xl mx-auto" x-data="invoiceForm()">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">New Sale / Invoice</h1>
      <p class="text-sm text-slate-500 mt-1">Manual item entry with pieces/nag quantity and automatic subtotal</p>
    </div>
    <a href="/invoices" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to Invoices</a>
  </div>

  <form action="/invoices/store" method="POST" class="space-y-6">
    <?= \SmartKhata\CSRF::getField() ?>

    <!-- Top Card: Customer & Date Selection -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="customer_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Select Customer <span class="text-rose-500">*</span></label>
          <select id="customer_id" name="customer_id" required
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="">-- Choose a customer --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $selectedCustomerId == $c['id'] ? 'selected' : '' ?>>
                <?= e($c['name']) ?> <?= !empty($c['phone']) ? '(' . e($c['phone']) . ')' : '' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <p class="text-[11px] text-slate-400 mt-1.5">Or <a href="/customers/create" class="text-blue-600 font-medium hover:underline">+ create a new customer</a></p>
        </div>

        <div>
          <label for="invoice_date" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Date <span class="text-rose-500">*</span></label>
          <input type="date" id="invoice_date" name="invoice_date" value="<?= date('Y-m-d') ?>" required
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
      </div>
    </div>

    <!-- Items Card: Dynamic Rows -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-base font-bold text-slate-900">Line Items</h2>
          <p class="text-xs text-slate-500">Unit: Piece / Nag only. Rates entered in Indian Rupees (₹).</p>
        </div>
        <button type="button" @click="addItem()" class="bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center space-x-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>Add Item Row</span>
        </button>
      </div>

      <!-- Items Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs mb-4">
          <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
            <tr>
              <th class="py-2.5 px-3">#</th>
              <th class="py-2.5 px-3 w-1/2">Item Description (Manual Name)</th>
              <th class="py-2.5 px-3 w-28 text-center">Qty (Pieces)</th>
              <th class="py-2.5 px-3 w-32 text-right">Rate (₹)</th>
              <th class="py-2.5 px-3 w-32 text-right">Amount (₹)</th>
              <th class="py-2.5 px-3 w-12 text-center"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <template x-for="(item, index) in items" :key="index">
              <tr class="hover:bg-slate-50/50">
                <td class="py-2.5 px-3 font-semibold text-slate-400" x-text="index + 1"></td>
                
                <td class="py-2.5 px-3">
                  <input type="text" :name="'item_name[' + index + ']'" x-model="item.name" required
                         placeholder="e.g. Cotton Shirt / Motor Pump / Nag"
                         class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </td>

                <td class="py-2.5 px-3">
                  <input type="number" :name="'quantity[' + index + ']'" x-model.number="item.quantity" min="1" step="1" required
                         class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-2 text-xs text-center font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </td>

                <td class="py-2.5 px-3">
                  <input type="number" :name="'rate[' + index + ']'" x-model.number="item.rate" min="0" step="0.01" required
                         class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs text-right font-semibold text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </td>

                <td class="py-2.5 px-3 text-right font-bold text-slate-900" x-text="formatCurrency(item.quantity * item.rate)"></td>

                <td class="py-2.5 px-3 text-center">
                  <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-slate-400 hover:text-rose-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Add Row Button Bottom -->
      <button type="button" @click="addItem()" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center space-x-1 py-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>+ Add another line item</span>
      </button>

      <!-- Calculations Summary Box -->
      <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-start justify-between gap-6">
        <div class="w-full sm:w-1/2 space-y-4">
          <div>
            <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Invoice Notes</label>
            <textarea id="notes" name="notes" rows="2"
                      placeholder="e.g. Goods delivered via transport / Bill reference"
                      class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
          </div>

          <label class="flex items-center space-x-2 text-xs text-slate-700 cursor-pointer">
            <input type="checkbox" name="apply_advance" value="yes" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span>Automatically adjust against available customer advance credit</span>
          </label>
        </div>

        <div class="w-full sm:w-72 bg-slate-50 rounded-2xl p-5 border border-slate-200 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-600">
            <span>Subtotal:</span>
            <span class="font-bold text-slate-900 text-sm" x-text="formatCurrency(subtotal())">₹0.00</span>
          </div>
          <div class="pt-2 border-t border-slate-200/80 flex items-center justify-between">
            <span class="font-bold text-slate-900 text-sm">Invoice Total:</span>
            <span class="font-extrabold text-blue-600 text-xl" x-text="formatCurrency(subtotal())">₹0.00</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Button -->
    <div class="flex items-center justify-end space-x-3">
      <a href="/invoices" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">Cancel</a>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition shadow-lg shadow-blue-600/30 flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>Generate Invoice</span>
      </button>
    </div>
  </form>
</div>

<script>
function invoiceForm() {
  return {
    items: [
      { name: '', quantity: 1, rate: 0 }
    ],
    addItem() {
      this.items.push({ name: '', quantity: 1, rate: 0 });
    },
    removeItem(idx) {
      if (this.items.length > 1) {
        this.items.splice(idx, 1);
      }
    },
    subtotal() {
      return this.items.reduce((sum, item) => {
        const q = parseFloat(item.quantity) || 0;
        const r = parseFloat(item.rate) || 0;
        return sum + (q * r);
      }, 0);
    },
    formatCurrency(val) {
      const num = parseFloat(val) || 0;
      return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
  };
}
</script>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
