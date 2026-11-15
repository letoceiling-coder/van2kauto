import type { Metadata } from "next";
import { FavoritesView } from "@/components/car/FavoritesView";

export const metadata: Metadata = { title: "Избранное", robots: { index: false } };

export default function Page() {
  return (
    <section className="mx-auto max-w-site px-4 pt-28 sm:px-6 sm:pt-36 lg:px-8">
      <p className="inline-flex items-center gap-2.5 text-[11px] font-semibold tracking-[0.22em] text-accent uppercase"><span className="h-px w-6 bg-accent" />Избранное</p>
      <h1 className="text-display mt-5 font-semibold">Ваши автомобили</h1>
      <FavoritesView />
    </section>
  );
}
