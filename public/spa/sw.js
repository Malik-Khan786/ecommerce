const CACHE = 'huzaifa-mobile-v2'
const STATIC = ['/', '/manifest.json']

self.addEventListener('install', e => {
    e.waitUntil(caches.open(CACHE).then(c => c.addAll(STATIC)).then(() => self.skipWaiting()))
})

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    )
})

self.addEventListener('fetch', e => {
    const url = e.request.url

    // Only handle http/https — skip chrome-extension, data, blob etc.
    if (!url.startsWith('http://') && !url.startsWith('https://')) return

    const path = new URL(url).pathname

    // Never cache API, admin, or spa asset requests (they have hashes)
    if (path.startsWith('/api/') || path.startsWith('/admin') || path.startsWith('/spa/')) return

    e.respondWith(
        caches.match(e.request).then(cached => {
            if (cached) return cached
            return fetch(e.request).then(response => {
                // Only cache valid same-origin basic responses
                if (!response || response.status !== 200 || response.type !== 'basic') return response
                const clone = response.clone()
                caches.open(CACHE).then(c => c.put(e.request, clone))
                return response
            }).catch(() => caches.match('/'))
        })
    )
})
