import type { Metadata } from "next";
import { CatalogView } from "@/components/car/CatalogView";
import { PageHero } from "@/components/site/PageHero";
import { cars, PRICE_DATE } from "@/content/cars";

export const metadata: Metadata = {
  title: "Каталог авто из Китая, Кореи и Японии — цены под ключ до Анапы",
  description: "Каталог автомобилей под заказ: новые и с пробегом из Китая, Кореи и Японии. Фильтры по стране, бюджету, кузову и двигателю, цена под ключ до Анапы.",
};

export default function Page() {
  return (
    <>
      <PageHero eyebrow={`Каталог · ${cars.length} моделей`} title="Каталог автомобилей под заказ" text={`Цены — предварительный ориентир под ключ до Анапы на ${PRICE_DATE}. Нужной машины нет в списке? Найдём на площадках по вашему запросу.`} image="/media/generated/auto-dark-landscape.webp" watermark="CATALOG" />
      <section className="mx-auto max-w-site px-4 pb-10 sm:px-6 lg:px-8">
        <CatalogView />
      </section>
    </>
  );
}
