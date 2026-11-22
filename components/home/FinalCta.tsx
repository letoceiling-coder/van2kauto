import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight } from "lucide-react";
import { LeadButton } from "@/components/site/Lead";
import { asset } from "@/lib/utils";

/** Финальный экран как в референсе: тёмное фото авто, крупный заголовок, две кнопки. */
export function FinalCta() {
  return (
    <section data-component-id="final-cta" className="relative mx-auto max-w-site px-4 pb-[var(--section-y)] sm:px-6 lg:px-8">
      <div data-reveal="" className="relative isolate flex min-h-[460px] flex-col justify-end overflow-hidden rounded-[2rem] border border-white/[0.08] p-6 sm:min-h-[540px] sm:p-10 lg:p-14">
        <Image src={asset("/media/generated/hyundai-palisade-black.webp")} alt="" fill sizes="(min-width:1280px) 1240px, 100vw" className="-z-20 object-cover" />
        <div className="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgb(9_9_10/0.92)_0%,rgb(9_9_10/0.6)_45%,rgb(9_9_10/0.1)_100%),linear-gradient(0deg,rgb(9_9_10/0.85),transparent_55%)]" />
        <h2 className="text-display text-metal max-w-[14ch] pb-[0.06em]">Ваш следующий автомобиль ближе, чем кажется</h2>
        <p className="mt-5 max-w-lg text-[17px] leading-relaxed text-white/70">Подберём модель из Китая, Кореи или Японии, проверим до выкупа и привезём под ключ в Анапу — со сметой до оплаты.</p>
        <div className="mt-8 flex flex-wrap gap-3">
          <LeadButton title="Подобрать автомобиль" className="btn-accent shine inline-flex h-14 items-center gap-2 rounded-full px-7 font-semibold">
            Подобрать автомобиль <ArrowUpRight className="size-4" />
          </LeadButton>
          <Link href="/catalog" className="inline-flex h-14 items-center rounded-full border border-white/20 px-7 font-semibold transition hover:border-white/50">
            Смотреть каталог
          </Link>
        </div>
      </div>
    </section>
  );
}
