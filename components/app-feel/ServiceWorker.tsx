"use client";

import { useEffect } from "react";

/**
 * Регистрация сервис-воркера (ADR docs/adr/0001-pwa-service-worker.md).
 * NEXT_PUBLIC_SW=off — снимает воркер у всех посетителей при следующем заходе.
 */
export function ServiceWorker() {
  useEffect(() => {
    if (!("serviceWorker" in navigator) || location.hostname === "localhost" || location.hostname === "127.0.0.1") return;
    if (process.env.NEXT_PUBLIC_SW === "off") {
      navigator.serviceWorker.getRegistrations().then((rs) => rs.forEach((r) => r.unregister()));
      caches?.keys().then((ks) => ks.filter((k) => k.startsWith("v23-")).forEach((k) => caches.delete(k)));
      return;
    }
    const v = process.env.NEXT_PUBLIC_BUILD_ID ?? "dev";
    const register = () => navigator.serviceWorker.register(`/sw.js?v=${encodeURIComponent(v)}`).catch(() => {});
    if (document.readyState === "complete") register();
    else window.addEventListener("load", register, { once: true });
  }, []);
  return null;
}
