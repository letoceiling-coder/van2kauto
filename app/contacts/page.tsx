import type { Metadata } from "next";
import { MessageCircle, PhoneCall, CalendarCheck, MapPin, Clock, Truck, FileCheck2 } from "lucide-react";
import { PageHero } from "@/components/site/PageHero";
import { Office } from "@/components/home/Office";
import { OpenNow } from "@/components/site/OpenNow";
import { LeadButton } from "@/components/site/Lead";
import { Reveal } from "@/components/motion/Reveal";
import { company } from "@/content/nav";

export const metadata: Metadata = { title: "Контакты — офис в Анапе", description: "Офис ВАН23АВТО в Анапе: выдача автомобилей, консультации и расчёт стоимости. Доставка в Новороссийск, Краснодар и по России." };

const ACTIONS = [
  { icon: MessageCircle, title: "Написать менеджеру", text: "Telegram или WhatsApp — пришлём варианты и смету в чат.", cta: "Написать", lead: "Написать менеджеру" },
  { icon: PhoneCall, title: "Заказать звонок", text: "Перезвоним в удобное время и ответим на вопросы по ввозу.", cta: "Перезвоните мне", lead: "Заказать обратный звонок" },
  { icon: CalendarCheck, title: "Записаться в офис", text: "Покажем примеры смет, отчёты осмотра и выданные машины.", cta: "Записаться", lead: "Запись в офис в Анапе" },
];

const CITIES = [
  { city: "Анапа", how: "Выдача в офисе или у вашего дома", time: "в день прибытия" },
  { city: "Новороссийск", how: "Автовоз или перегон с водителем", time: "+1 день" },
  { city: "Краснодар", how: "Автовоз до адреса, передача с актом", time: "+1–2 дня" },
  { city: "Вся Россия", how: "Автовоз или ж/д до вашего города", time: "по расчёту" },
];

export default function Page() {
  return (
    <>
      <PageHero eyebrow="Контакты" title="Офис и выдача в Анапе" text={`Менеджер по заявкам — ${company.manager}. Ответим в течение 15 минут в рабочее время.`} image="/media/generated/car-handover.webp" watermark="ANAPA">
        <div className="flex flex-wrap items-center gap-3">
          <OpenNow />
          <span className="inline-flex h-7 items-center gap-2 rounded-full bg-white/[0.06] px-3 text-xs text-foreground/80"><Clock className="size-3.5 text-accent" />{company.hours}</span>
        </div>
      </PageHero>

      <section data-component-id="contact-actions" className="mx-auto max-w-site px-4 sm:px-6 lg:px-8">
        <div className="grid gap-3 md:grid-cols-3">
          {ACTIONS.map(({ icon: Icon, title, text, cta, lead }, i) => (
            <Reveal key={title} delay={i * 0.06}>
              <LeadButton title={lead} className="group card relative grid h-full w-full grid-cols-[auto_1fr] items-start gap-x-4 overflow-hidden p-5 text-left md:flex md:flex-col md:p-6 transition hover:-translate-y-1 hover:border-accent/30">
                <span aria-hidden="true" className="absolute -top-16 -right-16 size-40 rounded-full bg-[radial-gradient(closest-side,rgb(235_17_29/0.18),transparent)] opacity-0 transition duration-500 group-hover:opacity-100" />
                <span className="row-span-3 grid size-12 place-items-center rounded-2xl bg-accent text-accent-foreground transition duration-300 group-hover:rotate-[-8deg] group-hover:scale-105"><Icon className="size-5" /></span>
                <span className="text-lg font-semibold tracking-tight md:mt-5 md:text-xl">{title}</span>
                <span className="mt-1 text-[14px] leading-relaxed text-muted-foreground md:mt-2 md:text-[15px]">{text}</span>
                <span className="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-accent md:mt-5">{cta} <span className="transition group-hover:translate-x-1">→</span></span>
              </LeadButton>
            </Reveal>
          ))}
        </div>
      </section>

      <section data-component-id="delivery-cities" className="mx-auto max-w-site px-4 pt-[var(--section-y)] sm:px-6 lg:px-8">
        <Reveal>
          <p className="inline-flex items-center gap-2.5 text-[11px] font-semibold tracking-[0.22em] text-accent uppercase"><span className="h-px w-6 bg-accent" />Где получить</p>
          <h2 className="text-headline mt-4 max-w-2xl">Передаём автомобиль там, где вам удобно</h2>
        </Reveal>
        <div className="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {CITIES.map((c, i) => (
            <Reveal key={c.city} delay={i * 0.05} className="card h-full p-5">
              <div className="flex items-center justify-between gap-2">
                <span className="inline-flex items-center gap-2 font-semibold"><MapPin className="size-4 text-accent" />{c.city}</span>
                <span className="rounded-full bg-white/[0.06] px-2.5 py-1 text-xs text-muted-foreground">{c.time}</span>
              </div>
              <p className="mt-3 flex items-start gap-2 text-[14px] text-muted-foreground"><Truck className="mt-0.5 size-4 shrink-0" />{c.how}</p>
            </Reveal>
          ))}
        </div>
      </section>

      <Office />

      <section className="mx-auto max-w-site px-4 sm:px-6 lg:px-8">
        <div className="card grid gap-6 p-6 text-sm sm:grid-cols-3 sm:p-8">
          <div className="flex gap-3"><FileCheck2 className="mt-0.5 size-5 shrink-0 text-accent" /><div><p className="text-muted-foreground">Сторона договора</p><p className="mt-1 text-base font-medium">{company.legal}</p></div></div>
          <div><p className="text-muted-foreground">Реквизиты</p><p className="mt-1 text-base font-medium">Публикуются после заполнения по документам компании</p></div>
          <div><p className="text-muted-foreground">Города выдачи</p><p className="mt-1 text-base font-medium">{company.cities.join(" · ")}</p></div>
        </div>
      </section>
    </>
  );
}
