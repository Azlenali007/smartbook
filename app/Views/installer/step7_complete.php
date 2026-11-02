<?php
/**
 * Installer Step 7: Installation Complete & Lock
 */
$installedUser = $_SESSION['installed_username'] ?? 'admin';
unset($_SESSION['installed_username']);
ob_start();
?>

<div class="text-center space-y-5">
  <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-3xl mx-auto shadow-lg shadow-emerald-600/30">
    ✓
  </div>

  <h2 class="text-2xl font-black text-white tracking-tight">Installation Completed Successfully!</h2>
  <p class="text-slate-300 text-sm leading-relaxed max-w-md mx-auto">
    Smart Khata has been securely provisioned, database schema installed, administrator account created, and installation permanently locked.
  </p>

  <div class="p-5 bg-slate-900/60 rounded-xl border border-slate-700 text-left text-xs text-slate-300 space-y-2.5 my-6">
    <div class="font-bold text-white uppercase tracking-wider text-[11px] mb-2">Verified System State:</div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>11 InnoDB / SQLite database tables created & indexed</span>
    </div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>Single Administrator provisioned: <strong class="text-white"><?= e($installedUser) ?></strong></span>
    </div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>Security lock written: <code class="text-blue-400">config/installed.lock</code></span>
    </div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>Installer route locked permanently against re-execution</span>
    </div>
    <div class="flex items-center space-x-2">
      <span class="text-emerald-400 font-bold">✓</span>
      <span>PWA Service Worker and Manifest active</span>
    </div>
  </div>

  <div class="pt-4 flex justify-center">
    <a href="/login" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl text-sm transition shadow-lg shadow-blue-600/30 flex items-center space-x-2">
      <span>Open Admin Login</span>
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
    </a>
  </div>
</div>

<?php
$installerContent = ob_get_clean();
require SK_APP . '/Views/layouts/installer.php';
?>
