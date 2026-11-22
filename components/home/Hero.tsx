"use client";

import Image from "next/image";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useRef, useState } from "react";
import { motion, useReducedMotion, useScroll, useTransform } from "motion/react";
import { ArrowUpRight, Search, ChevronDown } from "lucide-react";
import { useLead } from "@/components/site/Lead";
import { asset } from "@/lib/utils";
import { useMedia } from "@/lib/useMedia";
import { Counter } from "@/components/motion/Counter";

const POPULAR = ["Changan Q05", "Tayron", "Jimny", "BYD Seagull", "Freed", "BMW 3"];

export function Hero({ count }: { count: number }) {
  const ref = useRef<HTMLElement>(null);
  const reduce = useReducedMotion();
  const { scrollYProgress } = useScroll({ target: ref, offset: ["start start", "end start"] });
  // Параллакс только на десктопе с мышью — на телефонах он даёт рывки при скролле.
  const desktop = useMedia("(min-width: 1024px) and (pointer: fine)");
  const y = useTransform(scrollYProgress, [0, 1], ["0%", reduce || !desktop ? "0%" : "18%"]);
  const fade = useTransform(scrollYProgress, [0, 0.7], [1, 0]);
  const router = useRouter();
  const lead = useLead();
  const [q, setQ] = useState("");

  const words = ["Авто", "из", "Китая,", "Кореи", "и", "Японии", "под", "заказ"];

  return (
    <section ref={ref} data-component-id="hero" className="relative isolate flex min-h-[86svh] flex-col overflow-hidden lg:min-h-[100svh]">
      <motion.div style={{ y }} className="absolute inset-0 -z-10">
        {/* На телефоне — лёгкое фото (быстрый LCP); видео грузится только на десктопе с мышью. */}
        <Image src={asset("/media/generated/premium-car-dark.webp")} alt="" fill priority fetchPriority="high" sizes="100vw" className="scale-105 object-cover" />
        {desktop && <video
          className="absolute inset-0 size-full scale-105 object-cover"
          src={asset("/media/generated/premium-car-dark-loop.mp4")}
          poster={asset("/media/generated/premium-car-dark.webp")}
          autoPlay
          muted
          loop
          playsInline
          preload="metadata"
          aria-hidden="true"
        />}
      </motion.div>
      <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgb(9_9_10/0.6)_0%,rgb(9_9_10/0.25)_35%,rgb(9_9_10/0.88)_78%,#09090a_100%)]" />
      <div className="absolute inset-0 -z-10 bg-[radial-gradient(80%_60%_at_15%_60%,rgb(9_9_10/0.82),transparent_70%)]" />
      <div className="absolute inset-x-0 bottom-0 -z-10 h-px bg-[linear-gradient(90deg,transparent,rgb(235_17_29/0.6),transparent)]" />

      {/* Маршрут Азия → Анапа: тонкие линии поверх видео */}
      <svg aria-hidden="true" className="pointer-events-none absolute right-0 bottom-[18%] -z-10 hidden h-[60%] w-[55%] opacity-60 lg:block" viewBox="0 0 600 400" fill="none">
        <path d="M580 60 C 420 40, 360 200, 200 220 S 40 330, 10 360" stroke="url(#g1)" strokeWidth="1.2" className="anim-dash" />
        <path d="M590 150 C 470 150, 380 260, 260 270 S 80 340, 10 360" stroke="url(#g1)" strokeWidth="1" className="anim-dash" style={{ animationDelay: "-6s" }} />
        <path d="M560 260 C 450 290, 330 320, 10 360" stroke="url(#g1)" strokeWidth="0.8" className="anim-dash" style={{ animationDelay: "-11s" }} />
        <circle cx="10" cy="360" r="4" fill="#eb111d" />
        <circle cx="10" cy="360" r="12" stroke="#eb111d" strokeOpacity="0.4" />
        <defs>
          <linearGradient id="g1" x1="600" y1="0" x2="0" y2="400" gradientUnits="userSpaceOnUse">
            <stop stopColor="#eb111d" stopOpacity="0" />
            <stop offset="1" stopColor="#eb111d" stopOpacity="0.9" />
          </linearGradient>
        </defs>
      </svg>

      <motion.div style={desktop ? { opacity: fade } : undefined} className="mx-auto flex w-full max-w-site flex-1 flex-col justify-end px-4 pt-28 pb-6 sm:px-6 lg:px-8 lg:pt-32 lg:pb-16">
        <div className="grid grid-cols-1 items-end gap-10 lg:grid-cols-12">
          <div className="lg:col-span-8">
            <p className="hero-in inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/[0.06] py-1.5 pr-3.5 pl-1.5 text-xs font-medium text-white/85">
              <span className="rounded-full bg-accent px-2 py-0.5 text-[11px] font-semibold tracking-wide text-accent-foreground uppercase">Анапа</span>
              Привоз авто из-за рубежа под ключ
            </p>
            <h1 className="text-display mt-4 max-w-[15ch] font-semibold sm:mt-6" aria-label="Авто из Китая, Кореи и Японии под заказ">
              {words.map((w, i) => (
                <span key={i} className="hero-word" aria-hidden="true">
                  <span className={i >= 2 && i <= 5 && w !== "и" ? "text-accent" : "text-metal"} style={{ "--d": `${0.08 + i * 0.05}s` } as React.CSSProperties}>
                    {w}&nbsp;
                  </span>
                </span>
              ))}
            </h1>
            <p style={{ "--d": "0.45s" } as React.CSSProperties} className="hero-in mt-3 max-w-xl text-[15px] leading-[1.45] text-white/70 sm:mt-5 sm:text-lg">
              Подбираем, проверяем и привозим под ключ. Смета по статьям до оплаты, один менеджер и статус на каждом этапе.
            </p>

            <form
              style={{ "--d": "0.55s" } as React.CSSProperties}
              onSubmit={(e) => {
                e.preventDefault();
                router.push(`/catalog${q.trim() ? `?q=${encodeURIComponent(q.trim())}` : ""}`);
              }}
              role="search"
              className="hero-in mt-5 flex max-w-xl items-center gap-2 rounded-full border border-white/12 bg-black/60 p-1 pl-4 transition-[border-color] focus-within:border-accent/60 sm:mt-8 sm:p-1.5 sm:pl-5 md:bg-black/35 md:backdrop-blur-xl"
            >
              <Search className="size-5 shrink-0 text-white/50" />
              <label htmlFor="hero-q" className="sr-only">Марка или модель</label>
              <input id="hero-q" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Марка или модель" enterKeyHint="search" autoComplete="off" className="h-12 min-w-0 flex-1 bg-transparent text-base text-white outline-none placeholder:text-white/45" />
              <button className="btn-accent h-12 shrink-0 rounded-full px-5 text-[15px] font-semibold sm:px-7">Найти</button>
            </form>
            <div style={{ "--d": "0.65s" } as React.CSSProperties} className="hero-in no-scrollbar -mx-4 mt-3 flex snap-x gap-2 overflow-x-auto overscroll-x-contain px-4 sm:mx-0 sm:mt-4 sm:flex-wrap sm:overflow-visible sm:px-0">
              {POPULAR.map((p) => (
                <Link key={p} href={`/catalog?q=${encodeURIComponent(p)}`} className="press inline-flex h-11 shrink-0 snap-start items-center rounded-full border border-white/10 bg-white/[0.05] px-4 text-[13px] whitespace-nowrap text-white/80 transition hover:border-accent/50 hover:text-white sm:h-9">
                  {p}
                </Link>
              ))}
            </div>
          </div>

          <div style={{ "--d": "0.7s" } as React.CSSProperties} className="hero-in hidden lg:col-span-4 lg:block">
            <div className="relative overflow-hidden rounded-[1.75rem] border border-white/10 bg-black/55 p-5 shadow-[0_30px_80px_-40px_rgb(0_0_0/0.9)] sm:p-6 md:bg-black/40 md:backdrop-blur-xl">
              <span aria-hidden="true" className="absolute inset-x-6 top-0 h-px bg-[linear-gradient(90deg,transparent,rgb(235_17_29/0.8),transparent)]" />
              <div className="grid grid-cols-2 gap-x-4 gap-y-5">
                {[
                  [String(count), "моделей в каталоге"],
                  ["25–50", "дней до выдачи"],
                  ["3", "страны поставки"],
                  ["0 ₽", "скрытых платежей"],
                ].map(([v, l]) => (
                  <div key={l}>
                    <p className="tnum text-metal text-3xl font-semibold tracking-tight">{/^\d+$/.test(v) ? <Counter to={Number(v)} /> : v}</p>
                    <p className="mt-1 text-xs text-white/55">{l}</p>
                  </div>
                ))}
              </div>
              <div className="mt-5 flex flex-col gap-2 border-t border-white/10 pt-5 sm:flex-row lg:flex-col xl:flex-row">
                <Link href="/catalog" className="group inline-flex h-12 min-w-0 flex-1 items-center justify-center gap-1.5 rounded-full bg-white px-4 text-sm font-semibold whitespace-nowrap text-black transition hover:bg-white/90">
                  Смотреть каталог <ArrowUpRight className="size-4 transition group-hover:rotate-45" />
                </Link>
                <button onClick={() => lead({ title: "Рассчитать стоимость под ключ" })} className="h-12 min-w-0 flex-1 rounded-full border border-white/15 px-4 text-sm font-semibold whitespace-nowrap transition hover:border-white/40">
                  Рассчитать
                </button>
              </div>
            </div>
          </div>
        </div>
        <a href="#brands" aria-label="Листать вниз" className="mx-auto mt-10 hidden size-11 place-items-center rounded-full border border-white/15 text-white/70 transition hover:text-white lg:grid">
          <ChevronDown className="anim-nudge size-5" />
        </a>
      </motion.div>
    </section>
  );
}
