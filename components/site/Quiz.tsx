"use client";

import Link from "next/link";
import { useEffect, useMemo, useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { ArrowLeft, ArrowRight, Check } from "lucide-react";
import { LeadForm } from "./Lead";
import { CarCard } from "@/components/car/CarCard";
import { cars, type Country } from "@/content/cars";
import { cn } from "@/lib/utils";

interface Answers { task: string; link: string; country: Country | "any"; condition: "new" | "used" | "any"; budget: number; city: string }
const INIT: Answers = { task: "", link: "", country: "any", condition: "any", budget: 3_000_000, city: "Анапа" };
const KEY = "van2k:quiz";

const TASKS = ["Семейный кроссовер", "Город и экономия", "Электромобиль", "Минивэн на 7 мест", "Внедорожник", "Премиум-седан"];
const STEPS = ["Задача", "Страна", "Бюджет", "Контакт"];

export function Quiz() {
  const [step, setStep] = useState(0);
  const [a, setA] = useState<Answers>(INIT);
  const [dir, setDir] = useState(1);

  useEffect(() => {
    try {
      const s = sessionStorage.getItem(KEY);
      // eslint-disable-next-line react-hooks/set-state-in-effect
      if (s) setA({ ...INIT, ...JSON.parse(s) });
    } catch {}
  }, []);
  useEffect(() => {
    try { sessionStorage.setItem(KEY, JSON.stringify(a)); } catch {}
  }, [a]);

  const matches = useMemo(
    () => cars.filter((c) => (a.country === "any" || c.country === a.country) && (a.condition === "any" || c.condition === a.condition) && c.price <= a.budget * 1.1).sort((x, y) => Math.abs(y.price - a.budget) < Math.abs(x.price - a.budget) ? 1 : -1).slice(0, 3),
    [a],
  );

  const canNext = step === 0 ? !!(a.task || a.link.trim()) : true;
  const next = () => { setDir(1); setStep((s) => Math.min(3, a.link.trim() && s === 0 ? 2 : s + 1)); };
  const back = () => { setDir(-1); setStep((s) => Math.max(0, s - 1)); };

  const summary = [a.link.trim() ? `Ссылка: ${a.link.trim()}` : a.task, a.country === "any" ? "любая страна" : { china: "Китай", korea: "Корея", japan: "Япония" }[a.country], a.condition === "any" ? "" : a.condition === "new" ? "новый" : "с пробегом", `до ${new Intl.NumberFormat("ru-RU").format(a.budget)} ₽`, a.city].filter(Boolean).join(" · ");

  return (
    <div className="grid gap-8 lg:grid-cols-12">
      <div className="card relative overflow-hidden p-6 sm:p-10 lg:col-span-7">
        <div aria-hidden="true" className="absolute -top-24 -right-24 size-64 rounded-full bg-accent/10 blur-3xl" />
        <ol className="relative flex items-center gap-2" aria-label="Шаги">
          {STEPS.map((s, i) => (
            <li key={s} className="flex flex-1 flex-col gap-2">
              <div className="h-1 overflow-hidden rounded-full bg-white/10">
                <motion.div className="h-full bg-accent" initial={false} animate={{ width: i <= step ? "100%" : "0%" }} transition={{ duration: 0.4 }} />
              </div>
              <span className={cn("text-xs", i === step ? "text-foreground" : "text-muted-foreground")} aria-current={i === step ? "step" : undefined}>{i + 1}. {s}</span>
            </li>
          ))}
        </ol>

        <div className="relative mt-10 min-h-[360px]">
          <AnimatePresence mode="wait" custom={dir}>
            <motion.div key={step} custom={dir} initial={{ opacity: 0, x: dir * 40 }} animate={{ opacity: 1, x: 0 }} exit={{ opacity: 0, x: dir * -40 }} transition={{ duration: 0.35, ease: [0.22, 1, 0.36, 1] }}>
              {step === 0 && (
                <>
                  <h2 className="text-3xl font-semibold tracking-tight">Какая задача у автомобиля? <span className="text-accent">*</span></h2>
                  <p className="mt-2 text-muted-foreground">Выберите вариант или вставьте ссылку на объявление — тогда пропустим лишние вопросы.</p>
                  <div className="mt-6 grid gap-2 sm:grid-cols-2">
                    {TASKS.map((t) => (
                      <Option key={t} on={a.task === t} onClick={() => setA({ ...a, task: t })}>{t}</Option>
                    ))}
                  </div>
                  <label htmlFor="quiz-link" className="mt-6 mb-2 block text-sm text-muted-foreground">Или ссылка на объявление (Che168, Encar, аукцион)</label>
                  <input id="quiz-link" value={a.link} onChange={(e) => setA({ ...a, link: e.target.value })} placeholder="https://…" inputMode="url" className="h-13 w-full rounded-2xl border border-border bg-background/60 px-4 outline-none focus:border-accent/70" />
                </>
              )}
              {step === 1 && (
                <>
                  <h2 className="text-3xl font-semibold tracking-tight">Страна и состояние</h2>
                  <p className="mt-2 text-muted-foreground">Если не уверены — оставьте «Любая», подскажем лучший вариант под бюджет.</p>
                  <p className="mt-6 mb-3 text-sm font-medium">Страна</p>
                  <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    {([["any", "Любая"], ["china", "Китай"], ["korea", "Корея"], ["japan", "Япония"]] as const).map(([v, l]) => (
                      <Option key={v} on={a.country === v} onClick={() => setA({ ...a, country: v })}>{l}</Option>
                    ))}
                  </div>
                  <p className="mt-6 mb-3 text-sm font-medium">Состояние</p>
                  <div className="grid grid-cols-3 gap-2">
                    {([["any", "Не важно"], ["new", "Новый"], ["used", "С пробегом"]] as const).map(([v, l]) => (
                      <Option key={v} on={a.condition === v} onClick={() => setA({ ...a, condition: v })}>{l}</Option>
                    ))}
                  </div>
                </>
              )}
              {step === 2 && (
                <>
                  <h2 className="text-3xl font-semibold tracking-tight">Бюджет и город получения</h2>
                  <p className="mt-2 text-muted-foreground">Бюджет под ключ — с таможней, логистикой и оформлением.</p>
                  <p className="tnum mt-8 text-5xl font-semibold tracking-tight">{new Intl.NumberFormat("ru-RU").format(a.budget)} ₽</p>
                  <input type="range" min={1_000_000} max={10_000_000} step={100_000} value={a.budget} onChange={(e) => setA({ ...a, budget: Number(e.target.value) })} aria-label="Бюджет" className="mt-5 w-full accent-[#c6f447]" />
                  <div className="mt-1 flex justify-between text-xs text-muted-foreground"><span>1 млн</span><span>10 млн</span></div>
                  <p className="mt-8 mb-3 text-sm font-medium">Город</p>
                  <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    {["Анапа", "Новороссийск", "Краснодар", "Другой"].map((c) => (
                      <Option key={c} on={a.city === c} onClick={() => setA({ ...a, city: c })}>{c}</Option>
                    ))}
                  </div>
                </>
              )}
              {step === 3 && (
                <>
                  <h2 className="text-3xl font-semibold tracking-tight">Куда прислать варианты?</h2>
                  <p className="mt-2 mb-6 text-muted-foreground">Менеджер пришлёт до трёх вариантов с ценой под ключ до города «{a.city}».</p>
                  <LeadForm context={summary} compact />
                </>
              )}
            </motion.div>
          </AnimatePresence>
        </div>

        <div className="relative mt-8 flex items-center justify-between gap-3 border-t border-white/[0.06] pt-6">
          <button onClick={back} disabled={step === 0} className="inline-flex h-12 items-center gap-2 rounded-full px-4 text-sm font-medium text-muted-foreground transition hover:text-foreground disabled:opacity-0">
            <ArrowLeft className="size-4" />Назад
          </button>
          {step < 3 && (
            <button onClick={next} disabled={!canNext} className="btn-accent inline-flex h-12 items-center gap-2 rounded-full px-7 text-sm font-semibold transition disabled:opacity-40">
              Далее<ArrowRight className="size-4" />
            </button>
          )}
        </div>
      </div>

      <aside className="lg:col-span-5">
        <div className="lg:sticky lg:top-24">
          <p className="text-sm text-muted-foreground">Подходит под ваши ответы</p>
          <p className="mt-1 text-2xl font-semibold tracking-tight">{matches.length ? "Предварительные варианты" : "Подберём на площадках"}</p>
          <div className="mt-5 grid gap-4">
            <AnimatePresence mode="popLayout">
              {matches.map((c) => (
                <motion.div key={c.slug} layout initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }}>
                  <CarCard car={c} />
                </motion.div>
              ))}
            </AnimatePresence>
            {!matches.length && (
              <div className="card p-6 text-muted-foreground">В каталоге нет моделей под эти параметры — но мы найдём лот на площадках. Оставьте контакт на последнем шаге. <Link href="/catalog" className="text-accent underline-offset-4 hover:underline">Открыть каталог</Link></div>
            )}
          </div>
        </div>
      </aside>
    </div>
  );
}

function Option({ on, onClick, children }: { on: boolean; onClick: () => void; children: React.ReactNode }) {
  return (
    <button type="button" onClick={onClick} aria-pressed={on} className={cn("flex h-14 items-center justify-between gap-2 rounded-2xl border px-4 text-left text-[15px] transition active:scale-[0.98]", on ? "border-accent bg-accent/10 text-foreground" : "border-white/10 bg-white/[0.02] text-foreground/80 hover:border-white/25")}>
      {children}
      <span className={cn("grid size-5 shrink-0 place-items-center rounded-full border transition", on ? "border-accent bg-accent text-accent-foreground" : "border-white/20")}>{on && <Check className="size-3" strokeWidth={3} />}</span>
    </button>
  );
}
