/* ВАН23АВТО — сервис-воркер без библиотек (ADR 0001).
 * Статика с хэшем и медиа — cache-first; HTML — network-first с офлайн-страницей.
 * Не кешируем: чужие домены (API, админка, Метрика), не-GET запросы. Кеш по версии сборки, старые удаляются. */
const VERSION = new URL(self.location.href).searchParams.get("v") || "dev";
const STATIC = `v23-static-${VERSION}`;
const PAGES = `v23-pages-${VERSION}`;
const OFFLINE = "/offline/";

self.addEventListener("install", (e) => {
  e.waitUntil(caches.open(PAGES).then((c) => c.addAll([OFFLINE, "/"])).catch(() => {}).then(() => self.skipWaiting()));
});

self.addEventListener("activate", (e) => {
  e.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((k) => k.startsWith("v23-") && k !== STATIC && k !== PAGES).map((k) => caches.delete(k))))
      .then(() => self.clients.claim()),
  );
});

self.addEventListener("fetch", (e) => {
  const req = e.request;
  if (req.method !== "GET") return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  const isStatic = url.pathname.startsWith("/_next/static/") || url.pathname.startsWith("/media/") || url.pathname.startsWith("/brand/") || url.pathname.startsWith("/fonts/");
  if (isStatic) {
    e.respondWith(
      caches.open(STATIC).then((c) =>
        c.match(req).then((hit) => hit || fetch(req).then((res) => { if (res.ok) c.put(req, res.clone()); return res; })),
      ),
    );
    return;
  }

  if (req.mode === "navigate" || (req.headers.get("accept") || "").includes("text/html")) {
    e.respondWith(
      fetch(req)
        .then((res) => { if (res.ok) caches.open(PAGES).then((c) => c.put(req, res.clone())); return res; })
        .catch(() => caches.match(req).then((hit) => hit || caches.match(OFFLINE))),
    );
  }
});
