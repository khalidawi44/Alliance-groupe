// AG Anti-Spam — service worker (hors-ligne)
var CACHE = 'ag-antispam-2026-10-28-r2';
var ASSETS = ['analyse-numero.html','analyse-numero.manifest.json','ag-icon-192.png','ag-icon-512.png'];
self.addEventListener('install', function(e){
  e.waitUntil(caches.open(CACHE).then(function(c){return c.addAll(ASSETS);}).then(function(){return self.skipWaiting();}));
});
self.addEventListener('activate', function(e){
  e.waitUntil(caches.keys().then(function(ks){
    return Promise.all(ks.map(function(k){ if(k!==CACHE) return caches.delete(k); }));
  }).then(function(){return self.clients.claim();}));
});
self.addEventListener('fetch', function(e){
  e.respondWith(caches.match(e.request).then(function(r){ return r || fetch(e.request); }));
});
