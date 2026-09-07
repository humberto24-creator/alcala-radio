const CACHE_NAME = 'alcala-radio-v1';

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
  if (event.request.url.includes('stream.alcalafiesta.com')) {
    event.respondWith(fetch(event.request));
  }
});
