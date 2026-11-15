"use client";

import Link from "next/link";
import { Heart } from "lucide-react";
import { CarCard } from "./CarCard";
import { useFavorites } from "@/components/site/favorites";
import { LeadButton } from "@/components/site/Lead";
import { cars, carName } from "@/content/cars";

export function FavoritesView() {
  const { list } = useFavorites();
  const items = cars.filter((c) => list.includes(c.slug));
  if (!items.length)
    return (
      <div className="card mt-10 grid place-items-center px-6 py-20 text-center">
        <div className="grid size-16 place-items-center rounded-full bg-white/5"><Heart className="size-7 text-accent" /></div>
        <p className="mt-6 text-2xl font-semibold tracking-tight">Пока пусто</p>
        <p className="mt-2 max-w-sm text-muted-foreground">Нажмите на сердечко в карточке автомобиля, чтобы сравнить варианты и отправить их менеджеру одним запросом.</p>
        <Link href="/catalog" className="btn-accent mt-6 inline-flex h-12 items-center rounded-full px-6 text-sm font-semibold">Открыть каталог</Link>
      </div>
    );
  return (
    <>
      <div className="mt-8 flex flex-wrap items-center justify-between gap-4">
        <p className="text-muted-foreground">{items.length} в избранном · статус и цена актуальны на сегодня</p>
        <LeadButton context={items.map(carName).join(", ")} title="Сравнить и посчитать избранное" className="btn-accent h-12 rounded-full px-6 text-sm font-semibold">Отправить менеджеру</LeadButton>
      </div>
      <div className="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {items.map((c) => <CarCard key={c.slug} car={c} />)}
      </div>
    </>
  );
}
