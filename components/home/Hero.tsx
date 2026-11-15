"use client";

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
    <section ref={ref} data-component-id="hero" className="relative isolate flex min-h-[100svh] flex-col overflow-hidden">
      <motion.div style={{ y }} className="absolute inset-0 -z-10">
        <video
          className="size-full scale-105 object-cover"
          src={asset("/media/generated/premium-car-dark-loop.mp4")}
          poster={asset("/media/generated/premium-car-dark.webp")}
          autoPlay
          muted
          loop
          playsInline
          preload="metadata"
          aria-hidden="true"
        />
      </motion.div>
      <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgb(10_12_14/0.55)_0%,rgb(10_12_14/0.25)_35%,rgb(10_12_14/0.85)_78%,#0a0c0e_100%)]" />
      <div className="absolute inset-0 -z-10 bg-[radial-gradient(80%_60%_at_15%_60%,rgb(10_12_14/0.8),transparent_70%)]" />

      {/* Маршрут Азия → Анапа: тонкие линии поверх видео */}
      <svg aria-hidden="true" className="pointer-events-none absolute right-0 bottom-[18%] -z-10 hidden h-[60%] w-[55%] opacity-60 lg:block" viewBox="0 0 600 400" fill="none">
        <path d="M580 60 C 420 40, 360 200, 200 220 S 40 330, 10 360" stroke="url(#g1)" strokeWidth="1.2" className="anim-dash" />
        <path d="M590 150 C 470 150, 380 260, 260 270 S 80 340, 10 360" stroke="url(#g1)" strokeWidth="1" className="anim-dash" style={{ animationDelay: "-6s" }} />
        <path d="M560 260 C 450 290, 330 320, 10 360" stroke="url(#g1)" strokeWidth="0.8" className="anim-dash" style={{ animationDelay: "-11s" }} />
        <circle cx="10" cy="360" r="4" fill="#c6f447" />
        <circle cx="10" cy="360" r="12" stroke="#c6f447" strokeOpacity="0.4" />
        <defs>
          <linearGradient id="g1" x1="600" y1="0" x2="0" y2="400" gradientUnits="userSpaceOnUse">
            <stop stopColor="#c6f447" stopOpacity="0" />
            <stop offset="1" stopColor="#c6f447" stopOpacity="0.9" />
          </linearGradient>
        </defs>
      </svg>

      <motion.div style={desktop ? { opacity: fade } : undefined} className="mx-auto flex w-full max-w-site flex-1 flex-col justify-end px-4 pt-32 pb-10 sm:px-6 lg:px-8 lg:pb-16">
        <div className="grid items-end gap-10 lg:grid-cols-12">
          <div className="lg:col-span-8">
            <motion.p initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.6 }} className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/[0.06] py-1.5 pr-3.5 pl-1.5 text-xs font-medium text-white/85">
              <span className="rounded-full bg-accent px-2 py-0.5 text-[10px] font-bold tracking-wider text-accent-foreground uppercase">Анапа</span>
              Офис, выдача и доставка по России
            </motion.p>
            <h1 className="text-display mt-6 max-w-[15ch] font-semibold" aria-label="Авто из Китая, Кореи и Японии под заказ">
              {words.map((w, i) => (
                <span key={i} className="inline-block overflow-hidden pb-[0.1em] align-bottom" aria-hidden="true">
                  <motion.span
                    className={i >= 2 && i <= 5 && w !== "и" ? "inline-block text-accent" : "inline-block"}
                    initial={{ y: "110%" }}
                    animate={{ y: 0 }}
                    transition={reduce ? { duration: 0 } : { duration: 0.85, ease: [0.22, 1, 0.36, 1], delay: 0.1 + i * 0.06 }}
                  >
                    {w}&nbsp;
                  </motion.span>
                </span>
              ))}
            </h1>
            <motion.p initial={{ opacity: 0, y: 16 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.7, delay: 0.55 }} className="mt-6 max-w-xl text-[17px] leading-relaxed text-white/70 sm:text-lg">
              Подбираем, проверяем и привозим под ключ. Смета по статьям до оплаты, один менеджер и статус на каждом этапе.
            </motion.p>

            <motion.form
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.7, delay: 0.7 }}
              onSubmit={(e) => {
                e.preventDefault();
                router.push(`/catalog${q.trim() ? `?q=${encodeURIComponent(q.trim())}` : ""}`);
              }}
              role="search"
              className="mt-8 flex max-w-xl items-center gap-2 rounded-full border border-white/12 bg-black/55 p-1.5 pl-5 md:bg-black/35 md:backdrop-blur-xl transition focus-within:border-accent/60"
            >
              <Search className="size-5 shrink-0 text-white/50" />
              <label htmlFor="hero-q" className="sr-only">Марка или модель</label>
              <input id="hero-q" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Марка или модель" className="h-12 min-w-0 flex-1 bg-transparent text-[15px] text-white outline-none placeholder:text-white/45" />
              <button className="btn-accent shine h-12 shrink-0 rounded-full px-5 text-sm font-semibold sm:px-7">Найти</button>
            </motion.form>
            <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ delay: 0.9 }} className="mt-4 flex flex-wrap gap-2">
              {POPULAR.map((p) => (
                <Link key={p} href={`/catalog?q=${encodeURIComponent(p)}`} className="rounded-full border border-white/10 bg-white/[0.04] px-3.5 py-1.5 text-[13px] text-white/75 transition hover:border-accent/50 hover:text-white">
                  {p}
                </Link>
              ))}
            </motion.div>
          </div>

          <motion.div initial={{ opacity: 0, y: 24 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.8, delay: 0.8 }} className="lg:col-span-4">
            <div className="rounded-[1.75rem] border border-white/10 bg-black/55 p-5 md:bg-black/35 md:backdrop-blur-xl sm:p-6">
              <div className="grid grid-cols-2 gap-x-4 gap-y-5">
                {[
                  [String(count), "моделей в каталоге"],
                  ["25–50", "дней до выдачи"],
                  ["3", "страны поставки"],
                  ["0 ₽", "скрытых платежей"],
                ].map(([v, l]) => (
                  <div key={l}>
                    <p className="tnum text-3xl font-semibold tracking-tight">{/^\d+$/.test(v) ? <Counter to={Number(v)} /> : v}</p>
                    <p className="mt-1 text-xs text-white/55">{l}</p>
                  </div>
                ))}
              </div>
              <div className="mt-5 flex gap-2 border-t border-white/10 pt-5">
                <Link href="/catalog" className="group inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-full bg-white text-sm font-semibold text-black transition hover:bg-white/90">
                  Смотреть каталог <ArrowUpRight className="size-4 transition group-hover:rotate-45" />
                </Link>
                <button onClick={() => lead({ title: "Рассчитать стоимость под ключ" })} className="h-12 flex-1 rounded-full border border-white/15 text-sm font-semibold transition hover:border-white/40">
                  Рассчитать
                </button>
              </div>
            </div>
          </motion.div>
        </div>
        <a href="#brands" aria-label="Листать вниз" className="mx-auto mt-10 hidden size-11 place-items-center rounded-full border border-white/15 text-white/70 transition hover:text-white lg:grid">
          <ChevronDown className="size-5 animate-bounce" />
        </a>
      </motion.div>
    </section>
  );
}
