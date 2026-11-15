"use client";

import Image from "next/image";
import { useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { SectionHead } from "./SectionHead";
import { LeadButton } from "@/components/site/Lead";
import { cars, carName, estimate, formatPrice, photos, PRICE_DATE, PRICE_CITY } from "@/content/cars";
import { asset, cn } from "@/lib/utils";

const COLORS = ["#c6f447", "#9fd13a", "#6f8f2f", "#4a5d2a", "#353f26", "#262b22"];
const PICK = ["vw-tayron", "changan-qiyuan-q05", "honda-freed", "kia-sportage", "suzuki-jimny"];

export function PriceBreakdown() {
  const options = PICK.map((s) => cars.find((c) => c.slug === s)!).filter(Boolean);
  const [slug, setSlug] = useState(options[0].slug);
  const car = options.find((c) => c.slug === slug)!;
  const rows = estimate(car);
  return (
    <section data-component-id="pricing" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <SectionHead watermark="PRICE" eyebrow="Прозрачная цена" title="Как складывается стоимость под ключ" text="Каждая смета — по статьям, с курсом, датой и сроком действия. Выберите модель и посмотрите пример расчёта." />
      <div className="card grid overflow-hidden lg:grid-cols-12">
        <div className="relative min-h-[280px] lg:col-span-5">
          <AnimatePresence mode="wait">
            <motion.div key={car.slug} initial={{ opacity: 0, scale: 1.05 }} animate={{ opacity: 1, scale: 1 }} exit={{ opacity: 0 }} transition={{ duration: 0.5 }} className="absolute inset-0">
              <Image src={asset(photos(car)[0].src)} alt={photos(car)[0].alt} fill sizes="(min-width:1024px) 40vw, 100vw" className="object-cover" />
            </motion.div>
          </AnimatePresence>
          <div className="absolute inset-0 bg-gradient-to-t from-[#121417] via-[#121417]/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-[#121417]" />
          <div className="absolute inset-x-4 bottom-4 flex flex-wrap gap-2 lg:inset-x-6 lg:bottom-6">
            {options.map((o) => (
              <button key={o.slug} onClick={() => setSlug(o.slug)} aria-pressed={o.slug === slug} className={cn("h-9 rounded-full px-3.5 text-[13px] font-medium backdrop-blur-md transition", o.slug === slug ? "bg-accent text-accent-foreground" : "bg-black/50 text-white/80 hover:bg-black/70")}>
                {o.model}
              </button>
            ))}
          </div>
        </div>
        <div className="p-6 sm:p-8 lg:col-span-7 lg:p-10">
          <div className="flex flex-wrap items-end justify-between gap-4">
            <div>
              <p className="text-sm text-muted-foreground">{carName(car)} · {car.year} · {car.trim}</p>
              <AnimatePresence mode="wait">
                <motion.p key={car.slug} initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -8 }} className="tnum mt-2 text-4xl font-semibold tracking-tight sm:text-5xl">
                  {formatPrice(car.price)}
                </motion.p>
              </AnimatePresence>
            </div>
            <p className="rounded-full border border-white/10 px-3 py-1.5 text-xs text-muted-foreground">Предварительно до {PRICE_CITY} · расчёт от {PRICE_DATE}</p>
          </div>
          <div className="mt-7 flex h-3 overflow-hidden rounded-full bg-white/5" role="img" aria-label="Доли статей расходов">
            {rows.map((r, i) => (
              <motion.div key={r.name} initial={false} animate={{ width: `${(r.value / car.price) * 100}%` }} transition={{ duration: 0.7, ease: [0.22, 1, 0.36, 1] }} style={{ background: COLORS[i] }} className="h-full border-r border-[#121417] last:border-0" />
            ))}
          </div>
          <ul className="mt-6 divide-y divide-white/[0.06]">
            {rows.map((r, i) => (
              <li key={r.name} className="flex items-center justify-between gap-4 py-3.5 text-[15px]">
                <span className="flex items-center gap-3 text-foreground/80">
                  <span className="size-2.5 shrink-0 rounded-full" style={{ background: COLORS[i] }} />
                  {r.name}
                </span>
                <span className="tnum font-medium">{formatPrice(r.value)}</span>
              </li>
            ))}
          </ul>
          <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p className="max-w-sm text-sm text-muted-foreground">Пример для демонстрации. Точный расчёт зависит от лота, курса и даты таможенного оформления.</p>
            <LeadButton context={carName(car)} title="Получить точную смету" className="btn-accent shine inline-flex h-12 shrink-0 items-center justify-center rounded-full px-6 text-sm font-semibold">
              Получить точную смету
            </LeadButton>
          </div>
        </div>
      </div>
    </section>
  );
}
