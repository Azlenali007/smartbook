<?php
/**
 * Installer Step 2: Server Requirements Check
 */
ob_start();
?>

<div class="space-y-6">
  <div>
    <h2 class="text-xl font-bold text-white tracking-tight">Step 2: Server Requirements Check</h2>
    <p class="text-xs text-slate-400 mt-1">Verifying hosting environment, PHP extensions, and folder permissions</p>
  </div>

  <div class="space-y-3">
    <?php foreach ($req['checks'] as $c): ?>
      <div class="p-3.5 bg-slate-900/60 rounded-xl border border-slate-700/80 flex items-center justify-between">
        <div>
          <div class="text-xs font-bold text-white"><?= e($c['name']) ?></div>
          <div class="text-[11px] text-slate-400"><?= e($c['detail']) ?></div>
        </div>

        <div>
          <?php if ($c['status'] === 'pass'): ?>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-800 flex items-center space-x-1">
              <span>✓ Pass</span>
            </span>
          <?php elseif ($c['status'] === 'warning'): ?>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-amber-950 text-amber-400 border border-amber-800">
              Warning
            </span>
          <?php else: ?>
            <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-rose-950 text-rose-400 border border-rose-800">
              ✕ Fail
            </span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($req['can_proceed']): ?>
    <div class="p-4 bg-emerald-950/40 rounded-xl border border-emerald-800 text-emerald-300 text-xs flex items-center space-x-2">
      <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      <span>All critical hosting requirements passed successfully! You can proceed to database setup.</span>
    </div>

    <div class="pt-4 flex items-center justify-between">
      <a href="/install?step=1" class="text-xs text-slate-400 hover:text-white">&larr; Back</a>
      <a href="/install?step=3" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs transition shadow flex items-center space-x-2">
        <span>Continue to Database Configuration</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </a>
    </div>
  <?php else: ?>
    <div class="p-4 bg-rose-950/60 rounded-xl border border-rose-800 text-rose-300 text-xs">
      <strong>Action Required:</strong> One or more critical requirements failed. Please ensure PHP 8.2+, PDO extensions, and write permissions on <code>config/</code> and <code>storage/</code> are resolved before continuing.
    </div>

    <div class="pt-4 flex items-center justify-between">
      <a href="/install?step=1" class="text-xs text-slate-400 hover:text-white">&larr; Back</a>
      <button onclick="window.location.reload()" class="bg-slate-700 hover:bg-slate-600 text-white font-bold py-2.5 px-6 rounded-xl text-xs transition">
        Re-check Requirements
      </button>
    </div>
  <?php endif; ?>
</div>

<?php
$installerContent = ob_get_clean();
require SK_APP . '/Views/layouts/installer.php';
?>
