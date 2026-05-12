const AGRITWIN_CACHE = "agritwin-hmi-v1";
const APP_SHELL = [
  "index.php",
  "assets/hmi.css",
  "assets/hmi.js",
  "manifest.webmanifest",
  "AgriTwin_logo.png",
  "OpenAgri.png",
  "eufunded.png"
];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(AGRITWIN_CACHE).then((cache) => cache.addAll(APP_SHELL))
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((key) => key !== AGRITWIN_CACHE).map((key) => caches.delete(key))
    ))
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  const request = event.request;
  if (request.method !== "GET") {
    return;
  }

  event.respondWith(
    fetch(request)
      .then((response) => {
        const copy = response.clone();
        caches.open(AGRITWIN_CACHE).then((cache) => cache.put(request, copy));
        return response;
      })
      .catch(() => caches.match(request).then((cached) => cached || caches.match("index.php")))
  );
});
