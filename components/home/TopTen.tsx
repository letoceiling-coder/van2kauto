import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight, Route } from "lucide-react";
import { SectionHead } from "./SectionHead";
import { LeadButton } from "@/components/site/Lead";
import { cars, carName, countries, formatKm, isLiveCatalog, labels, photos, priceText } from "@/content/cars";
import { asset, small } from "@/lib/utils";

/**
 * Топ-10 по реальным данным: карточки витрины, собранные из фида che168 (самые востребованные «проходные» модели).
 * Список как в референсе: фото слева, справа характеристики, маршрут, срок и цена «от».
 */
export function TopTen() {
  const pool = cars.some((c) => c.hit) ? cars.filter((c) => c.hit) : cars;
  const list = pool.slice(0, 10);
  return (
    <section data-component-id="top10" id="top" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <SectionHead
        watermark="TOP 10"
        eyebrow={isLiveCatalog ? "По реальным данным рынка" : "Популярные модели"}
        title="Топ-10 автомобилей, которые выгодно везти сейчас"
        text="Самые востребованные «проходные» модели — по числу предложений на площадках Китая. Цена «от» под ключ до Анапы, точную смету пришлём по живому лоту."
        aside={
          <Link href="/catalog" className="group inline-flex h-12 items-center gap-2 rounded-full border border-white/15 px-5 text-sm font-semibold transition hover:border-white/40">
            Весь каталог <ArrowUpRight className="size-4 transition group-hover:rotate-45" />
          </Link>
        }
      />
      <ol className="grid gap-3 lg:grid-cols-2 lg:gap-4">
        {list.map((c, i) => {
          const img = photos(c)[0];
          return (
            <li key={c.slug} data-reveal="" className="group card relative grid grid-cols-[minmax(0,42%)_minmax(0,1fr)] gap-4 overflow-hidden p-2.5 transition duration-300 hover:border-white/15 sm:grid-cols-[220px_minmax(0,1fr)] sm:gap-5">
              <Link href={`/catalog/${c.slug}`} className="relative aspect-[4/3] overflow-hidden rounded-[1rem] bg-surface-2" aria-label={`${carName(c)} ${c.year}`}>
                <Image src={asset(small(img.src))} alt={img.alt} fill sizes="(min-width:1024px) 220px, 42vw" className="object-cover transition duration-700 ease-out group-hover:scale-[1.05]" />
                <span className="absolute top-2 left-2 grid h-8 min-w-8 place-items-center rounded-full bg-black/70 px-2 text-xs font-bold tnum text-white">{String(i + 1).padStart(2, "0")}</span>
                {c.utilizationOk && <span className="absolute bottom-2 left-2 rounded-full bg-black/75 px-2 py-0.5 text-[10px] font-semibold tracking-wide text-accent uppercase">Проходной</span>}
              </Link>
              <div className="flex min-w-0 flex-col py-1 pr-2">
                <div className="flex items-start justify-between gap-3">
                  <div className="min-w-0 flex-1">
                    <h3 className="truncate text-lg leading-tight font-semibold tracking-tight sm:text-xl">
                      <Link href={`/catalog/${c.slug}`} className="hover:text-accent">{carName(c)}</Link>
                    </h3>
                    <p className="mt-0.5 truncate text-[13px] text-muted-foreground">{c.trim || labels.body[c.body]}</p>
                  </div>
                </div>
                <dl className="mt-3 grid grid-cols-2 gap-x-3 gap-y-1.5 text-[13px] sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
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
                <p className="mt-3 hidden items-center gap-1.5 text-[13px] text-muted-foreground sm:flex">
                  <Route className="size-3.5 text-accent" />
                  {countries[c.country].label} → Анапа · {c.eta[0]}–{c.eta[1]} дней
                </p>
                <div className="mt-auto flex flex-wrap items-end justify-between gap-2 pt-3">
                  <p className="tnum text-xl leading-none font-semibold tracking-tight sm:text-[1.4rem]">{priceText(c)}</p>
                  <LeadButton car={c.slug} context={`${carName(c)} ${c.year}, ${c.trim}`} title={`Точная цена: ${carName(c)}`} className="h-9 rounded-full border border-white/15 px-3.5 text-[13px] font-semibold transition hover:border-accent hover:text-accent">
                    Узнать точную цену
                  </LeadButton>
                </div>
              </div>
            </li>
          );
        })}
      </ol>
    </section>
  );
}
