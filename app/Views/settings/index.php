<?php
/**
 * Smart Khata - Settings & Security View
 */
$pageTitle = 'Settings — Smart Khata';
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-8">
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Application Settings</h1>
    <p class="text-sm text-slate-500 mt-1">Configure business profile, invoice branding, numbering, and security</p>
  </div>

  <!-- Business Profile Card -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-base font-bold text-slate-900 mb-1">Business Profile & Invoice Header</h2>
    <p class="text-xs text-slate-500 mb-6">Appears on invoices, printable statements, and payment receipts</p>

    <form action="/settings/update-business" method="POST" class="space-y-6">
      <?= \SmartKhata\CSRF::getField() ?>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="shop_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Shop / Business Name <span class="text-rose-500">*</span></label>
          <input type="text" id="shop_name" name="shop_name" value="<?= e($settings['shop_name'] ?? 'Smart Khata') ?>" required
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>

        <div>
          <label for="shop_phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Shop Phone / Mobile</label>
          <input type="text" id="shop_phone" name="shop_phone" value="<?= e($settings['shop_phone'] ?? '') ?>"
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
      </div>

      <div>
        <label for="shop_address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Business Address</label>
        <textarea id="shop_address" name="shop_address" rows="2"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"><?= e($settings['shop_address'] ?? '') ?></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="shop_gstin" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">GSTIN Number (Optional)</label>
          <input type="text" id="shop_gstin" name="shop_gstin" value="<?= e($settings['shop_gstin'] ?? '') ?>" placeholder="e.g. 27ABCDE1234F1Z5"
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
          <p class="text-[11px] text-slate-400 mt-1">Leave empty if GST is not applicable.</p>
        </div>

        <div>
          <label for="gst_rate" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Default GST Rate (%)</label>
          <input type="number" step="0.1" min="0" max="28" id="gst_rate" name="gst_rate" value="<?= e($settings['gst_rate'] ?? '0') ?>"
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
          <p class="text-[11px] text-slate-400 mt-1">Default is 0% (non-GST).</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="invoice_prefix" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Number Prefix</label>
          <input type="text" id="invoice_prefix" name="invoice_prefix" value="<?= e($settings['invoice_prefix'] ?? 'INV-') ?>"
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
          <p class="text-[11px] text-slate-400 mt-1">e.g. INV- will generate INV-2026-0001</p>
        </div>

        <div>
          <label for="date_format" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Date Format</label>
          <select id="date_format" name="date_format" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="d-m-Y" selected>DD-MM-YYYY (Standard Indian Format)</option>
          </select>
        </div>
      </div>

      <div>
        <label for="invoice_footer" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Invoice Footer Notes & Terms</label>
        <textarea id="invoice_footer" name="invoice_footer" rows="2"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"><?= e($settings['invoice_footer'] ?? 'Thank you for your business!') ?></textarea>
      </div>

      <div class="pt-2 flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition shadow">
          Save Business Profile
        </button>
      </div>
    </form>
  </div>

  <!-- Admin Password Management Card -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <h2 class="text-base font-bold text-slate-900 mb-1">Admin Password & Security</h2>
    <p class="text-xs text-slate-500 mb-6">Logged in as Administrator: <strong class="text-slate-800"><?= e($admin['username'] ?? 'admin') ?></strong></p>

    <form action="/settings/update-password" method="POST" class="space-y-6">
      <?= \SmartKhata\CSRF::getField() ?>

      <div>
        <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Current Password</label>
        <input type="password" id="current_password" name="current_password" required
               class="w-full sm:w-80 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">New Password (min 8 chars)</label>
          <input type="password" id="new_password" name="new_password" required minlength="8"
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>

        <div>
          <label for="confirm_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirm New Password</label>
          <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        </div>
      </div>

      <div class="pt-2 flex justify-end">
        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition shadow">
          Change Admin Password
        </button>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
