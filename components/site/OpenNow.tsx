"use client";

import { useSyncExternalStore } from "react";

/** «Открыто сейчас» по времени Анапы (МСК): Пн–Сб 9–19. На сервере не рендерим статус (время клиента). */
function status() {
  const now = new Date(new Date().toLocaleString("en-US", { timeZone: "Europe/Moscow" }));
  const d = now.getDay(), h = now.getHours() + now.getMinutes() / 60;
  const open = d >= 1 && d <= 6 && h >= 9 && h < 19;
  return open ? "open" : "closed";
}

export function OpenNow() {
  const s = useSyncExternalStore((cb) => { const t = setInterval(cb, 60_000); return () => clearInterval(t); }, status, () => "");
  if (!s) return <span className="inline-block h-7" />;
  return (
    <span className={`inline-flex h-7 items-center gap-2 rounded-full px-3 text-xs font-semibold ${s === "open" ? "bg-accent/15 text-accent" : "bg-white/[0.06] text-muted-foreground"}`}>
      <span className={`size-1.5 rounded-full ${s === "open" ? "anim-pulse bg-accent" : "bg-muted-foreground"}`} />
      {s === "open" ? "Открыто сейчас" : "Сейчас закрыто — ответим утром"}
    </span>
  );
}
