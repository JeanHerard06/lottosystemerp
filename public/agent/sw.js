const CACHE='lotto-agent-v1';
const ASSETS=['./','index.html','dashboard.html','new_fiche.html','app.js','manifest.json'];
self.addEventListener('install',e=>e.waitUntil(caches.open(CACHE).then(c=>c.addAll(ASSETS))));
self.addEventListener('fetch',e=>e.respondWith(caches.match(e.request).then(r=>r||fetch(e.request))));
