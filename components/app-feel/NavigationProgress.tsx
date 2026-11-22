"use client";

import { usePathname, useSearchParams } from "next/navigation";
import { useEffect, useRef, useState } from "react";

/**
 * Полоса 2px сверху — только если переход дольше 150 мс (app-feel-web, правило 4).
 * Быстрые переходы не мигают. Старт — клик по внутренней ссылке, финиш — смена адреса.
 */
export function NavigationProgress() {
  const pathname = usePathname();
  const search = useSearchParams();
  const [state, setState] = useState<"idle" | "loading" | "done">("idle");
  const timer = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      const a = (e.target as Element | null)?.closest?.("a[href]") as HTMLAnchorElement | null;
      if (!a || a.target === "_blank" || a.hasAttribute("download")) return;
      const url = new URL(a.href, location.href);
      if (url.origin !== location.origin || (url.pathname === location.pathname && url.search === location.search)) return;
      if (timer.current) clearTimeout(timer.current);
      timer.current = setTimeout(() => setState("loading"), 150);
    };
    document.addEventListener("click", onClick, true);
    return () => document.removeEventListener("click", onClick, true);
  }, []);

  useEffect(() => {
    if (timer.current) clearTimeout(timer.current);
    timer.current = null;
    // eslint-disable-next-line react-hooks/set-state-in-effect -- завершение полосы по смене адреса
    setState((s) => (s === "loading" ? "done" : "idle"));
    const t = setTimeout(() => setState("idle"), 260);
    return () => clearTimeout(t);
  }, [pathname, search]);

  return (
    <div
      aria-hidden="true"
      className="pointer-events-none fixed inset-x-0 top-0 z-[70] h-[2px] origin-left bg-accent"
      style={{
        transform: state === "loading" ? "scaleX(0.7)" : state === "done" ? "scaleX(1)" : "scaleX(0)",
        opacity: state === "idle" ? 0 : 1,
        transition: state === "loading" ? "transform 1.2s cubic-bezier(0.1,0.7,0.2,1), opacity 0.1s" : "transform 0.2s ease-out, opacity 0.25s 0.1s",
      }}
    />
  );
}
