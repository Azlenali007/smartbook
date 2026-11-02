<?php
/**
 * Smart Khata - Public Landing Page
 * Clean, modern, responsive landing page for visitors and admin access.
 * ZERO TypeScript - Plain HTML, CSS, Alpine.js & Vanilla JavaScript.
 */
$shopName = $settings['shop_name'] ?? 'Smart Khata';
$currency = $settings['currency_symbol'] ?? '₹';
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= e($shopName) ?> — Smart Accounting & Customer Ledger System</title>
  <meta name="description" content="Complete digital udhaar and customer ledger management, invoicing, smart payment allocation, and financial tracking for retail and businesses.">
  <link rel="manifest" href="/manifest.webmanifest">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="stylesheet" href="/assets/css/app.css">
  <script defer src="/assets/js/alpine.min.js"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">

  <!-- Top Navigation Bar -->
  <header class="border-b border-slate-800/80 bg-slate-900/80 backdrop-blur-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <!-- Brand Logo -->
      <a href="/" class="flex items-center space-x-3 group">
        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-white text-xl shadow-lg shadow-blue-600/30 group-hover:bg-blue-500 transition">
          <?= e($currency) ?>
        </div>
        <div>
          <span class="text-lg font-bold tracking-tight text-white block leading-none"><?= e($shopName) ?></span>
          <span class="text-[10px] tracking-wider uppercase text-blue-400 font-semibold">Ledger & Invoicing</span>
        </div>
      </a>

      <!-- Navigation & Action Buttons -->
      <div class="flex items-center space-x-3">
        <!-- Install PWA Button (Hidden by default, shown when deferredPrompt is available) -->
        <button id="pwaInstallBtn" type="button" class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 border border-slate-700 transition">
          <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          <span>Install App</span>
        </button>

        <?php if (!empty($isLoggedIn)): ?>
          <a href="/dashboard" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-sm font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Go to Dashboard</span>
          </a>
        <?php else: ?>
          <a href="/login" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-600/30 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            <span>Admin Login</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <main class="flex-grow">
    <section class="relative overflow-hidden py-16 sm:py-24 border-b border-slate-800/60 bg-gradient-to-b from-slate-900/60 via-slate-950 to-slate-950">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <!-- Pill Badge -->
        <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-blue-950/80 border border-blue-800/80 text-blue-300 text-xs font-medium mb-6 shadow-sm">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>Fast, Reliable & Secure Ledger System</span>
        </div>

        <!-- Main Title -->
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white max-w-4xl mx-auto leading-tight sm:leading-tight">
          Modern Accounting &amp; Customer Udhaar Management
        </h1>

        <!-- Subtitle / Software Description -->
        <p class="mt-6 text-base sm:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
          Smart Khata simplifies credit tracking, automatic FIFO payment allocation, professional billing, and customer ledger reconciliation for retail shops and businesses.
        </p>

        <!-- CTA Action Buttons -->
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
          <?php if (!empty($isLoggedIn)): ?>
            <a href="/dashboard" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-semibold text-base bg-emerald-600 hover:bg-emerald-500 text-white shadow-xl shadow-emerald-600/25 transition flex items-center justify-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
              </svg>
              <span>Open Admin Dashboard</span>
            </a>
          <?php else: ?>
            <a href="/login" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-semibold text-base bg-blue-600 hover:bg-blue-500 text-white shadow-xl shadow-blue-600/25 transition flex items-center justify-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
              </svg>
              <span>Admin Login</span>
            </a>
          <?php endif; ?>

          <button id="heroInstallBtn" type="button" class="w-full sm:w-auto px-8 py-3.5 rounded-xl font-semibold text-base bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition flex items-center justify-center space-x-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span>Install on Mobile</span>
          </button>
        </div>

        <!-- Trust Badges -->
        <div class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl mx-auto text-xs text-slate-400">
          <div class="p-3 rounded-lg bg-slate-900/60 border border-slate-800/80">
            <span class="block text-white font-bold text-sm">100% Private</span>
            <span>Self-contained local data</span>
          </div>
          <div class="p-3 rounded-lg bg-slate-900/60 border border-slate-800/80">
            <span class="block text-white font-bold text-sm">Integer Paise Math</span>
            <span>Zero rounding errors</span>
          </div>
          <div class="p-3 rounded-lg bg-slate-900/60 border border-slate-800/80">
            <span class="block text-white font-bold text-sm">Auto Allocation</span>
            <span>FIFO debt settlement</span>
          </div>
          <div class="p-3 rounded-lg bg-slate-900/60 border border-slate-800/80">
            <span class="block text-white font-bold text-sm">PWA Offline</span>
            <span>Instant mobile access</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Features Grid -->
    <section class="py-16 sm:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-2xl mx-auto mb-16">
        <h2 class="text-xs font-semibold tracking-wider text-blue-400 uppercase">Core Capabilities</h2>
        <p class="mt-2 text-2xl sm:text-3xl font-bold tracking-tight text-white">Everything needed to run customer accounts</p>
        <p class="mt-3 text-slate-400 text-sm">Designed specifically for shop owners, traders, and small businesses managing credits and payments.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Feature 1: Customer Khata -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 hover:border-slate-700 transition">
          <div class="w-12 h-12 rounded-xl bg-blue-950 border border-blue-800/60 flex items-center justify-center text-blue-400 mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Customer Khata &amp; Ledgers</h3>
          <p class="text-slate-400 text-sm leading-relaxed">
            Maintain digital accounts for all your customers. Instantly view total credit balance, advances, transaction history, and generate balance statements.
          </p>
        </div>

        <!-- Feature 2: Invoicing & Billing -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 hover:border-slate-700 transition">
          <div class="w-12 h-12 rounded-xl bg-emerald-950 border border-emerald-800/60 flex items-center justify-center text-emerald-400 mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Invoicing &amp; Billing</h3>
          <p class="text-slate-400 text-sm leading-relaxed">
            Create professional invoices with custom line items, tax and discounts. Print thermal receipts (58mm/80mm) or A4 invoices, and share via WhatsApp.
          </p>
        </div>

        <!-- Feature 3: Smart Allocation -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 hover:border-slate-700 transition">
          <div class="w-12 h-12 rounded-xl bg-purple-950 border border-purple-800/60 flex items-center justify-center text-purple-400 mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Smart FIFO Allocation</h3>
          <p class="text-slate-400 text-sm leading-relaxed">
            Automated payment distribution settles oldest unpaid invoices first, or allows custom manual allocation with auto-calculated excess advances.
          </p>
        </div>

        <!-- Feature 4: Advance Balance Management -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 hover:border-slate-700 transition">
          <div class="w-12 h-12 rounded-xl bg-amber-950 border border-amber-800/60 flex items-center justify-center text-amber-400 mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Advance Balances</h3>
          <p class="text-slate-400 text-sm leading-relaxed">
            Seamlessly record advance cash payments from clients. Accumulated advances automatically settle against new invoices during billing.
          </p>
        </div>

        <!-- Feature 5: PWA & Mobile Ready -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 hover:border-slate-700 transition">
          <div class="w-12 h-12 rounded-xl bg-cyan-950 border border-cyan-800/60 flex items-center justify-center text-cyan-400 mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Installable PWA</h3>
          <p class="text-slate-400 text-sm leading-relaxed">
            Install directly on mobile devices without any app store. Full responsive UI tailored for fast one-handed mobile accounting.
          </p>
        </div>

        <!-- Feature 6: Safe Database Backups -->
        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-6 hover:border-slate-700 transition">
          <div class="w-12 h-12 rounded-xl bg-rose-950 border border-rose-800/60 flex items-center justify-center text-rose-400 mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
          </div>
          <h3 class="text-lg font-bold text-white mb-2">Automated Backups</h3>
          <p class="text-slate-400 text-sm leading-relaxed">
            One-click database export and restore functionality. Keep complete backups of your accounting records safe on your device at all times.
          </p>
        </div>
      </div>
    </section>

    <!-- Bottom Call-to-Action -->
    <section class="py-12 bg-slate-900/50 border-t border-slate-800/80">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold text-white mb-3">Ready to manage your shop's accounts?</h2>
        <p class="text-slate-400 text-sm mb-6 max-w-xl mx-auto">
          Secure, single-administrator access. Fast billing and zero calculation discrepancies.
        </p>
        <?php if (!empty($isLoggedIn)): ?>
          <a href="/dashboard" class="inline-flex items-center space-x-2 px-6 py-3 rounded-xl font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg transition">
            <span>Enter Admin Dashboard</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
        <?php else: ?>
          <a href="/login" class="inline-flex items-center space-x-2 px-6 py-3 rounded-xl font-semibold bg-blue-600 hover:bg-blue-500 text-white shadow-lg transition">
            <span>Login as Administrator</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="border-t border-slate-800/80 bg-slate-950 py-6 text-xs text-slate-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
      <div>
        <span>&copy; <?= date('Y') ?> <?= e($shopName) ?>. All rights reserved.</span>
      </div>
      <div class="flex items-center space-x-4">
        <span>Version 1.0.0</span>
        <span>•</span>
        <span>PWA Ready</span>
        <span>•</span>
        <a href="/login" class="text-blue-400 hover:underline">Admin Login</a>
      </div>
    </div>
  </footer>

  <!-- PWA Install Prompt Script (Vanilla JS) -->
  <script>
    let deferredPrompt = null;
    const pwaInstallBtn = document.getElementById('pwaInstallBtn');
    const heroInstallBtn = document.getElementById('heroInstallBtn');

    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      if (pwaInstallBtn) pwaInstallBtn.classList.remove('hidden');
    });

    function triggerInstall() {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
          if (choiceResult.outcome === 'accepted') {
            console.log('User accepted the PWA install prompt');
          }
          deferredPrompt = null;
          if (pwaInstallBtn) pwaInstallBtn.classList.add('hidden');
        });
      } else {
        alert('To install Smart Khata: tap "Add to Home screen" in your browser menu.');
      }
    }

    if (pwaInstallBtn) pwaInstallBtn.addEventListener('click', triggerInstall);
    if (heroInstallBtn) heroInstallBtn.addEventListener('click', triggerInstall);
  </script>
</body>
</html>
