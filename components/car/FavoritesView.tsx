"use client";

import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import { Heart, LayoutGrid, Columns3, X, Trash2 } from "lucide-react";
import { motion } from "motion/react";
import { CarCard } from "./CarCard";
import { useFavorites } from "@/components/site/favorites";
import { LeadButton } from "@/components/site/Lead";
import { type Car, cars, carName, countries, formatKm, formatPrice, labels, photos } from "@/content/cars";
import { asset, cn, small } from "@/lib/utils";

const ROWS: { label: string; get: (c: Car) => string; best?: (cs: Car[]) => number }[] = [
  { label: "Цена под ключ", get: (c) => formatPrice(c.price), best: (cs) => Math.min(...cs.map((c) => c.price)) },
  { label: "Страна", get: (c) => countries[c.country].label },
  { label: "Год", get: (c) => String(c.year) },
  { label: "Пробег", get: (c) => formatKm(c.mileage) },
  { label: "Кузов", get: (c) => labels.body[c.body] },
  { label: "Двигатель", get: (c) => c.engineLabel },
  { label: "Мощность", get: (c) => `${c.power} л.с.` },
  { label: "Привод", get: (c) => labels.drive[c.drive] },
  { label: "Руль", get: (c) => labels.steering[c.steering] },
  { label: "Срок до выдачи", get: (c) => `${c.eta[0]}–${c.eta[1]} дней` },
  { label: "Статус", get: (c) => labels.status[c.status] },
];

export function FavoritesView() {
  const { list, toggle } = useFavorites();
  const [view, setView] = useState<"grid" | "compare">("grid");
  const items = cars.filter((c) => list.includes(c.slug));

  if (!items.length)
    return (
      <div className="card relative mt-10 grid place-items-center overflow-hidden px-6 py-20 text-center">
        <div aria-hidden="true" className="absolute -bottom-32 left-1/2 size-80 -translate-x-1/2 rounded-full bg-[radial-gradient(closest-side,rgb(198_244_71/0.14),transparent)]" />
        <motion.div initial={{ scale: 0.6, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} transition={{ type: "spring", stiffness: 260, damping: 16 }} className="relative grid size-20 place-items-center rounded-full border border-white/10 bg-white/[0.04]">
          <Heart className="size-8 text-accent" />
        </motion.div>
        <p className="relative mt-6 text-2xl font-semibold tracking-tight">Пока пусто</p>
        <p className="relative mt-2 max-w-sm text-muted-foreground">Нажмите на сердечко в карточке, чтобы сравнить машины по цене, сроку и характеристикам — и отправить менеджеру одним запросом.</p>
        <Link href="/catalog" className="btn-accent shine relative mt-7 inline-flex h-13 items-center rounded-full px-7 font-semibold">Открыть каталог</Link>
      </div>
    );

  const best = Math.min(...items.map((c) => c.price));

  return (
    <>
      <div className="mt-8 flex flex-wrap items-center justify-between gap-3">
        <div role="tablist" aria-label="Вид" className="isolate inline-flex gap-1 rounded-full border border-white/10 bg-white/[0.03] p-1">
          {([["grid", "Карточки", LayoutGrid], ["compare", "Сравнение", Columns3]] as const).map(([id, label, Icon]) => (
            <button key={id} role="tab" aria-selected={view === id} onClick={() => setView(id)} className={cn("relative inline-flex h-10 items-center gap-2 rounded-full px-4 text-sm font-medium transition-colors", view === id ? "text-accent-foreground" : "text-foreground/65")}>
              {view === id && <motion.span layoutId="fav-tab" className="absolute inset-0 -z-10 rounded-full bg-accent" transition={{ type: "spring", damping: 28, stiffness: 380 }} />}
              <Icon className="size-4" />{label}
            </button>
          ))}
        </div>
        <LeadButton context={items.map(carName).join(", ")} title="Сравнить и посчитать избранное" className="btn-accent shine h-12 rounded-full px-6 text-sm font-semibold max-sm:w-full">
          Отправить {items.length} {items.length === 1 ? "авто" : "авто"} менеджеру
        </LeadButton>
      </div>

      {view === "grid" ? (
        <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          {items.map((c, i) => (
            <div key={c.slug} className="card-in" style={{ animationDelay: `${i * 50}ms` }}><CarCard car={c} /></div>
          ))}
        </div>
      ) : (
        <div className="card-in card mt-6 overflow-hidden">
          <div className="no-scrollbar overflow-x-auto">
            <table className="w-full min-w-max border-collapse text-[14px]">
              <thead>
                <tr>
                  <th className="sticky left-0 z-10 w-32 bg-surface p-4 text-left align-bottom text-xs font-medium text-muted-foreground sm:w-44">Параметр</th>
                  {items.map((c) => (
                    <th key={c.slug} className="w-[200px] p-3 text-left align-top font-normal sm:w-[240px]">
                      <div className="relative aspect-[4/3] overflow-hidden rounded-xl">
                        <Image src={asset(small(photos(c)[0].src))} alt={photos(c)[0].alt} fill sizes="240px" className="object-cover" />
                        <button onClick={() => toggle(c.slug)} aria-label={`Убрать ${carName(c)}`} className="absolute top-2 right-2 grid size-8 place-items-center rounded-full bg-black/70"><X className="size-4" /></button>
                      </div>
                      <Link href={`/catalog/${c.slug}`} className="mt-3 block font-semibold tracking-tight hover:text-accent">{carName(c)}</Link>
                      <p className="truncate text-xs text-muted-foreground">{c.trim}</p>
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {ROWS.map((r) => (
                  <tr key={r.label} className="border-t border-white/[0.06]">
                    <th scope="row" className="sticky left-0 z-10 bg-surface p-4 text-left text-xs font-medium text-muted-foreground">{r.label}</th>
                    {items.map((c) => {
                      const top = r.best && c.price === best && items.length > 1;
                      return (
                        <td key={c.slug} className={cn("p-3 px-4 tnum", top && "font-semibold text-accent")}>
                          {r.get(c)}
                          {top && <span className="ml-2 rounded-full bg-accent/15 px-2 py-0.5 text-[10px] font-bold uppercase">выгоднее</span>}
                        </td>
                      );
                    })}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div className="flex items-center justify-between gap-3 border-t border-white/[0.06] p-4 text-xs text-muted-foreground">
            <span className="sm:hidden">← листайте таблицу →</span>
            <button onClick={() => items.forEach((c) => toggle(c.slug))} className="ml-auto inline-flex items-center gap-1.5 hover:text-red-300"><Trash2 className="size-3.5" />Очистить избранное</button>
          </div>
        </div>
      )}
    </>
  );
}
