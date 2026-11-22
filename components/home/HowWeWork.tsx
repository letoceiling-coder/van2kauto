import Image from "next/image";
import { FileSignature, Search, ShieldCheck, Ship, Stamp, KeyRound } from "lucide-react";
import { process } from "@/content/home";
import { asset } from "@/lib/utils";

const ICONS = [Search, ShieldCheck, FileSignature, Ship, Stamp, KeyRound];

/**
 * «Как мы работаем» по референсу: огромный заголовок на тёмном фото, поверх при прокрутке горизонтально
 * проезжают карточки этапов. На десктопе — закреплённый экран и CSS-анимация по прокрутке (animation-timeline),
 * без JS и без рывков. На телефоне и без поддержки — обычная горизонтальная лента с прокруткой пальцем.
 */
export function HowWeWork() {
  return (
    <section data-component-id="how" id="how" aria-labelledby="how-title" className="how-pin relative">
      <div className="how-stage relative isolate overflow-hidden">
        <Image src={asset("/media/generated/toyota-camry-dark.webp")} alt="" fill sizes="100vw" className="-z-20 object-cover opacity-55" />
        <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,#09090a_0%,rgb(9_9_10/0.55)_30%,rgb(9_9_10/0.6)_70%,#09090a_100%)]" />
        <div className="mx-auto flex h-full max-w-site flex-col px-4 pt-[var(--section-y)] pb-5 sm:px-6 lg:px-8 lg:pb-[var(--section-y)]">
          <p className="inline-flex items-center gap-2.5 text-[11px] font-semibold tracking-[0.22em] text-accent uppercase">
            <span className="h-px w-6 bg-gradient-to-r from-transparent to-accent" />6 шагов · 25–50 дней
          </p>
          <h2 id="how-title" className="how-title mt-2 text-[30px] leading-[1.1] font-semibold tracking-[-0.02em] sm:text-[44px] lg:mt-4 lg:w-max lg:pb-[0.08em] lg:text-[clamp(3.2rem,12vw,11rem)] lg:leading-[0.9] lg:tracking-[-0.05em] lg:whitespace-nowrap">
            Как мы работаем
          </h2>
        </div>
        <ol className="how-track no-scrollbar flex snap-x snap-mandatory scroll-px-4 gap-2 overflow-x-auto overscroll-x-contain px-4 pb-[var(--section-y)] sm:gap-3 sm:px-6 lg:gap-4 lg:px-8">
          {process.map((s, i) => {
            const Icon = ICONS[i] ?? Search;
            return (
              <li key={s.title} className="relative flex w-[76vw] max-w-[340px] shrink-0 snap-start flex-col rounded-[1.25rem] border border-white/10 bg-black/65 p-5 sm:p-6 md:bg-black/45 md:backdrop-blur-xl">
                <div className="flex items-center justify-between">
                  <span className="grid size-11 place-items-center rounded-2xl bg-accent/15 text-accent"><Icon className="size-5" /></span>
                  <span className="tnum text-[13px] text-white/40">{String(i + 1).padStart(2, "0")}</span>
                </div>
                <h3 className="mt-6 text-[17px] font-semibold sm:mt-8 sm:text-xl">{s.title}</h3>
                <p className="mt-1.5 text-[15px] leading-[1.45] text-white/65 sm:mt-2">{s.text}</p>
                <p className="mt-auto pt-6 text-sm font-medium text-accent">{s.time}</p>
              </li>
            );
          })}
        </ol>
      </div>
    </section>
  );
}
