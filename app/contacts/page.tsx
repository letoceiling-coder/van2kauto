import type { Metadata } from "next";
import { PageHero } from "@/components/site/PageHero";
import { Office } from "@/components/home/Office";
import { company } from "@/content/nav";

export const metadata: Metadata = { title: "Контакты — офис в Анапе", description: "Офис VAN2KAUTO в Анапе: выдача автомобилей, консультации и расчёт стоимости. Доставка в Новороссийск, Краснодар и по России." };

export default function Page() {
  return (
    <>
      <PageHero eyebrow="Контакты" title="Офис и выдача в Анапе" text={`${company.hours}. Менеджер по заявкам — ${company.manager}. Доставка в Новороссийск, Краснодар и по всей России.`} image="/media/generated/car-handover.webp" watermark="ANAPA" />
      <Office />
      <section className="mx-auto max-w-site px-4 sm:px-6 lg:px-8">
        <div className="card grid gap-6 p-6 text-sm sm:grid-cols-3 sm:p-8">
          <div><p className="text-muted-foreground">Сторона договора</p><p className="mt-1 text-base font-medium">{company.legal}</p></div>
          <div><p className="text-muted-foreground">Реквизиты</p><p className="mt-1 text-base font-medium">Публикуются после заполнения по документам компании</p></div>
          <div><p className="text-muted-foreground">Города выдачи</p><p className="mt-1 text-base font-medium">{company.cities.join(" · ")}</p></div>
        </div>
      </section>
    </>
  );
}
