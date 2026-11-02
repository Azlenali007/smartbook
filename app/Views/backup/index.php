<?php
/**
 * Smart Khata - Backup & Restore Management View
 */
$pageTitle = 'Backup & Restore — Smart Khata';
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-8">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-slate-900">Database Backup & Recovery</h1>
      <p class="text-sm text-slate-500 mt-1">Manual snapshots, authenticated SQL dumps, and automated cron scheduler</p>
    </div>

    <!-- Create Backup Action -->
    <form action="/backup/create" method="POST">
      <?= \SmartKhata\CSRF::getField() ?>
      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
        <span>Create Backup Now</span>
      </button>
    </form>
  </div>

  <!-- Existing Backups List -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-bold text-slate-900 text-base">Available Database Backups</h2>
      <span class="text-xs text-slate-500">Stored safely in /storage/backups</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 uppercase border-b border-slate-200">
          <tr>
            <th class="py-3 px-4">Backup Filename</th>
            <th class="py-3 px-4">Created Timestamp</th>
            <th class="py-3 px-4 text-right">Size</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($backups)): ?>
            <tr><td colspan="4" class="py-8 text-center text-slate-400">No backups created yet. Click "Create Backup Now" above.</td></tr>
          <?php else: ?>
            <?php foreach ($backups as $b): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-mono font-semibold text-slate-900"><?= e($b['filename']) ?></td>
                <td class="py-3 px-4 text-slate-600"><?= date('d-m-Y H:i:s', strtotime($b['created_at'])) ?></td>
                <td class="py-3 px-4 text-right font-medium"><?= number_format($b['filesize'] / 1024, 1) ?> KB</td>
                <td class="py-3 px-4 text-right">
                  <a href="/backup/download?file=<?= urlencode($b['filename']) ?>" class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-[11px] transition inline-flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download SQL</span>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Restore Database Card -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8" x-data="{ confirmed: false }">
    <div class="flex items-center space-x-2 text-rose-600 mb-2">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      <h2 class="font-bold text-slate-900 text-base">Restore Database from SQL File</h2>
    </div>
    <p class="text-xs text-slate-600 mb-6 leading-relaxed">
      Warning: Restoring will overwrite existing ledger entries, customers, and invoices with the contents of the uploaded SQL dump. Always create a backup before restoring.
    </p>

    <form action="/backup/restore" method="POST" enctype="multipart/form-data" class="space-y-4">
      <?= \SmartKhata\CSRF::getField() ?>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Choose .SQL Backup File</label>
        <input type="file" name="backup_file" accept=".sql" required
               class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-800 hover:file:bg-slate-200">
      </div>

      <label class="flex items-center space-x-2 text-xs text-slate-700 cursor-pointer pt-2">
        <input type="checkbox" x-model="confirmed" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
        <span class="font-semibold text-rose-700">I confirm that I understand this will replace current database records with the backup file.</span>
      </label>

      <div class="pt-2">
        <button type="submit" :disabled="!confirmed"
                class="bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-2.5 px-6 rounded-xl text-xs transition shadow">
          Proceed with Database Restore
        </button>
      </div>
    </form>
  </div>

  <!-- Automated Scheduled Backup Guide Card -->
  <div class="bg-slate-900 text-white rounded-2xl p-6 sm:p-8 space-y-4 shadow-sm">
    <div class="flex items-center space-x-2">
      <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <h3 class="font-bold text-base">Automated Daily Backups via Hosting Cron</h3>
    </div>
    <p class="text-xs text-slate-400 leading-relaxed">
      Smart Khata includes a standalone CLI backup script that can be triggered automatically by your hosting cron job (cPanel, Plesk, Linux crontab) without keeping a browser open.
    </p>

    <div class="p-3 bg-slate-950 rounded-xl font-mono text-xs text-emerald-400 overflow-x-auto border border-slate-800">
      0 2 * * * /usr/bin/php <?= dirname(SK_ROOT) ?>/cli/backup.php > /dev/null 2>&1
    </div>
    <p class="text-[11px] text-slate-400">
      This schedule runs every night at 2:00 AM Asia/Kolkata time and automatically stores daily SQL snapshots.
    </p>
  </div>
</div>

<?php
$content = ob_get_clean();
require SK_APP . '/Views/layouts/main.php';
?>
