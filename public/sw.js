/**
 * Smart Khata - Secure Service Worker
 * Only caches safe static assets (CSS, JS, icons) and provides offline fallback.
 * Strictly NEVER caches financial data, invoices, or authenticated responses.
 */

const CACHE_NAME = 'smart-khata-static-v1.1.0';
const STATIC_ASSETS = [
  '/offline.html',
  '/manifest.webmanifest',
  '/favicon.svg',
  '/icon.svg',
  '/pwa-192x192.png',
  '/pwa-512x512.png',
  '/apple-touch-icon.png',
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/assets/js/alpine.min.js'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    }).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // Never intercept or cache non-GET requests (POST, PUT, DELETE for transactions)
  if (request.method !== 'GET') {
    return;
  }

  // Never cache sensitive API routes or download routes
  if (
    url.pathname.includes('/api/') ||
    url.pathname.includes('/pdf') ||
    url.pathname.includes('/backup/download') ||
    url.pathname.includes('/logout')
  ) {
    return;
  }

  // For HTML navigation requests: Network First, fallback to offline.html
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => {
        return caches.match('/offline.html');
      })
    );
    return;
  }

  // For static assets: Cache First, then Network
  if (
    url.pathname.startsWith('/assets/') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.webmanifest')
  ) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          // Return cache and revalidate in background
          fetch(request).then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
            }
          }).catch(() => {});
          return cachedResponse;
        }
        return fetch(request);
      })
    );
    return;
  }

  // Default network pass-through
  event.respondWith(fetch(request));
});
