import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ShieldCheck, Clock, FileText, MessageCircle, ChevronRight, Check } from "lucide-react";
import { Gallery } from "@/components/car/Gallery";
import { CarCard, CountryTag } from "@/components/car/CarCard";
import { FavButton } from "@/components/car/FavButton";
import { LeadButton } from "@/components/site/Lead";
import { Reveal } from "@/components/motion/Reveal";
import { cars, carName, countries, estimate, formatKm, formatPrice, labels, photos, PRICE_CITY, PRICE_DATE } from "@/content/cars";

export const dynamicParams = false;

export function generateStaticParams() {
  return cars.map((c) => ({ slug: c.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const c = cars.find((x) => x.slug === slug);
  if (!c) return {};
  return {
    title: `${carName(c)} ${c.year} ${countries[c.country].from} под заказ — цена под ключ`,
    description: `${carName(c)} ${c.trim}, ${c.year}. ${c.summary} Ориентир ${formatPrice(c.price)} до Анапы.`,
  };
}

export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const car = cars.find((c) => c.slug === slug);
  if (!car) notFound();
  const name = carName(car);
  const rows = estimate(car);
  const similar = [...cars.filter((c) => c.slug !== slug && c.country === car.country), ...cars.filter((c) => c.slug !== slug && c.country !== car.country && c.body === car.body)].slice(0, 4);
  const specs: [string, string][] = [
    ["Страна", countries[car.country].label],
    ["Год", String(car.year)],
    ["Состояние", labels.condition[car.condition]],
    ["Пробег", formatKm(car.mileage)],
    ["Кузов", labels.body[car.body]],
    ["Двигатель", car.engineLabel],
    ["Мощность", `${car.power} л.с.`],
    ["Привод", labels.drive[car.drive]],
    ["Руль", labels.steering[car.steering]],
    ...(car.generation ? ([["Поколение", car.generation]] as [string, string][]) : []),
  ];

  return (
    <div className="mx-auto max-w-site px-4 pt-24 pb-10 sm:px-6 lg:px-8 lg:pt-28">
      <nav aria-label="Хлебные крошки" className="mb-6 flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
        <Link href="/" className="hover:text-foreground">Главная</Link><ChevronRight className="size-3.5" />
        <Link href="/catalog" className="hover:text-foreground">Каталог</Link><ChevronRight className="size-3.5" />
        <Link href={countries[car.country].href} className="hover:text-foreground">{countries[car.country].label}</Link><ChevronRight className="size-3.5" />
        <span className="text-foreground/80">{name}</span>
      </nav>

      <div className="grid gap-8 lg:grid-cols-12 lg:gap-10">
        <div className="lg:col-span-7">
          <Gallery images={photos(car)} title={name} />
          <p className="mt-3 text-xs text-muted-foreground">Фото — иллюстрация модели. Фото конкретного лота и отчёт осмотра менеджер пришлёт после запроса.</p>
        </div>

        <div className="lg:col-span-5">
          <div className="lg:sticky lg:top-24">
            <div className="flex flex-wrap items-center gap-2">
              <CountryTag country={car.country} className="bg-white/[0.06]" />
              <span className="inline-flex h-7 items-center gap-1.5 rounded-full bg-white/[0.06] px-2.5 text-[11px] font-medium">
                <span className="anim-pulse size-1.5 rounded-full bg-accent" />{labels.status[car.status]} · проверено {PRICE_DATE}
              </span>
              {car.badge && <span className="inline-flex h-7 items-center rounded-full bg-accent px-2.5 text-[11px] font-bold text-accent-foreground">{car.badge}</span>}
            </div>
            <h1 className="mt-4 text-[2rem] leading-[1.08] font-semibold tracking-tight sm:text-5xl">{name}, {car.year}</h1>
            <p className="mt-2 text-lg text-muted-foreground">{car.trim}</p>
            <p className="mt-5 text-[16px] leading-relaxed text-foreground/80">{car.summary}</p>

            <div className="card mt-6 p-6">
              <p className="text-sm text-muted-foreground">Ориентир под ключ</p>
              <p className="tnum mt-1 text-4xl font-semibold tracking-tight">{formatPrice(car.price)}</p>
              <p className="mt-2 text-sm text-muted-foreground">Предварительно до {PRICE_CITY} · расчёт от {PRICE_DATE} · действует 7 дней</p>
              <div className="mt-5 grid grid-cols-2 gap-3 text-sm">
                <div className="rounded-2xl bg-white/[0.04] p-3.5"><Clock className="mb-2 size-4 text-accent" />{car.eta[0]}–{car.eta[1]} дней до выдачи</div>
                <div className="rounded-2xl bg-white/[0.04] p-3.5"><ShieldCheck className="mb-2 size-4 text-accent" />Проверка лота до выкупа</div>
              </div>
              <div className="mt-5 hidden gap-2 sm:flex">
                <LeadButton context={`${name} ${car.year}, ${car.trim}`} title={`Уточнить по ${name}`} className="btn-accent shine inline-flex h-14 flex-1 items-center justify-center gap-2 rounded-full font-semibold">
                  <MessageCircle className="size-4.5" />Уточнить по авто
                </LeadButton>
                <FavButton slug={car.slug} className="size-14 border border-white/12 hover:border-white/30" />
              </div>
            </div>

            <ul className="mt-6 grid gap-2.5">
              {car.highlights.map((h) => (
                <li key={h} className="flex items-start gap-3 text-[15px]"><span className="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-accent/15 text-accent"><Check className="size-3" strokeWidth={3} /></span>{h}</li>
              ))}
            </ul>
          </div>
        </div>
      </div>

      <div className="mt-16 grid gap-6 lg:grid-cols-2">
        <Reveal className="card p-6 sm:p-8">
          <h2 className="text-2xl font-semibold tracking-tight">Характеристики</h2>
          <dl className="mt-6 divide-y divide-white/[0.06]">
            {specs.map(([k, v]) => (
              <div key={k} className="flex justify-between gap-4 py-3.5 text-[15px]"><dt className="text-muted-foreground">{k}</dt><dd className="text-right font-medium">{v}</dd></div>
            ))}
          </dl>
        </Reveal>
        <Reveal delay={0.08} className="card p-6 sm:p-8">
          <div className="flex items-center justify-between gap-4">
            <h2 className="text-2xl font-semibold tracking-tight">Из чего цена</h2>
            <FileText className="size-5 text-accent" />
          </div>
          <ul className="mt-6 divide-y divide-white/[0.06]">
            {rows.map((r) => (
              <li key={r.name} className="flex justify-between gap-4 py-3.5 text-[15px]"><span className="text-muted-foreground">{r.name}</span><span className="tnum font-medium">{formatPrice(r.value)}</span></li>
            ))}
            <li className="flex justify-between gap-4 pt-4 text-lg font-semibold"><span>Итого до {PRICE_CITY}</span><span className="tnum text-accent">{formatPrice(car.price)}</span></li>
          </ul>
          <p className="mt-5 text-sm text-muted-foreground">Пример расчёта. Финальная смета фиксируется по конкретному лоту: версия, курс, дата и срок действия — до оплаты.</p>
        </Reveal>
      </div>

      {similar.length > 0 && (
        <section className="mt-20">
          <h2 className="text-headline mb-8">Похожие варианты</h2>
          <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {similar.map((c) => <CarCard key={c.slug} car={c} />)}
          </div>
        </section>
      )}

      {/* Мобильный закреплённый CTA (ТЗ §3) — над нижней навигацией */}
      <div className="fixed inset-x-0 bottom-[84px] z-30 px-3 sm:hidden">
        <div className="mx-auto flex max-w-md items-center gap-2 rounded-full border border-white/10 bg-surface/90 p-1.5 pl-5 backdrop-blur-xl">
          <div className="min-w-0 flex-1">
            <p className="tnum truncate text-[15px] leading-tight font-semibold">{formatPrice(car.price)}</p>
            <p className="truncate text-[11px] text-muted-foreground">до {PRICE_CITY}, предварительно</p>
          </div>
          <LeadButton context={`${name} ${car.year}, ${car.trim}`} title={`Уточнить по ${name}`} className="btn-accent h-12 shrink-0 rounded-full px-5 text-sm font-semibold">Уточнить по авто</LeadButton>
        </div>
      </div>
    </div>
  );
}
