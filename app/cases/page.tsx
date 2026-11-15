import type { Metadata } from "next";
import { PageHero } from "@/components/site/PageHero";
import { Cases } from "@/components/home/Cases";
import { Trust } from "@/components/home/Trust";
import { QuizBand } from "@/components/home/QuizBand";

export const metadata: Metadata = { title: "Выдачи клиентам", description: "Реальные выдачи автомобилей из Китая, Кореи и Японии в Анапе, Новороссийске и Краснодаре: модель, срок и итог по смете." };

export default function Page() {
  return (
    <>
      <PageHero eyebrow="Выдачи" title="Ключи уже у наших клиентов" text="Каждый кейс — модель, город, срок от договора до выдачи и итог относительно согласованной сметы." image="/media/generated/car-delivery-premium.webp" watermark="KEYS" />
      <Cases compact />
      <Trust />
      <QuizBand />
    </>
  );
}
