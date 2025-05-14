// Service Worker for Note Management App
const CACHE_NAME = 'note-app-v1';
const DYNAMIC_CACHE = 'note-app-dynamic-v1';

// Resources to cache on install
const STATIC_RESOURCES = [
  '/',
  '/assets/css/main.css',
  '/assets/css/notes.css',
  '/assets/css/auth.css',
  '/assets/js/main.js',
  '/assets/js/notes.js',
  '/assets/js/labels.js',
  '/assets/js/offline.js',
  '/offline',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Install event - cache static assets
self.addEventListener('install', event => {
  console.log('[Service Worker] Installing Service Worker...');
  
  // Skip waiting to activate immediately
  self.skipWaiting();
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[Service Worker] Pre-caching app shell');
        return cache.addAll(STATIC_RESOURCES.map(url => new Request(url, {credentials: 'same-origin'})));
      })
      .catch(error => {
        console.error('[Service Worker] Pre-caching failed:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('[Service Worker] Activating Service Worker...');
  
  // Take control immediately
  self.clients.claim();
  
  event.waitUntil(
    caches.keys()
      .then(keyList => {
        return Promise.all(keyList.map(key => {
          if (key !== CACHE_NAME && key !== DYNAMIC_CACHE) {
            console.log('[Service Worker] Removing old cache:', key);
            return caches.delete(key);
          }
        }));
      })
  );
  
  return self.clients.claim();
});

// Helper function to determine if a request should be cached
function isRequestCacheable(request) {
  const url = new URL(request.url);
  
  // Don't cache API requests that modify data
  if (url.pathname.includes('/api/') && request.method !== 'GET') {
    return false;
  }
  
  // Don't cache authentication requests
  if (url.pathname.includes('/login') || 
      url.pathname.includes('/register') || 
      url.pathname.includes('/logout')) {
    return false;
  }
  
  return true;
}

// Helper function to determine if a request is for an API
function isApiRequest(request) {
  return request.url.includes('/api/');
}

// Fetch event - serve from cache or network
self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);
  
  // Skip non-GET requests and cross-origin requests
  if (request.method !== 'GET' || url.origin !== self.location.origin) {
    return;
  }
  
  // Handle API requests specially
  if (isApiRequest(request)) {
    event.respondWith(
      fetch(request)
        .then(response => {
          // Clone the response for caching
          const clonedResponse = response.clone();
          
          // Only cache successful responses
          if (response.ok && isRequestCacheable(request)) {
            caches.open(DYNAMIC_CACHE)
              .then(cache => cache.put(request, clonedResponse));
          }
          
          return response;
        })
        .catch(error => {
          console.log('[Service Worker] API fetch failed, trying cache', error);
          return caches.match(request)
            .then(cachedResponse => {
              if (cachedResponse) {
                return cachedResponse;
              }
              
              // If we're offline and there's no cached data, get from IndexedDB
              return new Response(
                JSON.stringify({ 
                  success: false, 
                  offline: true,
                  message: 'You are currently offline' 
                }),
                { headers: { 'Content-Type': 'application/json' } }
              );
            });
        })
    );
    return;
  }
  
  // Network-first strategy for HTML pages
  if (request.headers.get('accept').includes('text/html')) {
    event.respondWith(
      fetch(request)
        .then(response => {
          // Clone the response for caching
          const clonedResponse = response.clone();
          
          caches.open(DYNAMIC_CACHE)
            .then(cache => cache.put(request, clonedResponse));
          
          return response;
        })
        .catch(error => {
          console.log('[Service Worker] HTML fetch failed, serving from cache', error);
          
          return caches.match(request)
            .then(cachedResponse => {
              if (cachedResponse) {
                return cachedResponse;
              }
              
              // If no cached version of the page, show offline page
              return caches.match('/offline');
            });
        })
    );
    return;
  }
  
  // Cache-first strategy for static assets
  event.respondWith(
    caches.match(request)
      .then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }
        
        // If not in cache, fetch from network
        return fetch(request)
          .then(response => {
            // Clone the response for caching
            const clonedResponse = response.clone();
            
            // Cache the fetched response
            caches.open(DYNAMIC_CACHE)
              .then(cache => cache.put(request, clonedResponse));
            
            return response;
          });
      })
  );
});

// Listen for sync events (background sync)
self.addEventListener('sync', event => {
  console.log('[Service Worker] Background Sync', event.tag);
  
  if (event.tag === 'sync-notes') {
    event.waitUntil(
      // This will be handled by the main thread
      self.clients.matchAll()
        .then(clients => {
          if (clients && clients.length > 0) {
            clients[0].postMessage({
              type: 'sync-notes'
            });
          }
        })
    );
  }
});

// Listen for push notifications
self.addEventListener('push', event => {
  console.log('[Service Worker] Push Received', event);
  
  const data = event.data.json();
  
  const options = {
    body: data.body,
    icon: '/assets/img/icon-192x192.png',
    badge: '/assets/img/badge-72x72.png',
    data: {
      url: data.url
    }
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
  console.log('[Service Worker] Notification click', event);
  
  event.notification.close();
  
  if (event.notification.data && event.notification.data.url) {
    event.waitUntil(
      clients.openWindow(event.notification.data.url)
    );
  }
});