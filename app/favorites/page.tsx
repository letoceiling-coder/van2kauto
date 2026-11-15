import type { Metadata } from "next";
import { FavoritesView } from "@/components/car/FavoritesView";
import { PageHero } from "@/components/site/PageHero";

export const metadata: Metadata = { title: "Избранное", robots: { index: false } };

export default function Page() {
  return (
    <>
      <PageHero eyebrow="Избранное" title="Ваш личный гараж" text="Сравнивайте машины по цене под ключ, сроку и характеристикам и отправляйте подборку менеджеру одним запросом." image="/media/generated/premium-car-dark.webp" watermark="GARAGE" />
      <section className="mx-auto max-w-site px-4 sm:px-6 lg:px-8">
        <FavoritesView />
      </section>
    </>
  );
}
