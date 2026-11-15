"use client";

import { useSyncExternalStore } from "react";

/** SSR-безопасный matchMedia: на сервере false, на клиенте — живое значение. */
export function useMedia(query: string) {
  return useSyncExternalStore(
    (cb) => {
      const m = window.matchMedia(query);
      m.addEventListener("change", cb);
      return () => m.removeEventListener("change", cb);
    },
    () => window.matchMedia(query).matches,
    () => false,
  );
}
