import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight } from "lucide-react";
import { Reveal } from "@/components/motion/Reveal";
import { SectionHead } from "./SectionHead";
import { cars, countries, formatPrice, type Country } from "@/content/cars";
import { asset } from "@/lib/utils";

const META: Record<Country, { image: string; text: string; tags: string[] }> = {
  china: { image: "/media/generated/china-auto-premium.webp", text: "Новые электромобили, гибриды и китайские версии VW, Audi, BMW по ценам внутреннего рынка.", tags: ["Che168", "Новые", "EV и EREV"] },
  korea: { image: "/media/generated/korea-auto-premium.webp", text: "Кроссоверы и седаны с прозрачной историей: Encar, страховые случаи, сервисная книжка.", tags: ["Encar", "С пробегом", "Левый руль"] },
  japan: { image: "/media/generated/japan-auto-premium.webp", text: "Аукционные лоты с оценкой и аукционным листом: минивэны, кей-кары, легендарные внедорожники.", tags: ["Аукционы", "Оценка 4+", "Правый руль"] },
};

export function Countries() {
  return (
    <section data-component-id="countries" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <SectionHead watermark="ASIA" eyebrow="Три направления" title="Выберите страну — покажем, что реально привезти" text="У каждой страны свои площадки, проверки и логистика. Мы работаем со всеми тремя с первого дня." />
      <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {(Object.keys(countries) as Country[]).map((k, i) => {
          const list = cars.filter((c) => c.country === k);
          const min = Math.min(...list.map((c) => c.price));
          return (
            <Reveal key={k} delay={i * 0.08}>
              <Link href={countries[k].href} className="press-soft group relative isolate flex h-[260px] flex-col justify-end overflow-hidden rounded-[1.5rem] border border-white/[0.08] p-5 sm:h-[520px] sm:rounded-[2rem] sm:p-7">
                <Image src={asset(META[k].image)} alt={`Автомобиль ${countries[k].from}`} fill sizes="(min-width:1024px) 33vw, 100vw" className="-z-10 object-cover transition duration-[1.2s] ease-out group-hover:scale-[1.07]" />
                <div className="absolute inset-0 -z-10 bg-gradient-to-t from-[#09090a] via-[#09090a]/55 to-transparent" />
                <span className="absolute top-6 left-6 hidden font-display text-7xl font-bold sm:block tracking-tight text-white/90 mix-blend-overlay sm:text-8xl">{countries[k].flag}</span>
                <span className="absolute top-6 right-6 grid size-12 place-items-center rounded-full bg-white/10 transition duration-300 group-hover:rotate-45 group-hover:bg-accent group-hover:text-accent-foreground">
                  <ArrowUpRight className="size-5" />
                </span>
                <div className="flex flex-wrap gap-1.5">
                  {META[k].tags.map((t) => (
                    <span key={t} className="rounded-full border border-white/15 bg-black/30 px-2.5 py-1 text-[11px] text-white/80">{t}</span>
                  ))}
                </div>
                <h3 className="mt-3 text-[22px] font-semibold tracking-tight sm:mt-4 sm:text-3xl">Авто {countries[k].from}</h3>
                <p className="mt-1.5 line-clamp-2 max-w-sm text-[14px] text-white/65 sm:mt-2 sm:line-clamp-none sm:text-[15px]">{META[k].text}</p>
                <div className="mt-5 flex items-center justify-between border-t border-white/10 pt-4 text-sm">
                  <span className="text-white/60">{list.length ? `${list.length} моделей · ` : "Подбор под заказ · "}{countries[k].city}</span>
                  {list.length > 0 && <span className="tnum font-semibold">от {formatPrice(min)}</span>}
                </div>
              </Link>
            </Reveal>
          );
        })}
      </div>
    </section>
  );
}
