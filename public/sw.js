const CACHE_NAME = 'pmd-sragen-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    OFFLINE_URL,
    '/css/main.css',
    '/css/landing.css',
    '/css/pendataan.css',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/apple-touch-icon.png',
    '/favicon.ico'
];

// Install Event - Pre-cache essential assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('Pre-caching partial error:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate Event - Clean up outdated caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Only handle GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Navigation requests (HTML pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => {
                const cache = await caches.open(CACHE_NAME);
                const cachedResponse = await cache.match(request);
                if (cachedResponse) {
                    return cachedResponse;
                }
                const offlineFallback = await cache.match(OFFLINE_URL);
                return offlineFallback || new Response('Offline', { status: 503, statusText: 'Offline' });
            })
        );
        return;
    }

    // Static Assets (CSS, JS, Fonts, Images)
    const url = new URL(request.url);
    const isStaticAsset = (
        url.pathname.startsWith('/css/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname.startsWith('/icons/') ||
        url.hostname.includes('cdn.jsdelivr.net') ||
        url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com') ||
        url.hostname.includes('cdnjs.cloudflare.com')
    );

    if (isStaticAsset) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return networkResponse;
                }).catch(() => cachedResponse);

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // Default fallback: Network with cache fallback
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
