import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight } from "lucide-react";
import { LeadButton } from "@/components/site/Lead";
import { asset } from "@/lib/utils";

/** Финальный экран как в референсе: тёмное фото авто, крупный заголовок, две кнопки. */
export function FinalCta() {
  return (
    <section data-component-id="final-cta" className="relative mx-auto max-w-site px-4 pb-[var(--section-y)] sm:px-6 lg:px-8">
      <div data-reveal="" className="relative isolate flex min-h-[380px] flex-col justify-end overflow-hidden rounded-[1.5rem] border border-white/[0.08] p-5 sm:min-h-[540px] sm:rounded-[2rem] sm:p-10 lg:p-14">
        <Image src={asset("/media/generated/hyundai-palisade-black.webp")} alt="" fill sizes="(min-width:1280px) 1240px, 100vw" className="-z-20 object-cover" />
        <div className="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgb(9_9_10/0.92)_0%,rgb(9_9_10/0.6)_45%,rgb(9_9_10/0.1)_100%),linear-gradient(0deg,rgb(9_9_10/0.85),transparent_55%)]" />
        <h2 className="text-display max-w-[14ch]">Ваш следующий автомобиль ближе, чем кажется</h2>
        <p className="mt-3 max-w-lg text-[15px] leading-[1.45] text-white/70 sm:mt-5 sm:text-[17px]">Подберём модель из Китая, Кореи или Японии, проверим до выкупа и привезём под ключ в Анапу — со сметой до оплаты.</p>
        <div className="mt-6 flex flex-col gap-2 sm:mt-8 sm:flex-row sm:gap-3">
          <LeadButton title="Подобрать автомобиль" className="btn-accent inline-flex h-12 items-center justify-center gap-2 rounded-full px-7 text-[15px] font-semibold sm:h-14">
            Подобрать автомобиль <ArrowUpRight className="size-4" />
          </LeadButton>
          <Link href="/catalog" className="press inline-flex h-12 items-center justify-center rounded-full border border-white/20 px-7 text-[15px] font-semibold transition hover:border-white/50 sm:h-14">
            Смотреть каталог
          </Link>
        </div>
      </div>
    </section>
  );
}
