<?php
/**
 * Smart Khata - Main Application Responsive Layout
 */
$settings = getSettings();
$currentRoute = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$admin = \SmartKhata\Auth::user();
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title><?= e($pageTitle ?? 'Smart Khata — Accounting & Ledger') ?></title>
  
  <!-- PWA Meta -->
  <meta name="theme-color" content="#0f172a">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Smart Khata">
  <link rel="manifest" href="/manifest.webmanifest">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/assets/css/app.css">
  <!-- Alpine.js & Custom Scripts -->
  <script src="/assets/js/alpine.min.js" defer></script>
  <script src="/assets/js/app.js" defer></script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col" x-data="pwaInstaller">

  <!-- Mobile Top Bar -->
  <header class="lg:hidden bg-slate-900 text-white px-4 py-3 flex items-center justify-between sticky top-0 z-40 shadow-sm safe-pt">
    <div class="flex items-center space-x-3">
      <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-bold text-white shadow-inner">₹</div>
      <div>
        <h1 class="font-bold text-base tracking-tight leading-tight"><?= e($settings['shop_name'] ?? 'Smart Khata') ?></h1>
        <p class="text-[10px] text-slate-400">Ledger & Billing</p>
      </div>
    </div>
    <div class="flex items-center space-x-2">
      <!-- PWA Install Prompt Mobile Button -->
      <button x-show="isInstallable && !isInstalled" @click="triggerInstall()" class="bg-blue-600 text-xs px-2.5 py-1 rounded-md text-white font-medium flex items-center space-x-1 shadow">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        <span>Install</span>
      </button>
      <button x-show="isIOS && !isInstalled" @click="showIOSGuide = true" class="bg-slate-800 border border-slate-700 text-xs px-2.5 py-1 rounded-md text-slate-200 font-medium">
        <span>+ App</span>
      </button>
      <a href="/logout" class="text-slate-400 hover:text-white p-1">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      </a>
    </div>
  </header>

  <div class="flex-1 flex overflow-hidden">
    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex flex-col w-64 bg-slate-900 text-slate-300 flex-shrink-0 border-r border-slate-800">
      <!-- Brand -->
      <div class="p-6 border-b border-slate-800 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-white text-xl shadow-lg">₹</div>
          <div>
            <h2 class="font-bold text-white text-lg tracking-tight"><?= e($settings['shop_name'] ?? 'Smart Khata') ?></h2>
            <span class="inline-block text-[11px] px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-800/50">Pro Accounting</span>
          </div>
        </div>
      </div>

      <!-- Quick Action Buttons -->
      <div class="px-4 pt-4 pb-2 space-y-2">
        <a href="/invoices/create" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-3 rounded-lg text-sm flex items-center justify-center space-x-2 transition shadow">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>New Sale / Invoice</span>
        </a>
        <a href="/payments/create" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-3 rounded-lg text-sm flex items-center justify-center space-x-2 transition shadow">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span>Receive Payment</span>
        </a>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-4 py-3 space-y-1 overflow-y-auto text-sm">
        <a href="/dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-medium transition <?= ($currentRoute === '/dashboard' || $currentRoute === '/') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          <span>Dashboard</span>
        </a>

        <a href="/customers" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-medium transition <?= str_starts_with($currentRoute, '/customers') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <span>Customers & Udhaar</span>
        </a>

        <a href="/invoices" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-medium transition <?= str_starts_with($currentRoute, '/invoices') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          <span>Invoices & Billing</span>
        </a>

        <a href="/payments" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-medium transition <?= str_starts_with($currentRoute, '/payments') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <span>Payments Received</span>
        </a>

        <a href="/reports" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-medium transition <?= str_starts_with($currentRoute, '/reports') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          <span>Reports & Audit</span>
        </a>

        <a href="/backup" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-medium transition <?= str_starts_with($currentRoute, '/backup') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
          <span>Backup & Restore</span>
        </a>

        <a href="/settings" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg font-medium transition <?= str_starts_with($currentRoute, '/settings') ? 'bg-blue-600 text-white' : 'hover:bg-slate-800 hover:text-white' ?>">
          <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <span>Settings</span>
        </a>
      </nav>

      <!-- Desktop PWA Install Card & Admin Profile -->
      <div class="p-4 border-t border-slate-800 space-y-3">
        <!-- PWA In-App Banner Desktop -->
        <div x-show="isInstallable && !isInstalled" class="p-3 bg-slate-800 rounded-xl border border-slate-700">
          <div class="text-xs font-semibold text-white mb-1">Install Smart Khata App</div>
          <p class="text-[11px] text-slate-400 mb-2">Fast, standalone access on your desktop or Chromebook.</p>
          <button @click="triggerInstall()" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs py-1.5 px-3 rounded-lg font-medium transition shadow">
            Install Desktop App
          </button>
        </div>

        <div class="flex items-center justify-between text-xs text-slate-400">
          <div>
            <div class="text-white font-medium"><?= e($admin['username'] ?? 'Admin') ?></div>
            <div>Single Administrator</div>
          </div>
          <a href="/logout" title="Sign Out" class="p-2 rounded-lg bg-slate-800 hover:bg-red-900/50 hover:text-red-300 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          </a>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto pb-20 lg:pb-8">
      <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">

        <!-- Flash Messages -->
        <?php if ($flashSuccess): ?>
          <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span class="text-sm font-medium"><?= e($flashSuccess) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
          </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
          <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="text-sm font-medium"><?= e($flashError) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">&times;</button>
          </div>
        <?php endif; ?>

        <!-- Dynamic Content Slot -->
        <?= $content ?? '' ?>

      </div>
    </main>
  </div>

  <!-- Mobile Bottom Tab Navigation -->
  <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 flex items-center justify-around py-2 z-40 safe-pb shadow-lg">
    <a href="/dashboard" class="flex flex-col items-center py-1 px-3 text-xs <?= ($currentRoute === '/dashboard' || $currentRoute === '/') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
      <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      <span>Dashboard</span>
    </a>

    <a href="/customers" class="flex flex-col items-center py-1 px-3 text-xs <?= str_starts_with($currentRoute, '/customers') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
      <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      <span>Customers</span>
    </a>

    <!-- Quick Floating Action -->
    <a href="/invoices/create" class="flex flex-col items-center -mt-6">
      <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg border-2 border-white hover:bg-blue-700 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      </div>
      <span class="text-[10px] mt-1 font-medium text-slate-700">Sale</span>
    </a>

    <a href="/payments" class="flex flex-col items-center py-1 px-3 text-xs <?= str_starts_with($currentRoute, '/payments') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
      <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      <span>Payments</span>
    </a>

    <a href="/reports" class="flex flex-col items-center py-1 px-3 text-xs <?= str_starts_with($currentRoute, '/reports') ? 'text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800' ?>">
      <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
      <span>Reports</span>
    </a>
  </nav>

  <!-- iOS Safari "Add to Home Screen" Instruction Modal -->
  <div x-show="showIOSGuide" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl border border-slate-200" @click.away="showIOSGuide = false">
      <div class="flex items-center space-x-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold">₹</div>
        <div>
          <h3 class="text-base font-bold text-slate-900">Install Smart Khata on iPhone</h3>
          <p class="text-xs text-slate-500">Add to your Home Screen</p>
        </div>
      </div>
      <div class="space-y-3 text-xs text-slate-700 mb-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
        <div class="flex items-start space-x-2">
          <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[11px] flex-shrink-0">1</span>
          <span>Tap the <strong class="font-semibold text-slate-900">Share</strong> button (box with an arrow pointing up) at the bottom of Safari.</span>
        </div>
        <div class="flex items-start space-x-2">
          <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[11px] flex-shrink-0">2</span>
          <span>Scroll down and tap <strong class="font-semibold text-slate-900">Add to Home Screen</strong>.</span>
        </div>
        <div class="flex items-start space-x-2">
          <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[11px] flex-shrink-0">3</span>
          <span>Tap <strong class="font-semibold text-slate-900">Add</strong> at top right. Smart Khata will appear like a native app!</span>
        </div>
      </div>
      <button @click="showIOSGuide = false" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 rounded-xl text-xs transition">
        Got it!
      </button>
    </div>
  </div>

</body>
</html>
