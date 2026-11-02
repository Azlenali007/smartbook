<?php
/**
 * Smart Khata - Add Customer View
 */
$pageTitle = 'Add Customer — Smart Khata';
ob_start();
?>

<div class="max-w-2xl mx-auto">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Customer</h1>
      <p class="text-sm text-slate-500 mt-1">Create customer khata account with initial opening balance</p>
    </div>
    <a href="/customers" class="text-sm font-semibold text-slate-600 hover:text-slate-900">&larr; Back to Customers</a>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="/customers/store" method="POST" class="space-y-6">
      <?= \SmartKhata\CSRF::getField() ?>

      <!-- Customer Name -->
      <div>
        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Customer Name <span class="text-rose-500">*</span></label>
        <input type="text" id="name" name="name" required autofocus
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
               placeholder="e.g. Ramesh Kumar / Sharma Traders">
      </div>

      <!-- Phone Number -->
      <div>
        <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Mobile / Phone Number</label>
        <input type="tel" id="phone" name="phone"
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
               placeholder="10-digit mobile number">
      </div>

      <!-- Address -->
      <div>
        <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Shop / Residence Address</label>
        <textarea id="address" name="address" rows="2"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                  placeholder="Street, City, Pin Code"></textarea>
      </div>

      <!-- Opening Balance Section -->
      <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Opening Balance (Initial Udhaar / Advance)</span>
          <span class="text-[11px] text-slate-500">Will be logged in chronological ledger</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1.5">Balance Type</label>
            <select name="balance_type" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
              <option value="due">Customer Owes (Previous Udhaar / Debt)</option>
              <option value="advance">Customer Advance (Prepaid Credit)</option>
            </select>
          </div>

          <div>
            <label for="opening_balance" class="block text-xs font-medium text-slate-600 mb-1.5">Amount (₹)</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold">₹</span>
              <input type="number" step="0.01" min="0" id="opening_balance" name="opening_balance" value="0.00"
                     class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
          </div>
        </div>

        <div>
          <label for="opening_balance_date" class="block text-xs font-medium text-slate-600 mb-1.5">As of Date</label>
          <input type="date" id="opening_balance_date" name="opening_balance_date" value="<?= date('Y-m-d') ?>"
                 class="w-full sm:w-1/2 bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:outline-none">
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Internal Notes (Optional)</label>
        <textarea id="notes" name="notes" rows="2"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                  placeholder="Special terms, landmark, or customer references"></textarea>
      </div>

      <!-- Submit -->
      <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
        <a href="/customers" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">Cancel</a>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition shadow shadow-blue-600/30">
          Save Customer Khata
        </button>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
