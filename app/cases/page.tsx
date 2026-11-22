import type { Metadata } from "next";
import Link from "next/link";
import { PageHero } from "@/components/site/PageHero";
import { LeadButton } from "@/components/site/Lead";

export const metadata: Metadata = { title: "Выдачи клиентам", description: "Выдачи автомобилей из Китая, Кореи и Японии в Анапе: модель, срок и итог по смете.", robots: { index: false } };

/** Реальные выдачи публикуются после первых сделок. Демо-истории здесь не показываем — выдавать выдумку за факт нельзя. */
export default function Page() {
  return (
    <>
      <PageHero eyebrow="Выдачи" title="Здесь будут реальные выдачи" text="Мы публикуем только настоящие сделки: модель, город, срок от договора до ключей и итог относительно сметы — с согласия клиента." image="/media/generated/car-delivery-premium.webp" watermark="KEYS" />
      <section className="mx-auto max-w-site px-4 pb-[var(--section-y)] sm:px-6 lg:px-8">
        <div className="card flex flex-col items-start gap-4 p-5 sm:p-8">
          <p className="text-title font-semibold">Хотите стать первым кейсом?</p>
          <p className="max-w-xl text-muted-foreground">Подберём автомобиль из топа популярных моделей и проведём сделку прозрачно — со сметой до оплаты и статусами на каждом этапе.</p>
          <div className="flex flex-col gap-2 sm:flex-row">
            <LeadButton title="Подобрать автомобиль" className="btn-accent inline-flex h-12 items-center justify-center rounded-full px-6 font-semibold">Подобрать автомобиль</LeadButton>
            <Link href="/catalog" className="press inline-flex h-12 items-center justify-center rounded-full border border-white/15 px-6 font-semibold">Смотреть каталог</Link>
          </div>
        </div>
      </section>
    </>
  );
}
