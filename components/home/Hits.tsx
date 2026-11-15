"use client";

import Link from "next/link";
import { useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { ArrowUpRight } from "lucide-react";
import { CarCard } from "@/components/car/CarCard";
import { SectionHead } from "./SectionHead";
import { cars, countries, type Country } from "@/content/cars";
import { cn } from "@/lib/utils";

type Tab = "all" | Country;
const TABS: { id: Tab; label: string }[] = [{ id: "all", label: "Все" }, ...(Object.keys(countries) as Country[]).map((c) => ({ id: c, label: countries[c].label }))];

export function Hits() {
  const [tab, setTab] = useState<Tab>("all");
  const list = cars.filter((c) => c.hit && (tab === "all" || c.country === tab)).slice(0, 8);
  return (
    <section data-component-id="hits" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <SectionHead
        watermark="HITS"
        eyebrow="Хиты сезона"
        title="Что заказывают чаще всего"
        text="Модели, по которым больше всего заявок в Анапе и крае. Цена — ориентир под ключ до Анапы, точную смету пришлём по живому лоту."
        aside={
          <div role="tablist" aria-label="Страна" className="isolate flex gap-1 rounded-full border border-white/10 bg-white/[0.03] p-1">
            {TABS.map((t) => (
              <button key={t.id} role="tab" aria-selected={tab === t.id} onClick={() => setTab(t.id)} className={cn("relative h-10 rounded-full px-4 text-sm font-medium transition-colors", tab === t.id ? "text-accent-foreground" : "text-foreground/65 hover:text-foreground")}>
                {tab === t.id && <motion.span layoutId="hits-tab" className="absolute inset-0 -z-10 rounded-full bg-accent" transition={{ type: "spring", damping: 28, stiffness: 380 }} />}
                {t.label}
              </button>
            ))}
          </div>
        }
      />
      <motion.div layout className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <AnimatePresence mode="popLayout">
          {list.map((c, i) => (
            <motion.div key={c.slug} layout initial={{ opacity: 0, y: 24 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, scale: 0.96 }} transition={{ duration: 0.45, delay: i * 0.04, ease: [0.22, 1, 0.36, 1] }}>
              <CarCard car={c} />
            </motion.div>
          ))}
        </AnimatePresence>
      </motion.div>
      <div className="mt-10 flex justify-center">
        <Link href={tab === "all" ? "/catalog" : `/catalog?country=${tab}`} className="group inline-flex h-14 items-center gap-3 rounded-full border border-white/12 pr-2 pl-7 font-semibold transition hover:border-accent/60">
          Весь каталог
          <span className="grid size-10 place-items-center rounded-full bg-accent text-accent-foreground transition group-hover:rotate-45"><ArrowUpRight className="size-4" /></span>
        </Link>
      </div>
    </section>
  );
}
