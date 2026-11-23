import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight, ChevronRight, Route } from "lucide-react";
import { SectionHead } from "./SectionHead";
import { LeadButton } from "@/components/site/Lead";
import { cars, carName, countries, formatKm, isLiveCatalog, labels, photos, priceText } from "@/content/cars";
import { asset, small } from "@/lib/utils";

/**
 * Топ-10 по реальным данным (карточки витрины из фида che168).
 * Телефон — строка списка как в Avito/Telegram: фото, название, одна строка характеристик, цена; вся строка — ссылка.
 * Десктоп — карточка с характеристиками, маршрутом и кнопкой точной цены.
 */
export function TopTen() {
  const pool = cars.some((c) => c.hit) ? cars.filter((c) => c.hit) : cars;
  const list = pool.slice(0, 10);
  return (
    <section data-component-id="top10" id="top" className="relative mx-auto max-w-site scroll-mt-20 px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <SectionHead
        watermark="TOP 10"
        eyebrow={isLiveCatalog ? "Реальные данные рынка" : "Популярные модели"}
        title="Топ-10 авто, которые выгодно везти сейчас"
        text="Самые востребованные «проходные» модели по числу предложений в Китае. Цена «от» — под ключ до Анапы."
        aside={
          <Link href="/catalog" className="press group hidden h-12 items-center gap-2 rounded-full border border-white/15 px-5 text-sm font-semibold transition hover:border-white/40 md:inline-flex">
            Весь каталог <ArrowUpRight className="size-4 transition group-hover:rotate-45" />
          </Link>
        }
      />
      <ol className="grid grid-cols-1 gap-2 sm:gap-3 lg:grid-cols-2 lg:gap-4">
        {list.map((c, i) => {
          const img = photos(c)[0];
          const n = String(i + 1).padStart(2, "0");
          return (
            <li key={c.slug} data-reveal="">
              {/* Телефон: строка списка */}
              <Link href={`/catalog/${c.slug}`} className="press-soft flex items-center gap-3 rounded-[1.25rem] bg-white/[0.04] p-2 pr-3 sm:hidden">
                <span className="relative h-[84px] w-[112px] shrink-0 overflow-hidden rounded-[0.9rem] bg-surface-2">
                  <Image src={asset(small(img.src))} alt={img.alt} fill sizes="112px" className={img.kind === "listing" ? "donor-crop object-cover" : "object-cover"} />
                  <span className="absolute top-1.5 left-1.5 rounded-full bg-black/70 px-1.5 py-0.5 text-[11px] font-semibold tnum text-white">{n}</span>
                </span>
                <span className="min-w-0 flex-1">
                  <span className="block truncate text-[17px] leading-[1.3] font-semibold">{carName(c)}</span>
                  <span className="mt-0.5 block truncate text-[13px] text-muted-foreground">
                    {c.year} · {formatKm(c.mileage)}{c.power ? ` · ${c.power} л.с.` : ""}
                  </span>
                  <span className="mt-1.5 flex items-center gap-2">
                    <span className="tnum text-[17px] leading-none font-bold">{priceText(c)}</span>
                    {c.utilizationOk && <span className="rounded-full bg-accent/15 px-1.5 py-0.5 text-[11px] font-medium text-accent">проходной</span>}
                  </span>
                </span>
                <ChevronRight className="size-5 shrink-0 text-muted-foreground" />
              </Link>

              {/* Планшет и десктоп: карточка */}
              <div className="group card relative hidden grid-cols-[220px_minmax(0,1fr)] gap-5 overflow-hidden p-2.5 transition duration-300 hover:border-white/15 sm:grid">
                <Link href={`/catalog/${c.slug}`} className="relative aspect-[4/3] overflow-hidden rounded-[1rem] bg-surface-2" aria-label={`${carName(c)} ${c.year}`}>
                  <Image src={asset(small(img.src))} alt={img.alt} fill sizes="220px" className={`${img.kind === "listing" ? "donor-crop " : ""}object-cover transition duration-700 ease-out group-hover:scale-[1.05]`} />
                  <span className="absolute top-2 left-2 grid h-8 min-w-8 place-items-center rounded-full bg-black/70 px-2 text-xs font-semibold tnum text-white">{n}</span>
                  {c.utilizationOk && <span className="absolute bottom-2 left-2 rounded-full bg-black/75 px-2 py-0.5 text-[11px] font-medium text-accent">проходной</span>}
                </Link>
                <div className="flex min-w-0 flex-col py-1 pr-2">
                  <h3 className="truncate text-[18px] leading-tight font-semibold">
                    <Link href={`/catalog/${c.slug}`} className="hover:text-accent">{carName(c)}</Link>
                  </h3>
                  <p className="mt-0.5 truncate text-[13px] text-muted-foreground">{c.trim || labels.body[c.body]}</p>
                  <dl className="mt-3 grid grid-cols-4 gap-x-3 text-[13px] lg:grid-cols-2 lg:gap-y-1.5 xl:grid-cols-4">
                    {[
                      ["Год", String(c.year)],
                      ["Пробег", formatKm(c.mileage)],
                      ["Мощность", c.power ? `${c.power} л.с.` : "—"],
                      ["Привод", labels.drive[c.drive]],
                    ].map(([k, v]) => (
                      <div key={k} className="min-w-0">
                        <dt className="text-[11px] text-muted-foreground">{k}</dt>
                        <dd className="truncate font-medium">{v}</dd>
                      </div>
                    ))}
                  </dl>
                  <p className="mt-3 flex items-center gap-1.5 text-[13px] text-muted-foreground">
                    <Route className="size-3.5 text-accent" />
                    {countries[c.country].label} → Анапа · {c.eta[0]}–{c.eta[1]} дней
                  </p>
                  <div className="mt-auto flex flex-wrap items-end justify-between gap-2 pt-3">
                    <p className="tnum text-[20px] leading-none font-bold">{priceText(c)}</p>
                    <LeadButton car={c.slug} context={`${carName(c)} ${c.year}, ${c.trim}`} title={`Точная цена: ${carName(c)}`} className="h-11 rounded-full border border-white/15 px-4 text-[13px] font-semibold transition hover:border-accent hover:text-accent">
                      Узнать точную цену
                    </LeadButton>
                  </div>
                </div>
              </div>
            </li>
          );
        })}
      </ol>
      <Link href="/catalog" className="press mt-4 flex h-12 items-center justify-center rounded-full border border-white/15 text-[15px] font-semibold md:hidden">Весь каталог</Link>
    </section>
  );
}
