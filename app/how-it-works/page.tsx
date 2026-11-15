import type { Metadata } from "next";
import { PageHero } from "@/components/site/PageHero";
import { Process } from "@/components/home/Process";
import { PriceBreakdown } from "@/components/home/PriceBreakdown";
import { Faq } from "@/components/home/Faq";
import { QuizBand } from "@/components/home/QuizBand";

export const metadata: Metadata = { title: "Как заказать авто под ключ", description: "Подбор, осмотр, смета, договор, выкуп, логистика, документы и выдача — как проходит заказ автомобиля из Азии." };

export default function Page() {
  return (
    <>
      <PageHero eyebrow="Как это работает" title="От заявки до ключей — прозрачно на каждом шаге" text="Подбор, осмотр, смета, договор, выкуп, логистика, документы и выдача. Один менеджер и понятные статусы." image="/media/generated/contract-signing.webp" watermark="STEPS" />
      <Process />
      <PriceBreakdown />
      <QuizBand />
      <Faq />
    </>
  );
}
