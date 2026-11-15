import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight } from "lucide-react";
import { Reveal } from "@/components/motion/Reveal";
import { asset } from "@/lib/utils";

export function QuizBand() {
  return (
    <section className="relative mx-auto max-w-site px-4 sm:px-6 lg:px-8">
      <Reveal>
        <div className="relative isolate overflow-hidden rounded-[2rem] border border-white/[0.08] px-6 py-14 sm:px-12 sm:py-20">
          <Image src={asset("/media/generated/auto-dark-landscape.webp")} alt="" fill sizes="100vw" className="-z-10 object-cover" />
          <div className="absolute inset-0 -z-10 bg-gradient-to-r from-[#0a0c0e] via-[#0a0c0e]/80 to-[#0a0c0e]/20" />
          <div aria-hidden="true" className="absolute -bottom-24 -left-24 -z-10 size-80 rounded-full bg-accent/20 blur-[100px]" />
          <p className="inline-flex items-center gap-2.5 text-[11px] font-semibold tracking-[0.22em] text-accent uppercase"><span className="h-px w-6 bg-accent" />Подбор за 2 минуты</p>
          <h2 className="text-headline mt-4 max-w-xl">Не знаете, что выбрать? Начните с бюджета</h2>
          <p className="mt-4 max-w-md text-[17px] text-white/70">Четыре вопроса — и менеджер пришлёт 3 варианта с ценой под ключ до вашего города.</p>
          <Link href="/selection" className="group btn-accent shine mt-8 inline-flex h-14 items-center gap-3 rounded-full pr-2 pl-7 font-semibold">
            Подобрать авто
            <span className="grid size-10 place-items-center rounded-full bg-accent-foreground text-accent transition group-hover:rotate-45"><ArrowUpRight className="size-4" /></span>
          </Link>
        </div>
      </Reveal>
    </section>
  );
}
