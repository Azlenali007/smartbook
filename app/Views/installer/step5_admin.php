<?php
/**
 * Installer Step 5: Admin Account Setup & Business Details
 */
$dbDriver = $_GET['db_driver'] ?? 'sqlite';
$dbHost = $_GET['db_host'] ?? '127.0.0.1';
$dbPort = $_GET['db_port'] ?? '3306';
$dbName = $_GET['db_name'] ?? 'smart_khata';
$dbUser = $_GET['db_user'] ?? 'root';
$dbPass = $_GET['db_pass'] ?? '';

ob_start();
?>

<div class="space-y-6">
  <div>
    <h2 class="text-xl font-bold text-white tracking-tight">Step 5: Admin & Business Setup</h2>
    <p class="text-xs text-slate-400 mt-1">Create single administrator credentials and initial business settings</p>
  </div>

  <form action="/install/process" method="POST" class="space-y-6">
    <?= \SmartKhata\CSRF::getField() ?>

    <!-- Carried forward DB parameters -->
    <input type="hidden" name="db_driver" value="<?= e($dbDriver) ?>">
    <input type="hidden" name="db_host" value="<?= e($dbHost) ?>">
    <input type="hidden" name="db_port" value="<?= e($dbPort) ?>">
    <input type="hidden" name="db_name" value="<?= e($dbName) ?>">
    <input type="hidden" name="db_user" value="<?= e($dbUser) ?>">
    <input type="hidden" name="db_pass" value="<?= e($dbPass) ?>">

    <!-- Admin Account Section -->
    <div class="p-5 bg-slate-900/60 rounded-xl border border-slate-700 space-y-4">
      <div class="text-xs font-bold text-blue-400 uppercase tracking-wider flex items-center space-x-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        <span>Single Administrator Credentials</span>
      </div>

      <div>
        <label for="admin_username" class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Admin Username <span class="text-rose-500">*</span></label>
        <input type="text" id="admin_username" name="admin_username" value="admin" required
               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="admin_password" class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Password (min 8 chars) <span class="text-rose-500">*</span></label>
          <input type="password" id="admin_password" name="admin_password" required minlength="8" placeholder="••••••••"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div>
          <label for="admin_password_confirm" class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Confirm Password <span class="text-rose-500">*</span></label>
          <input type="password" id="admin_password_confirm" name="admin_password_confirm" required minlength="8" placeholder="••••••••"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
      </div>
    </div>

    <!-- Business Details Section -->
    <div class="p-5 bg-slate-900/60 rounded-xl border border-slate-700 space-y-4">
      <div class="text-xs font-bold text-blue-400 uppercase tracking-wider flex items-center space-x-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        <span>Shop / Business Profile</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="shop_name" class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Shop / Business Name <span class="text-rose-500">*</span></label>
          <input type="text" id="shop_name" name="shop_name" value="Smart Khata Store" required
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div>
          <label for="shop_phone" class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Phone Number</label>
          <input type="text" id="shop_phone" name="shop_phone" placeholder="e.g. 9876543210"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
      </div>

      <div>
        <label for="shop_address" class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Address</label>
        <input type="text" id="shop_address" name="shop_address" placeholder="e.g. Main Market, Commercial Area"
               class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="invoice_prefix" class="block text-[11px] font-bold text-slate-300 uppercase mb-1">Invoice Prefix</label>
          <input type="text" id="invoice_prefix" name="invoice_prefix" value="INV-"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div>
          <label for="shop_gstin" class="block text-[11px] font-bold text-slate-300 uppercase mb-1">GSTIN (Optional)</label>
          <input type="text" id="shop_gstin" name="shop_gstin" placeholder="Optional"
                 class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
      </div>
    </div>

    <!-- Submit Complete Installation -->
    <div class="pt-4 flex items-center justify-between border-t border-slate-700/80">
      <a href="/install?step=3" class="text-xs text-slate-400 hover:text-white">&larr; Back</a>
      <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-xl text-xs transition shadow-lg shadow-emerald-600/30 flex items-center space-x-2">
        <span>Complete Installation</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      </button>
    </div>
  </form>
</div>

<?php
$installerContent = ob_get_clean();
require SK_APP . '/Views/layouts/installer.php';
?>
