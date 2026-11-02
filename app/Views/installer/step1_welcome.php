<?php
/**
 * Installer Step 1: Welcome
 */
ob_start();
?>

<div class="text-center space-y-4">
  <div class="w-16 h-16 rounded-2xl bg-blue-600/20 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-3xl mx-auto shadow-inner">
    ₹
  </div>
  <h2 class="text-2xl font-black text-white tracking-tight">Welcome to Smart Khata</h2>
  <p class="text-slate-300 text-sm leading-relaxed max-w-lg mx-auto">
    Smart Khata is a single-admin customer ledger, billing, udhaar management, invoice and payment tracking system built for speed, financial accuracy, and mobile PWA installability.
  </p>

  <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-700 text-left text-xs text-slate-300 space-y-2.5 my-6">
    <div class="font-bold text-white uppercase tracking-wider text-[11px]">System Capabilities & Modules:</div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>Double-entry verified customer udhaar & ledger balances with running history</span>
    </div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>Accurate integer paise money calculations — zero rounding errors</span>
    </div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>Automatic FIFO & manual payment allocations with customer advance retention</span>
    </div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>Progressive Web App (PWA) installable on Android & iPhone iOS Safari</span>
    </div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>Automated and manual database backup with SQL recovery</span>
    </div>
  </div>

  <div class="pt-4 flex justify-center">
    <a href="/install?step=2" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl text-sm transition shadow-lg shadow-blue-600/30 flex items-center space-x-2">
      <span>Start System Check</span>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
    </a>
  </div>
</div>

<?php
$installerContent = ob_get_clean();
require SK_APP . '/Views/layouts/installer.php';
?>
