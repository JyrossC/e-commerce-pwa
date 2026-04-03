/**
 * NovaMart Service Worker
 * Cache-first for static assets, Network-first for API calls & Pages
 */

const CACHE_NAME    = 'novamart-v1.0.2';
const STATIC_ASSETS = [
  '/ecommerce-pwa/',
  '/ecommerce-pwa/pages/shop.php',
  '/ecommerce-pwa/pages/login.php',
  '/ecommerce-pwa/assets/css/style.css',
  '/ecommerce-pwa/assets/js/app.js',
  '/ecommerce-pwa/assets/js/pwa.js',
  '/ecommerce-pwa/offline.html',
];

// Install — cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS)).then(() => self.skipWaiting())
  );
});

// Activate — clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

// Fetch — strategy selection
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET or cross-origin requests
  if (request.method !== 'GET' || url.origin !== location.origin) return;

  // 1. API calls → network-first
  if (url.pathname.includes('/api/')) {
    event.respondWith(networkFirst(request));
    return;
  }

  // 2. Pages & HTML documents → network-first (to ensure fresh CSRF tokens)
  if (url.pathname.endsWith('.php') || url.pathname.endsWith('/') || !url.pathname.includes('.')) {
    event.respondWith(networkFirst(request));
    return;
  }

  // 3. Static assets → cache-first
  if (url.pathname.match(/\.(css|js|png|jpg|jpeg|webp|gif|svg|woff2?)$/i)) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Default: Network first
  event.respondWith(networkFirst(request));
});

// ─── Strategies ──────────────────────────────────────────────────

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    return caches.match('/ecommerce-pwa/offline.html');
  }
}

async function networkFirst(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    const cached = await caches.match(request);
    return cached || new Response(JSON.stringify({ success: false, message: 'Offline' }), {
      headers: { 'Content-Type': 'application/json' },
    });
  }
}

async function staleWhileRevalidate(request) {
  const cache  = await caches.open(CACHE_NAME);
  const cached = await cache.match(request);

  const fetchPromise = fetch(request).then((response) => {
    if (response.ok) cache.put(request, response.clone());
    return response;
  }).catch(() => null);

  return cached || fetchPromise || caches.match('/ecommerce-pwa/offline.html');
}
