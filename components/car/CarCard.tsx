import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight, Gauge, Fuel, Calendar } from "lucide-react";
import { FavButton } from "./FavButton";
import { type Car, carName, countries, formatKm, labels, photos, PRICE_CITY, priceText } from "@/content/cars";
import { asset, cn, small } from "@/lib/utils";

export function CountryTag({ country, className }: { country: Car["country"]; className?: string }) {
  return (
    <span className={cn("inline-flex h-7 items-center gap-1.5 rounded-full bg-black/65 px-2.5 text-[11px] font-semibold tracking-wide text-white", className)}>
      <span className="text-accent">{countries[country].flag}</span>
      {countries[country].label}
    </span>
  );
}

export function CarCard({ car, priority, className }: { car: Car; priority?: boolean; className?: string }) {
  const [main, second] = photos(car);
  return (
    <Link
      href={`/catalog/${car.slug}`}
      className={cn(
        "group card card-glow relative flex flex-col overflow-hidden p-2 transition duration-300 active:scale-[0.985] hover:-translate-y-1 hover:border-white/15 hover:shadow-[0_30px_80px_-30px_rgb(198_244_71/0.25)]",
        className,
      )}
    >
      <div className="relative aspect-[4/3] overflow-hidden rounded-[1.1rem] bg-surface-2">
        <Image src={asset(small(main.src))} alt={main.alt} fill priority={priority} sizes="(min-width:1280px) 25vw, (min-width:768px) 45vw, 92vw" className="object-cover transition duration-700 ease-out group-hover:scale-[1.06]" />
        {second && (
          <Image src={asset(small(second.src))} alt="" aria-hidden="true" fill sizes="(min-width:1280px) 25vw, (min-width:768px) 45vw, 92vw" className="object-cover opacity-0 transition duration-700 group-hover:opacity-100 max-md:hidden" />
        )}
        <div className="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-black/20" />
        <div className="absolute top-3 left-3 flex flex-wrap gap-1.5">
          <CountryTag country={car.country} />
          {car.badge && <span className="inline-flex h-7 items-center rounded-full bg-accent px-2.5 text-[11px] font-bold text-accent-foreground">{car.badge}</span>}
          {car.utilizationOk && <span className="inline-flex h-7 items-center rounded-full bg-black/65 px-2.5 text-[11px] font-semibold text-accent">Проходной</span>}
        </div>
        <FavButton slug={car.slug} className="absolute top-2.5 right-2.5 size-10 bg-black/60 text-white hover:bg-black/65" />
        <span className="absolute bottom-3 left-3 inline-flex h-7 items-center gap-1.5 rounded-full bg-black/65 px-2.5 text-[11px] font-medium text-white/90">
          <span className={cn("size-1.5 rounded-full", car.status === "auction" ? "bg-amber-300" : "anim-pulse bg-accent")} />
          {labels.status[car.status]}
        </span>
      </div>
      <div className="flex flex-1 flex-col px-3 pt-4 pb-3">
        <div className="flex items-start justify-between gap-3">
          <div className="min-w-0">
            <h3 className="truncate text-lg leading-tight font-semibold tracking-tight">{carName(car)}</h3>
            <p className="mt-1 truncate text-sm text-muted-foreground">{car.trim}</p>
          </div>
          <span className="grid size-9 shrink-0 place-items-center rounded-full border border-white/10 transition duration-300 group-hover:rotate-45 group-hover:border-accent group-hover:bg-accent group-hover:text-accent-foreground">
            <ArrowUpRight className="size-4" />
          </span>
        </div>
        <dl className="mt-4 grid grid-cols-3 gap-2 text-[13px] text-foreground/80">
          <div className="flex items-center gap-1.5"><Calendar className="size-3.5 text-muted-foreground" /><dt className="sr-only">Год</dt><dd>{car.year}</dd></div>
          <div className="flex items-center gap-1.5"><Gauge className="size-3.5 text-muted-foreground" /><dt className="sr-only">Пробег</dt><dd className="truncate">{formatKm(car.mileage)}</dd></div>
          <div className="flex items-center gap-1.5"><Fuel className="size-3.5 text-muted-foreground" /><dt className="sr-only">Двигатель</dt><dd className="truncate">{labels.engine[car.engine].replace("Последовательный гибрид", "EREV")}</dd></div>
        </dl>
        <div className="mt-4 flex items-end justify-between gap-2 border-t border-white/[0.06] pt-4">
          <div>
            <p className="tnum text-[1.4rem] leading-none font-semibold tracking-tight">{priceText(car)}</p>
            <p className="mt-1.5 text-xs text-muted-foreground">{car.priceFrom ? "Точная цена — по запросу" : `Предварительно до ${PRICE_CITY}`}</p>
          </div>
          {car.power > 0 && <span className="text-xs text-muted-foreground">{car.power} л.с.</span>}
        </div>
      </div>
    </Link>
  );
}
