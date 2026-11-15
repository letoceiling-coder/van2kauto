import Image from "next/image";
import Link from "next/link";
import { ArrowUpRight, MapPin, Clock } from "lucide-react";
import { SectionHead } from "./SectionHead";
import { cases } from "@/content/home";
import { asset } from "@/lib/utils";

export function Cases({ compact }: { compact?: boolean }) {
  return (
    <section data-component-id="cases" className="relative py-[var(--section-y)]">
      <div className="mx-auto max-w-site px-4 sm:px-6 lg:px-8">
        <SectionHead
          watermark="KEYS"
          eyebrow="Выдачи"
          title="Реальные машины — реальные сроки"
          text="Модель, город, срок от договора до ключей и итог относительно сметы."
          aside={!compact ? <Link href="/cases" className="inline-flex items-center gap-2 text-sm font-semibold text-foreground/80 underline-offset-4 hover:text-accent hover:underline">Все выдачи <ArrowUpRight className="size-4" /></Link> : undefined}
        />
      </div>
      <div className="no-scrollbar flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-px-4 px-4 pb-4 sm:scroll-px-6 sm:px-6 lg:scroll-px-[max(2rem,calc((100vw-80rem)/2+2rem))] lg:px-[max(2rem,calc((100vw-80rem)/2+2rem))]">
        {cases.map((c) => (
          <article key={c.slug} className="group relative w-[82vw] shrink-0 snap-start overflow-hidden rounded-[1.75rem] border border-white/[0.08] sm:w-[420px]">
            <div className="relative aspect-[4/5]">
              <Image src={asset(c.image)} alt={`${c.car} — выдача клиенту, ${c.city}`} fill sizes="(min-width:640px) 420px, 82vw" className="object-cover transition duration-1000 group-hover:scale-105" />
              <div className="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent" />
            </div>
            <div className="absolute inset-x-0 bottom-0 p-6">
              <div className="flex flex-wrap gap-2 text-xs">
                <span className="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1"><MapPin className="size-3.5 text-accent" />{c.city}</span>
                <span className="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1"><Clock className="size-3.5 text-accent" />{c.days} дней</span>
                <span className="rounded-full bg-accent px-2.5 py-1 font-semibold text-accent-foreground">{c.budget}</span>
              </div>
              <h3 className="mt-4 text-2xl font-semibold tracking-tight">{c.car}</h3>
              <p className="mt-2 text-sm leading-relaxed text-white/65">{c.text}</p>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}
