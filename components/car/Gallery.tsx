"use client";

import Image from "next/image";
import { useCallback, useEffect, useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { ChevronLeft, ChevronRight, Expand, X } from "lucide-react";
import type { CarImage } from "@/content/cars";
import { asset, cn, small } from "@/lib/utils";

const KIND: Record<NonNullable<CarImage["kind"]>, string> = { listing: "Фото объявления", inspection: "Фото осмотра", delivery: "Фото выдачи", model: "Иллюстрация модели" };

export function Gallery({ images, title }: { images: CarImage[]; title: string }) {
  const [i, setI] = useState(0);
  const [dir, setDir] = useState(1);
  const [full, setFull] = useState(false);
  const n = images.length;
  const go = useCallback((d: number) => { setDir(d); setI((x) => (x + d + n) % n); }, [n]);

  useEffect(() => {
    if (!full) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") setFull(false);
      if (e.key === "ArrowRight") go(1);
      if (e.key === "ArrowLeft") go(-1);
    };
    document.documentElement.style.overflow = "hidden";
    window.addEventListener("keydown", onKey);
    return () => { window.removeEventListener("keydown", onKey); document.documentElement.style.overflow = ""; };
  }, [full, go]);

  const img = images[i];
  const slide = (big?: boolean) => (
    <AnimatePresence initial={false} custom={dir} mode="popLayout">
      <motion.div
        key={i}
        custom={dir}
        initial={{ x: `${dir * 30}%`, opacity: 0 }}
        animate={{ x: 0, opacity: 1 }}
        exit={{ x: `${dir * -30}%`, opacity: 0 }}
        transition={{ duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
        drag={n > 1 ? "x" : false}
        dragConstraints={{ left: 0, right: 0 }}
        dragElastic={0.2}
        onDragEnd={(_, info) => { if (info.offset.x < -60) go(1); else if (info.offset.x > 60) go(-1); }}
        style={{ touchAction: "pan-y" }}
        className="absolute inset-0 cursor-grab active:cursor-grabbing"
      >
        <Image src={asset(img.src)} alt={img.alt} fill priority={i === 0} sizes={big ? "100vw" : "(min-width:1024px) 60vw, 100vw"} className={big ? "object-contain" : `${img.kind === "listing" ? "donor-crop " : ""}object-cover`} draggable={false} />
      </motion.div>
    </AnimatePresence>
  );

  return (
    <div onKeyDown={(e) => { if (e.key === "ArrowRight") go(1); if (e.key === "ArrowLeft") go(-1); }}>
      <div className="relative aspect-[4/3] overflow-hidden border-white/[0.08] bg-surface-2 sm:rounded-[1.75rem] sm:border" tabIndex={0} role="region" aria-roledescription="галерея" aria-label={`Фото: ${title}`}>
        {slide()}
        <div className="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-black/50 to-transparent" />
        <span className="absolute top-4 left-4 rounded-full bg-black/65 px-3 py-1.5 text-xs font-medium max-sm:top-auto max-sm:bottom-3 max-sm:left-3 max-sm:text-[11px]">{KIND[img.kind ?? "model"]}</span>
        <button onClick={() => setFull(true)} aria-label="Открыть на весь экран" className="absolute top-3 right-3 grid size-11 place-items-center rounded-full bg-black/65 transition hover:bg-black/75"><Expand className="size-4.5" /></button>
        {n > 1 && (
          <>
            <button onClick={() => go(-1)} aria-label="Предыдущее фото" className="absolute top-1/2 left-3 hidden sm:grid size-11 -translate-y-1/2 place-items-center rounded-full bg-black/65 transition hover:bg-black/75"><ChevronLeft className="size-5" /></button>
            <button onClick={() => go(1)} aria-label="Следующее фото" className="absolute top-1/2 right-3 hidden sm:grid size-11 -translate-y-1/2 place-items-center rounded-full bg-black/65 transition hover:bg-black/75"><ChevronRight className="size-5" /></button>
            <div className="absolute inset-x-0 bottom-4 flex justify-center gap-1.5 sm:hidden" aria-hidden="true">
              {images.map((m, k) => <span key={m.src} className={cn("h-1.5 rounded-full bg-white transition-all duration-300", k === i ? "w-5" : "w-1.5 opacity-50")} />)}
            </div>
            <span className="tnum absolute right-4 bottom-4 max-sm:hidden rounded-full bg-black/65 px-3 py-1.5 text-xs">{i + 1} / {n}</span>
          </>
        )}
      </div>
      {n > 1 && (
        <div className="no-scrollbar mt-3 hidden gap-2 overflow-x-auto sm:flex">
          {images.map((m, k) => (
            <button key={m.src} onClick={() => { setDir(k > i ? 1 : -1); setI(k); }} aria-label={`Фото ${k + 1}`} aria-current={k === i} className={cn("relative aspect-[4/3] w-24 shrink-0 overflow-hidden rounded-xl border-2 transition sm:w-28", k === i ? "border-accent" : "border-transparent opacity-60 hover:opacity-100")}>
              <Image src={asset(small(m.src))} alt="" fill sizes="112px" className={m.kind === "listing" ? "donor-crop object-cover" : "object-cover"} />
            </button>
          ))}
        </div>
      )}
      <AnimatePresence>
        {full && (
          <motion.div role="dialog" aria-modal="true" aria-label="Просмотр фото" className="fixed inset-0 z-[120] bg-black" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
            <div className="absolute inset-0">{slide(true)}</div>
            <button autoFocus onClick={() => setFull(false)} aria-label="Закрыть" className="absolute top-4 right-4 grid size-12 place-items-center rounded-full bg-white/10"><X className="size-5" /></button>
            {n > 1 && (
              <>
                <button onClick={() => go(-1)} aria-label="Предыдущее фото" className="absolute top-1/2 left-4 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white/10"><ChevronLeft className="size-6" /></button>
                <button onClick={() => go(1)} aria-label="Следующее фото" className="absolute top-1/2 right-4 grid size-12 -translate-y-1/2 place-items-center rounded-full bg-white/10"><ChevronRight className="size-6" /></button>
              </>
            )}
            <p className="tnum absolute bottom-6 left-1/2 -translate-x-1/2 text-sm text-white/70">{i + 1} / {n} · {img.alt}</p>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
