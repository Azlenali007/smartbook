<?php
/**
 * Smart Khata - Admin Login View
 */
$settings = getSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Admin Login — Smart Khata</title>
  <link rel="manifest" href="/manifest.webmanifest">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">

  <div class="max-w-md w-full bg-slate-800/90 backdrop-blur-md rounded-2xl shadow-2xl p-8 border border-slate-700">
    <div class="text-center mb-8">
      <div class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center font-bold text-white text-3xl mx-auto mb-4 shadow-lg shadow-blue-600/30">
        ₹
      </div>
      <h1 class="text-2xl font-bold tracking-tight text-white"><?= e($settings['shop_name'] ?? 'Smart Khata') ?></h1>
      <p class="text-slate-400 text-xs mt-1">Single-Admin Accounting & Udhaar System</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="mb-6 p-4 rounded-xl bg-rose-950/60 border border-rose-800 text-rose-300 text-xs leading-relaxed flex items-start space-x-2">
        <svg class="w-4 h-4 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <form action="/login" method="POST" class="space-y-5">
      <?= \SmartKhata\CSRF::getField() ?>

      <div>
        <label for="username" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Admin Username</label>
        <input type="text" id="username" name="username" required autofocus
               class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="Enter admin username">
      </div>

      <div>
        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
        <input type="password" id="password" name="password" required
               class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
               placeholder="••••••••">
      </div>

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-xl text-sm transition shadow-lg shadow-blue-600/30 flex items-center justify-center space-x-2">
        <span>Sign In to Dashboard</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
      </button>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-700/80 text-center text-[11px] text-slate-500">
      Private System &bull; Single Administrator Only &bull; Secured with Argon2/Bcrypt
    </div>
  </div>

</body>
</html>
