/* Cryptocoinex service worker — offline fallback + light asset caching.
 * Paths are RELATIVE to the worker's scope so it works both under a
 * subdirectory (e.g. /cryptocoinex/) and at the domain root (production). */
const CACHE = 'cryptocoinex-v2';
const OFFLINE_URL = new URL('offline.html', self.registration.scope).href;
const PRECACHE = [
  OFFLINE_URL,
  new URL('css/tokens.css', self.registration.scope).href,
  new URL('images/logo-square.png', self.registration.scope).href,
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE)
      // Best-effort: a single missing asset must not abort the whole install.
      .then((cache) => Promise.allSettled(PRECACHE.map((u) => cache.add(u))))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  // Navigation requests: network-first, fall back to the offline page.
  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  // Static assets: cache-first.
  if (/\.(css|js|png|jpg|jpeg|svg|woff2?|ttf)$/.test(new URL(req.url).pathname)) {
    event.respondWith(
      caches.match(req).then((cached) => cached || fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE).then((cache) => cache.put(req, copy));
        return res;
      }).catch(() => cached))
    );
  }
});
