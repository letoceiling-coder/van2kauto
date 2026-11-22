"use client";

import Image from "next/image";
import { useRef } from "react";
import { motion, useScroll, useTransform } from "motion/react";
import { SectionHead } from "./SectionHead";
import { process } from "@/content/home";
import { asset, small } from "@/lib/utils";

const IMAGES = ["/media/generated/car-selection.webp", "/media/generated/car-inspection.webp", "/media/generated/contract-signing.webp", "/media/generated/japan-auction-car.webp", "/media/generated/delivery-route.webp", "/media/generated/car-handover.webp"];

export function Process() {
  const ref = useRef<HTMLOListElement>(null);
  const { scrollYProgress } = useScroll({ target: ref, offset: ["start 70%", "end 60%"] });
  const h = useTransform(scrollYProgress, [0, 1], ["0%", "100%"]);
  return (
    <section data-component-id="process" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <SectionHead watermark="ROUTE" eyebrow="Как заказать" title="Шесть шагов от заявки до ключей" text="Каждый этап с понятным сроком и ответственным. Вы всегда знаете, где машина и что будет дальше." />
      <ol ref={ref} className="relative grid grid-cols-1 gap-4 md:grid-cols-2 lg:gap-x-10">
        <div aria-hidden="true" className="absolute top-0 bottom-0 left-[27px] w-px bg-white/[0.08] md:hidden">
          <motion.div style={{ height: h }} className="w-px bg-accent" />
        </div>
        {process.map((s, i) => (
          <li
            key={s.title}
            data-reveal=""
            style={{ "--reveal-delay": `${(i % 2) * 0.08}s` } as React.CSSProperties}
            className="group card relative flex gap-5 overflow-hidden p-4 pr-5 sm:p-5"
          >
            <span className="relative z-10 grid size-14 shrink-0 place-items-center rounded-2xl border border-white/10 bg-background font-display text-xl font-semibold text-accent transition group-hover:bg-accent group-hover:text-accent-foreground">
              {String(i + 1).padStart(2, "0")}
            </span>
            <div className="min-w-0 flex-1">
              <div className="flex flex-wrap items-center justify-between gap-2">
                <h3 className="text-xl font-semibold tracking-tight">{s.title}</h3>
                <span className="rounded-full bg-white/[0.06] px-2.5 py-1 text-xs text-muted-foreground">{s.time}</span>
              </div>
              <p className="mt-2 text-[15px] leading-relaxed text-muted-foreground">{s.text}</p>
            </div>
            <div className="relative hidden w-28 shrink-0 overflow-hidden rounded-xl sm:block">
              <Image src={asset(small(IMAGES[i]))} alt="" fill sizes="112px" className="object-cover opacity-70 grayscale transition duration-500 group-hover:scale-110 group-hover:opacity-100 group-hover:grayscale-0" />
            </div>
          </li>
        ))}
      </ol>
    </section>
  );
}
