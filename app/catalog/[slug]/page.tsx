import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ShieldCheck, Clock, FileText, MessageCircle, ChevronRight, ChevronLeft, Check, Calendar, Gauge, Zap, Cog, Route } from "lucide-react";
import { Gallery } from "@/components/car/Gallery";
import { CarCard, CountryTag } from "@/components/car/CarCard";
import { FavButton } from "@/components/car/FavButton";
import { LotBar } from "@/components/car/LotBar";
import { LeadButton } from "@/components/site/Lead";
import { Reveal } from "@/components/motion/Reveal";
import { cars, carName, countries, estimate, formatKm, formatPrice, labels, photos, PRICE_CITY, PRICE_DATE, priceText } from "@/content/cars";

export const dynamicParams = false;

export function generateStaticParams() {
  return cars.map((c) => ({ slug: c.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const c = cars.find((x) => x.slug === slug);
  if (!c) return {};
  const title = `${carName(c)} ${c.year} ${countries[c.country].from} под заказ — цена под ключ`;
  const description = `${carName(c)} ${c.trim}, ${c.year}. ${c.summary} ${c.priceFrom ? `Цена ${priceText(c)}, точный расчёт под ключ — по запросу.` : `Ориентир ${formatPrice(c.price)} до Анапы.`}`;
  return { title, description, openGraph: { title, description, images: [photos(c)[0].src] } };
}

export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const car = cars.find((c) => c.slug === slug);
  if (!car) notFound();
  const name = carName(car);
  const rows = estimate(car);
  const context = `${name} ${car.year}, ${car.trim}`;
  const similar = [...cars.filter((c) => c.slug !== slug && c.country === car.country), ...cars.filter((c) => c.slug !== slug && c.country !== car.country && c.body === car.body)].slice(0, 4);
  const specs: [string, string][] = [
    ["Страна", countries[car.country].label],
    ["Год", String(car.year)],
    ["Состояние", labels.condition[car.condition]],
    ["Пробег", formatKm(car.mileage)],
    ["Кузов", labels.body[car.body]],
    ["Двигатель", car.engineLabel],
    ...(car.power ? ([["Мощность", `${car.power} л.с.`]] as [string, string][]) : []),
    ...(car.utilizationOk != null ? ([["Утильсбор", car.utilizationOk ? "Проходной (льготный)" : "Полный"]] as [string, string][]) : []),
    ["Привод", labels.drive[car.drive]],
    ["Руль", labels.steering[car.steering]],
    ...(car.generation ? ([["Поколение", car.generation]] as [string, string][]) : []),
  ];
  const quick = [
    { icon: Calendar, v: String(car.year), l: "год" },
    { icon: Gauge, v: car.mileage ? `${Math.round(car.mileage / 1000)} тыс.` : "0 км", l: "пробег" },
    { icon: Zap, v: car.power ? `${car.power}` : "—", l: "л.с." },
    { icon: Cog, v: car.drive === "AWD" ? "4WD" : car.drive, l: labels.drive[car.drive].toLowerCase() },
  ];
  const [a, b] = car.eta;
  const route = [
    { t: "Договор и предоплата", d: "День 0" },
    { t: car.status === "auction" ? "Торги и выкуп лота" : "Выкуп и экспорт", d: `${Math.round(a * 0.15)}–${Math.round(b * 0.2)} дн.` },
    { t: countries[car.country].road, d: `${Math.round(a * 0.5)}–${Math.round(b * 0.6)} дн.` },
    { t: "Таможня, СБКТС, ЭПТС", d: `${Math.round(a * 0.8)}–${Math.round(b * 0.85)} дн.` },
    { t: `Выдача в ${PRICE_CITY === "Анапы" ? "Анапе" : PRICE_CITY}`, d: `${a}–${b} дн.` },
  ];

  return (
    <div className="mx-auto max-w-site px-4 pt-16 pb-28 sm:px-6 sm:pt-24 sm:pb-10 lg:px-8 lg:pt-28">
      <nav aria-label="Хлебные крошки" className="mb-6 hidden flex-wrap items-center gap-1.5 text-sm text-muted-foreground sm:flex">
        <Link href="/" className="hover:text-foreground">Главная</Link><ChevronRight className="size-3.5" />
        <Link href="/catalog" className="hover:text-foreground">Каталог</Link><ChevronRight className="size-3.5" />
        <Link href={countries[car.country].href} className="hover:text-foreground">{countries[car.country].label}</Link><ChevronRight className="size-3.5" />
        <span className="text-foreground/80">{name}</span>
      </nav>

      <div className="grid gap-6 lg:grid-cols-12 lg:gap-10">
        <div className="-mx-4 sm:mx-0 lg:col-span-7">
          <div className="relative">
            <Gallery images={photos(car)} title={name} />
            <Link href="/catalog" aria-label="Назад в каталог" className="absolute top-3 left-3 z-10 hidden size-11 place-items-center rounded-full bg-black/65 max-sm:grid">
              <ChevronLeft className="size-5" />
            </Link>
          </div>
          <p className="mt-3 px-4 text-xs text-muted-foreground sm:px-0">Фото — иллюстрация модели. Фото конкретного лота и отчёт осмотра менеджер пришлёт после запроса.</p>
        </div>

        <div className="lg:col-span-5">
          <div className="lg:sticky lg:top-24">
            <Reveal>
              <div className="flex flex-wrap items-center gap-2">
                <CountryTag country={car.country} className="bg-white/[0.06]" />
                <span className="inline-flex h-7 items-center gap-1.5 rounded-full bg-white/[0.06] px-2.5 text-[11px] font-medium">
                  <span className="anim-pulse size-1.5 rounded-full bg-accent" />{labels.status[car.status]} · {PRICE_DATE}
                </span>
                {car.badge && <span className="inline-flex h-7 items-center rounded-full bg-accent px-2.5 text-[11px] font-bold text-accent-foreground">{car.badge}</span>}
              </div>
              <h1 className="mt-4 text-[2rem] leading-[1.06] font-semibold tracking-tight sm:text-5xl">{name}<span className="text-muted-foreground">, {car.year}</span></h1>
              <p className="mt-2 text-lg text-muted-foreground">{car.trim}</p>
            </Reveal>

            <div className="mt-5 grid grid-cols-4 gap-2">
              {quick.map(({ icon: Icon, v, l }) => (
                <div key={l} className="rounded-2xl border border-white/[0.07] bg-white/[0.03] px-2 py-3 text-center">
                  <Icon className="mx-auto size-4 text-accent" />
                  <p className="tnum mt-1.5 text-[15px] leading-tight font-semibold">{v}</p>
                  <p className="mt-0.5 truncate text-[11px] text-muted-foreground">{l}</p>
                </div>
              ))}
            </div>

            <div className="card relative mt-4 overflow-hidden p-5 sm:p-6">
              <div aria-hidden="true" className="absolute -top-20 -right-20 size-56 rounded-full bg-[radial-gradient(closest-side,rgb(235_17_29/0.16),transparent)]" />
              <p className="relative text-sm text-muted-foreground">{car.priceFrom ? "Цена под ключ" : "Ориентир под ключ"}</p>
              <p className="tnum relative mt-1 text-[2.4rem] leading-none font-semibold tracking-tight">{priceText(car)}</p>
              <p className="relative mt-2.5 text-[13px] text-muted-foreground">{car.priceFrom ? "Зависит от лота, курса и комплектации — точную сумму пришлём по запросу" : `Предварительно до ${PRICE_CITY} · расчёт от ${PRICE_DATE} · 7 дней`}</p>
              <div className="relative mt-5 grid grid-cols-2 gap-2 text-[13px]">
                <div className="rounded-2xl bg-white/[0.04] p-3.5"><Clock className="mb-2 size-4 text-accent" />{a}–{b} дней до выдачи</div>
                <div className="rounded-2xl bg-white/[0.04] p-3.5"><ShieldCheck className="mb-2 size-4 text-accent" />Проверка лота до выкупа</div>
              </div>
              <div className="relative mt-5 hidden gap-2 sm:flex">
                <LeadButton context={context} title={`Уточнить по ${name}`} className="btn-accent shine inline-flex h-14 flex-1 items-center justify-center gap-2 rounded-full font-semibold">
                  <MessageCircle className="size-[18px]" />Уточнить по авто
                </LeadButton>
                <FavButton slug={car.slug} className="size-14 border border-white/12 hover:border-white/30" />
              </div>
            </div>

            <p className="mt-6 text-[16px] leading-relaxed text-foreground/80">{car.summary}</p>
            <ul className="mt-4 grid gap-2.5">
              {car.highlights.map((h) => (
                <li key={h} className="flex items-start gap-3 text-[15px]"><span className="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-accent/15 text-accent"><Check className="size-3" strokeWidth={3} /></span>{h}</li>
              ))}
            </ul>
          </div>
        </div>
      </div>

      <div className="mt-12 grid gap-4 sm:mt-16 lg:grid-cols-3 lg:gap-6">
        <Reveal className="card p-5 sm:p-8">
          <h2 className="text-2xl font-semibold tracking-tight">Характеристики</h2>
          <dl className="mt-5 divide-y divide-white/[0.06]">
            {specs.map(([k, v]) => (
              <div key={k} className="flex justify-between gap-4 py-3 text-[15px]"><dt className="text-muted-foreground">{k}</dt><dd className="text-right font-medium">{v}</dd></div>
            ))}
          </dl>
        </Reveal>
        <Reveal delay={0.06} className="card p-5 sm:p-8">
          <div className="flex items-center justify-between gap-4">
            <h2 className="text-2xl font-semibold tracking-tight">Из чего цена</h2>
            <FileText className="size-5 text-accent" />
          </div>
          {car.priceFrom ? (
            <div className="mt-5 grid gap-3 text-[15px]">
              {["Автомобиль у продавца", "Экспорт, осмотр, доставка", "Таможня и утильсбор", "Брокер, СБКТС, ЭПТС", "Комиссия ВАН23АВТО"].map((n) => (
                <div key={n} className="flex items-center gap-3"><Check className="size-4 shrink-0 text-accent" strokeWidth={3} />{n}</div>
              ))}
              <p className="text-[13px] text-muted-foreground">Цена «под ключ» уже включает всё перечисленное. Смету по конкретному лоту — с курсом и сроком действия — пришлём до оплаты.</p>
              <LeadButton context={context} title={`Расчёт по ${name}`} className="btn-accent mt-1 inline-flex h-12 items-center justify-center rounded-full font-semibold">Получить точный расчёт</LeadButton>
            </div>
          ) : (
          <>
          <ul className="mt-5 divide-y divide-white/[0.06]">
            {rows.map((r) => (
              <li key={r.name} className="flex justify-between gap-4 py-3 text-[15px]"><span className="text-muted-foreground">{r.name}</span><span className="tnum shrink-0 font-medium">{formatPrice(r.value)}</span></li>
            ))}
            <li className="flex justify-between gap-4 pt-4 text-lg font-semibold"><span>Итого</span><span className="tnum text-accent">{formatPrice(car.price)}</span></li>
          </ul>
          <p className="mt-4 text-[13px] text-muted-foreground">Пример расчёта. Финальная смета фиксируется по лоту — с курсом, датой и сроком действия — до оплаты.</p>
          </>
          )}
        </Reveal>
        <Reveal delay={0.12} className="card p-5 sm:p-8">
          <div className="flex items-center justify-between gap-4">
            <h2 className="text-2xl font-semibold tracking-tight">Как привезём</h2>
            <Route className="size-5 text-accent" />
          </div>
          <ol className="relative mt-6 grid gap-5 before:absolute before:top-2 before:bottom-2 before:left-[7px] before:w-px before:bg-gradient-to-b before:from-accent before:to-white/10">
            {route.map((s, i) => (
              <li key={s.t} className="relative flex items-start justify-between gap-4 pl-8">
                <span className={`absolute top-1 left-0 size-[15px] rounded-full border-2 ${i === 0 ? "border-accent bg-accent" : "border-accent/60 bg-background"}`} />
                <span className="text-[15px] leading-snug">{s.t}</span>
                <span className="tnum shrink-0 text-sm text-muted-foreground">{s.d}</span>
              </li>
            ))}
          </ol>
          <p className="mt-6 text-[13px] text-muted-foreground">{countries[car.country].city} → Анапа. О сдвиге сроков сообщаем сами, с новой датой.</p>
        </Reveal>
      </div>

      {similar.length > 0 && (
        <section className="mt-16 sm:mt-20">
          <div className="mb-6 flex items-end justify-between gap-4 sm:mb-8">
            <h2 className="text-headline">Похожие варианты</h2>
            <Link href={`/catalog?country=${car.country}`} className="shrink-0 text-sm font-semibold text-foreground/70 hover:text-accent">Все →</Link>
          </div>
          <div className="no-scrollbar -mx-4 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto px-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-4 sm:overflow-visible sm:px-0 xl:grid-cols-4">
            {similar.map((c) => (
              <div key={c.slug} className="w-[80vw] max-w-[340px] shrink-0 snap-start sm:w-auto sm:max-w-none"><CarCard car={c} /></div>
            ))}
          </div>
        </section>
      )}

      <LotBar slug={car.slug} price={priceText(car)} context={context} title={name} />
    </div>
  );
}
