/**
 * Smart Khata - Client Application Controller
 * Handles Service Worker, PWA installation (Android & iOS Safari),
 * currency formatting, and dynamic Alpine.js reactive components.
 */

window.SmartKhata = {
  formatINR(paise) {
    if (paise === undefined || paise === null || isNaN(paise)) return '₹0.00';
    const rupees = (Number(paise) / 100).toFixed(2);
    const parts = rupees.split('.');
    let integerPart = parts[0];
    const decimalPart = parts[1];

    const isNegative = integerPart.startsWith('-');
    if (isNegative) integerPart = integerPart.substring(1);

    // Indian currency comma grouping: last 3 digits, then every 2 digits
    let lastThree = integerPart.substring(integerPart.length - 3);
    const otherNumbers = integerPart.substring(0, integerPart.length - 3);
    if (otherNumbers !== '') {
      lastThree = ',' + lastThree;
    }
    const formattedInteger = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree;

    return (isNegative ? '-₹' : '₹') + formattedInteger + '.' + decimalPart;
  },

  // Service Worker Registration
  initPWA() {
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then((reg) => {
          console.log('[SmartKhata] Service Worker registered with scope:', reg.scope);
        }).catch((err) => {
          console.warn('[SmartKhata] Service Worker registration failed:', err);
        });
      });
    }
  }
};

// Auto initialize PWA service worker
window.SmartKhata.initPWA();

// Alpine component for PWA installation handling
document.addEventListener('alpine:init', () => {
  Alpine.data('pwaInstaller', () => ({
    deferredPrompt: null,
    isInstallable: false,
    isInstalled: false,
    isIOS: false,
    showIOSGuide: false,

    init() {
      // Check if running in standalone mode (already installed)
      const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;
      this.isInstalled = isStandalone;

      // Detect iOS Safari
      const ua = window.navigator.userAgent.toLowerCase();
      this.isIOS = /iphone|ipad|ipod/.test(ua) && !window.MSStream;

      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        this.deferredPrompt = e;
        this.isInstallable = true;
      });

      window.addEventListener('appinstalled', () => {
        this.isInstalled = true;
        this.isInstallable = false;
        this.deferredPrompt = null;
        console.log('[SmartKhata] App was installed successfully');
      });
    },

    async triggerInstall() {
      if (!this.deferredPrompt) return;
      this.deferredPrompt.prompt();
      const { outcome } = await this.deferredPrompt.userChoice;
      if (outcome === 'accepted') {
        this.isInstalled = true;
        this.isInstallable = false;
        this.deferredPrompt = null;
      }
    }
  }));
});
