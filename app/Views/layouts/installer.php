<?php
/**
 * Smart Khata - Web Installer Wizard Layout
 */
$installerError = $_SESSION['installer_error'] ?? null;
unset($_SESSION['installer_error']);
$currentStep = (int)($_GET['step'] ?? 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Installation Wizard — Smart Khata</title>
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="stylesheet" href="/assets/css/app.css">
  <script src="/assets/js/alpine.min.js" defer></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex flex-col justify-between p-4 sm:p-6 font-sans antialiased">

  <!-- Header -->
  <header class="max-w-2xl mx-auto w-full pt-4 pb-6 text-center">
    <div class="flex items-center justify-center space-x-3 mb-2">
      <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold text-xl flex items-center justify-center shadow-lg shadow-blue-600/30">₹</div>
      <h1 class="text-2xl font-black text-white tracking-tight">Smart Khata</h1>
    </div>
    <p class="text-xs text-slate-400">Complete Accounting Software &bull; Browser-Based Web Installer</p>

    <!-- Stepper indicator -->
    <div class="mt-6 flex items-center justify-center space-x-2 sm:space-x-4">
      <?php
      $steps = [
        1 => 'Welcome',
        2 => 'Server Check',
        3 => 'Database',
        4 => 'Install',
        5 => 'Admin',
        6 => 'Business',
        7 => 'Finish'
      ];
      foreach ($steps as $sNum => $sLabel):
        $isActive = ($sNum === $currentStep);
        $isPast = ($sNum < $currentStep);
      ?>
        <div class="flex items-center space-x-1.5">
          <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition
            <?= $isActive ? 'bg-blue-600 text-white ring-4 ring-blue-500/30' : ($isPast ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400') ?>">
            <?= $isPast ? '✓' : $sNum ?>
          </div>
          <span class="hidden md:inline text-[11px] <?= $isActive ? 'text-white font-bold' : 'text-slate-500' ?>"><?= $sLabel ?></span>
        </div>
        <?php if ($sNum < 7): ?>
          <div class="hidden sm:block w-3 h-0.5 <?= $isPast ? 'bg-emerald-600' : 'bg-slate-800' ?>"></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </header>

  <!-- Main Step Card -->
  <main class="max-w-2xl mx-auto w-full flex-1">
    <?php if ($installerError): ?>
      <div class="mb-6 p-4 rounded-xl bg-rose-950/80 border border-rose-800 text-rose-300 text-xs flex items-start space-x-2">
        <svg class="w-4 h-4 text-rose-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span><?= e($installerError) ?></span>
      </div>
    <?php endif; ?>

    <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-2xl p-6 sm:p-10">
      <?= $installerContent ?? '' ?>
    </div>
  </main>

  <!-- Footer -->
  <footer class="max-w-2xl mx-auto w-full text-center py-6 text-xs text-slate-500">
    Smart Khata Setup Wizard &bull; Production Ready Installation System
  </footer>

</body>
</html>
