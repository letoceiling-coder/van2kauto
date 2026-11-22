import { Eyebrow } from "./SectionHead";
import { LeadButton } from "@/components/site/Lead";
import { cars, shortPrice } from "@/content/cars";

/**
 * «Почему выбирают» по логике референса: заголовок закреплён слева (sticky), справа при прокрутке
 * проходят крупные цифры с пояснениями. Только CSS (sticky + появление по прокрутке) — без JS и рывков.
 */
export function WhyUs() {
  const minPrice = Math.min(...cars.map((c) => c.price).filter(Boolean));
  const rows: { v: string; t: string; d: string }[] = [
    { v: "25–50", t: "дней до выдачи", d: "От предоплаты до ключей в Анапе. Срок фиксируем диапазоном и сами сообщаем о сдвигах — с новой датой." },
    { v: "до 160 л.с.", t: "льготный утильсбор", d: "Подбираем «проходные» комплектации 3–5 лет: пошлина и утильсбор в разы ниже, машина выходит дешевле рынка." },
    { v: `от ${shortPrice(minPrice)}`, t: "под ключ до Анапы", d: "Цена включает автомобиль, экспорт, доставку, таможню, утильсбор, брокера, СБКТС/ЭПТС и нашу комиссию." },
    { v: "0 ₽", t: "скрытых платежей", d: "Смета по статьям с курсом и сроком действия — до оплаты. Любое изменение согласуем заранее." },
    { v: "3", t: "страны поставки", d: "Китай, Корея и Япония: площадки, аукционы и проверенные партнёры на месте. Осмотр до выкупа." },
    { v: "1", t: "менеджер на всю сделку", d: "Руслан ведёт заявку от первого сообщения до выдачи. Статус каждого этапа — в мессенджере." },
  ];
  return (
    <section data-component-id="why" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <div className="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:gap-16">
        <div className="lg:col-span-5">
          <div className="lg:sticky lg:top-28">
            <Eyebrow>Почему мы</Eyebrow>
            <h2 className="text-headline mt-4 pb-[0.06em]" style={{ hyphens: "manual", WebkitHyphens: "manual" }}>
              <span className="text-metal">Почему клиенты выбирают</span> <span className="text-foreground/40">ВАН23АВТО</span>
            </h2>
            <div className="mt-4 flex items-center gap-2 sm:mt-6" aria-label="Китай, Корея, Япония">
              {["CN", "KR", "JP"].map((f) => (
                <span key={f} className="grid size-9 place-items-center sm:size-10 rounded-full border border-white/10 bg-white/[0.04] text-[11px] font-bold tracking-wider text-foreground/80">{f}</span>
              ))}
              <span className="ml-1 text-[13px] text-muted-foreground sm:ml-2 sm:text-sm">Проверенные площадки и аукционы</span>
            </div>
            <LeadButton title="Рассчитать стоимость под ключ" className="btn-accent mt-8 hidden h-12 items-center rounded-full px-6 text-sm font-semibold lg:inline-flex">
              Рассчитать стоимость
            </LeadButton>
          </div>
        </div>
        <dl className="grid grid-cols-2 gap-2 sm:gap-3 lg:col-span-7 lg:block">
          {rows.map((r) => (
            <div key={r.t} data-reveal="" className="flex flex-col justify-between gap-1 rounded-[1.25rem] bg-white/[0.04] p-4 lg:grid lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:gap-8 lg:rounded-none lg:border-t lg:border-white/[0.08] lg:bg-transparent lg:px-0 lg:py-9">
              <dt>
                <span className="tnum block text-[22px] leading-[1.15] font-semibold tracking-tight sm:text-[28px] lg:text-[clamp(2.1rem,3.6vw,3.4rem)] lg:leading-none lg:whitespace-nowrap">{r.v}</span>
                <span className="mt-1 block text-[13px] text-muted-foreground lg:mt-2 lg:text-sm lg:font-medium lg:text-accent">{r.t}</span>
              </dt>
              <dd className="hidden self-end text-[15px] leading-relaxed text-muted-foreground lg:block">{r.d}</dd>
            </div>
          ))}
        </dl>
      </div>
    </section>
  );
}
