const CACHE_NAME = 'jja-lab-v1';
const OFFLINE_URL = '/offline';

const ASSETS_TO_CACHE = [
    OFFLINE_URL,
    '/styles/app.css',
    // Fonts and other critical assets will be added here or discovered dynamically
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    // Standard navigation or asset request
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request).catch(() => {
                // If the fetch fails (offline) and it's a navigation request, show offline page
                if (event.request.mode === 'navigate') {
                    return caches.match(OFFLINE_URL);
                }
                return null;
            });
        })
    );
});
