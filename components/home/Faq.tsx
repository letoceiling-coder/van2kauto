"use client";

import { useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { Plus } from "lucide-react";
import { SectionHead } from "./SectionHead";
import { LeadButton } from "@/components/site/Lead";
import { faq } from "@/content/home";
import { cn } from "@/lib/utils";

export function Faq() {
  const [open, setOpen] = useState<number | null>(0);
  return (
    <section data-component-id="faq" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <div className="grid grid-cols-1 gap-10 lg:grid-cols-12">
        <div className="lg:col-span-4">
          <SectionHead eyebrow="Вопросы" title="Отвечаем до того, как вы спросите" className="mb-6" />
          <div className="card hidden p-6 lg:block">
            <p className="font-semibold">Не нашли ответ?</p>
            <p className="mt-2 text-sm text-muted-foreground">Руслан ответит в Telegram или перезвонит в удобное время.</p>
            <LeadButton title="Задать вопрос менеджеру" className="btn-accent mt-5 h-11 w-full rounded-full text-sm font-semibold">Задать вопрос</LeadButton>
          </div>
        </div>
        <ul className="divide-y divide-white/[0.07] border-y border-white/[0.07] lg:col-span-8">
          {faq.map((f, i) => {
            const on = open === i;
            return (
              <li key={f.q}>
                <h3>
                  <button onClick={() => setOpen(on ? null : i)} aria-expanded={on} className="flex min-h-14 w-full items-center justify-between gap-4 py-4 text-left text-[17px] font-medium transition hover:text-accent sm:gap-6 sm:py-6 sm:text-xl">
                    {f.q}
                    <span className={cn("grid size-9 shrink-0 place-items-center rounded-full border border-white/12 transition duration-300", on && "rotate-45 border-accent bg-accent text-accent-foreground")}>
                      <Plus className="size-4" />
                    </span>
                  </button>
                </h3>
                <AnimatePresence initial={false}>
                  {on && (
                    <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} transition={{ duration: 0.3, ease: [0.22, 1, 0.36, 1] }} className="overflow-hidden">
                      <p className="max-w-2xl pb-5 text-[15px] leading-[1.5] text-muted-foreground sm:pb-6 sm:text-[16px]">{f.a}</p>
                    </motion.div>
                  )}
                </AnimatePresence>
              </li>
            );
          })}
        </ul>
      </div>
    </section>
  );
}
