import type { Metadata } from "next";
import { PageHero } from "@/components/site/PageHero";
import { Quiz } from "@/components/site/Quiz";

export const metadata: Metadata = {
  title: "Подбор авто под бюджет",
  description: "Квиз подбора автомобиля из Китая, Кореи или Японии: задача, страна, бюджет и город — менеджер пришлёт варианты с ценой под ключ.",
};

export default function Page() {
  return (
    <>
      <PageHero eyebrow="Подбор за 2 минуты" title="Найдём автомобиль под ваш бюджет" text="Четыре шага — и менеджер пришлёт до трёх вариантов с ценой под ключ до вашего города. Ответы сохраняются, можно вернуться назад." image="/media/generated/car-selection.webp" watermark="MATCH" />
      <section className="mx-auto max-w-site px-4 sm:px-6 lg:px-8">
        <Quiz />
      </section>
    </>
  );
}
