"use client";

import { createContext, useCallback, useContext, useEffect, useId, useRef, useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { Check, Loader2, Send, X } from "lucide-react";
import { cn } from "@/lib/utils";

type LeadCtx = { context?: string; title?: string };
const Ctx = createContext<(c?: LeadCtx) => void>(() => {});
export const useLead = () => useContext(Ctx);

export function LeadProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = useState<LeadCtx | null>(null);
  useEffect(() => rememberUtm(), []);
  const open = useCallback((c?: LeadCtx) => setState(c ?? {}), []);
  return (
    <Ctx.Provider value={open}>
      {children}
      <AnimatePresence>{state && <LeadModal ctx={state} onClose={() => setState(null)} />}</AnimatePresence>
    </Ctx.Provider>
  );
}

export function LeadButton({ children, className, context, title }: { children: React.ReactNode; className?: string } & LeadCtx) {
  const open = useLead();
  return (
    <button type="button" className={className} onClick={() => open({ context, title })}>
      {children}
    </button>
  );
}

const CHANNELS = ["Telegram", "WhatsApp", "Звонок"] as const;

const API_URL = (process.env.NEXT_PUBLIC_API_URL ?? "https://vanauto-api.neeklo.ru").replace(/\/$/, "");
const UTM_KEYS = ["utm_source", "utm_medium", "utm_campaign", "utm_content", "utm_term"] as const;

/** UTM и реферер первого захода — сохраняем на сессию, чтобы не потерять при переходах по сайту. */
export function rememberUtm() {
  try {
    if (sessionStorage.getItem("v2k_utm")) return;
    const q = new URLSearchParams(location.search);
    const utm: Record<string, string> = {};
    for (const k of UTM_KEYS) { const v = q.get(k); if (v) utm[k] = v.slice(0, 200); }
    if (document.referrer && !document.referrer.startsWith(location.origin)) utm.referrer = document.referrer.slice(0, 250);
    sessionStorage.setItem("v2k_utm", JSON.stringify(utm));
  } catch {}
}

function readUtm(): Record<string, string> {
  try { return JSON.parse(sessionStorage.getItem("v2k_utm") || "{}"); } catch { return {}; }
}

function ymClientId(): string | undefined {
  try {
    const m = document.cookie.match(/(?:^|; )_ym_uid=(\d+)/);
    return m?.[1];
  } catch { return undefined; }
}

/** Отправка заявки в админку → Telegram менеджерам. Машина определяется по адресу /catalog/<slug>. */
export async function sendLead(payload: { name?: string; contact: string; channel?: string; comment?: string; website?: string; context?: Record<string, string> }) {
  rememberUtm();
  const m = location.pathname.match(/\/catalog\/([^/]+)/);
  const res = await fetch(`${API_URL}/api/v1/leads`, {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify({
      ...payload,
      consent: true,
      car_slug: m ? decodeURIComponent(m[1]) : undefined,
      page: location.pathname + location.search,
      utm: readUtm(),
      ym_client_id: ymClientId(),
    }),
  });
  if (res.status === 429) throw new Error("Слишком много заявок подряд. Попробуйте через минуту.");
  if (res.status === 422) {
    const j = await res.json().catch(() => null);
    const first = j?.errors ? (Object.values(j.errors)[0] as string[])[0] : null;
    throw new Error(first || "Проверьте телефон или @username");
  }
  if (!res.ok) throw new Error("Не удалось отправить. Напишите нам в Telegram.");
  try { (window as unknown as { ym?: (...a: unknown[]) => void }).ym?.(Number(process.env.NEXT_PUBLIC_YM_ID ?? 0), "reachGoal", "lead_submit"); } catch {}
  return res.json();
}

export function LeadForm({ context, onDone, compact }: { context?: string; onDone?: () => void; compact?: boolean }) {
  const id = useId();
  const [channel, setChannel] = useState<(typeof CHANNELS)[number]>("Telegram");
  const [status, setStatus] = useState<"idle" | "sending" | "done">("idle");
  const [error, setError] = useState("");

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const data = new FormData(e.currentTarget);
    const phone = String(data.get("contact") ?? "").trim();
    if (phone.replace(/\D/g, "").length < 10 && !phone.startsWith("@")) {
      setError("Укажите телефон или @username в Telegram");
      return;
    }
    setError("");
    setStatus("sending");
    try {
      await sendLead({
        name: String(data.get("name") ?? "").trim() || undefined,
        contact: phone,
        channel,
        comment: String(data.get("message") ?? "").trim() || undefined,
        website: String(data.get("website") ?? ""),
        context: context ? { "Запрос": context } : undefined,
      });
      setStatus("done");
      onDone?.();
    } catch (err) {
      setStatus("idle");
      setError(err instanceof Error ? err.message : "Не удалось отправить. Напишите нам в Telegram.");
    }
  }

  if (status === "done") {
    return (
      <motion.div initial={{ opacity: 0, scale: 0.96 }} animate={{ opacity: 1, scale: 1 }} className="py-6 text-center">
        <div className="mx-auto grid size-16 place-items-center rounded-full bg-accent text-accent-foreground">
          <Check className="size-8" strokeWidth={2.5} />
        </div>
        <h3 className="mt-5 text-2xl">Заявка принята</h3>
        <p className="mx-auto mt-2 max-w-xs text-muted-foreground">
          Менеджер свяжется с вами через {channel === "Звонок" ? "звонок" : channel} в рабочее время — обычно в течение 15 минут.
        </p>
      </motion.div>
    );
  }

  const field = "h-13 w-full rounded-2xl border border-border bg-background/60 px-4 text-[15px] outline-none transition placeholder:text-muted-foreground/70 focus:border-accent/70 focus:bg-background";
  return (
    <form onSubmit={submit} className="grid gap-3" noValidate>
      {context && (
        <div className="rounded-2xl border border-accent/25 bg-accent/[0.06] px-4 py-3 text-sm">
          <span className="text-muted-foreground">Запрос по: </span>
          {context}
        </div>
      )}
      {/* Ловушка для ботов: люди это поле не видят и не заполняют. */}
      <input type="text" name="website" tabIndex={-1} autoComplete="off" aria-hidden="true" className="absolute -left-[9999px] h-0 w-0 opacity-0" />
      <label className="sr-only" htmlFor={`${id}-name`}>Имя</label>
      <input id={`${id}-name`} name="name" autoComplete="given-name" placeholder="Как к вам обращаться" className={field} />
      <label className="sr-only" htmlFor={`${id}-contact`}>Телефон или Telegram</label>
      <input id={`${id}-contact`} name="contact" inputMode="tel" autoComplete="tel" required placeholder="Телефон или @telegram *" className={cn(field, error && "border-red-400/70")} aria-invalid={!!error} aria-describedby={error ? `${id}-err` : undefined} />
      {error && <p id={`${id}-err`} className="-mt-1 px-1 text-sm text-red-300">{error}</p>}
      {!compact && (
        <>
          <label className="sr-only" htmlFor={`${id}-msg`}>Комментарий</label>
          <textarea id={`${id}-msg`} name="message" rows={3} placeholder="Модель, бюджет или ссылка на объявление" className={cn(field, "h-auto resize-none py-3.5")} />
        </>
      )}
      <fieldset className="flex flex-wrap gap-2">
        <legend className="mb-2 px-1 text-sm text-muted-foreground">Удобный канал связи</legend>
        {CHANNELS.map((c) => (
          <button key={c} type="button" onClick={() => setChannel(c)} aria-pressed={channel === c} className={cn("h-10 rounded-full border px-4 text-sm transition", channel === c ? "border-accent bg-accent text-accent-foreground" : "border-border hover:border-foreground/30")}>
            {c}
          </button>
        ))}
      </fieldset>
      <button type="submit" disabled={status === "sending"} className="btn-accent shine mt-2 inline-flex h-14 items-center justify-center gap-2 rounded-full font-semibold transition active:scale-[0.98] disabled:opacity-70">
        {status === "sending" ? <Loader2 className="size-5 animate-spin" /> : <Send className="size-4.5" />}
        {status === "sending" ? "Отправляем…" : "Отправить заявку"}
      </button>
      <p className="px-1 text-xs leading-relaxed text-muted-foreground">
        Нажимая кнопку, вы соглашаетесь на <a href={`${process.env.NEXT_PUBLIC_BASE_PATH ?? ""}/privacy/`} className="underline underline-offset-2 hover:text-foreground">обработку персональных данных</a>. Ответим без навязчивых звонков.
      </p>
    </form>
  );
}

function LeadModal({ ctx, onClose }: { ctx: LeadCtx; onClose: () => void }) {
  const ref = useRef<HTMLDivElement>(null);
  useEffect(() => {
    const prev = document.activeElement as HTMLElement | null;
    const html = document.documentElement;
    html.style.overflow = "hidden";
    ref.current?.querySelector<HTMLElement>("input")?.focus();
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
      if (e.key === "Tab" && ref.current) {
        const f = ref.current.querySelectorAll<HTMLElement>("button, input, textarea, a[href]");
        const first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    };
    window.addEventListener("keydown", onKey);
    return () => {
      html.style.overflow = "";
      window.removeEventListener("keydown", onKey);
      prev?.focus();
    };
  }, [onClose]);

  return (
    <motion.div className="fixed inset-0 z-[100] flex items-end justify-center sm:items-center sm:p-6" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
      <div className="absolute inset-0 bg-black/70" onClick={onClose} />
      <motion.div
        ref={ref}
        role="dialog"
        aria-modal="true"
        aria-labelledby="lead-title"
        className="card pb-safe relative w-full max-w-lg rounded-b-none p-6 shadow-2xl sm:rounded-b-[1.5rem] sm:p-8"
        initial={{ y: 60, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        exit={{ y: 60, opacity: 0 }}
        transition={{ type: "spring", damping: 30, stiffness: 320 }}
      >
        <div className="mx-auto -mt-2 mb-4 h-1 w-10 rounded-full bg-foreground/15 sm:hidden" />
        <button onClick={onClose} aria-label="Закрыть" className="absolute top-4 right-4 grid size-10 place-items-center rounded-full bg-foreground/5 transition hover:bg-foreground/10">
          <X className="size-5" />
        </button>
        <p className="text-xs font-medium tracking-[0.18em] text-accent uppercase">Ответ за 15 минут</p>
        <h2 id="lead-title" className="mt-2 pr-10 text-2xl sm:text-3xl">{ctx.title ?? "Подберём и посчитаем авто под ключ"}</h2>
        <p className="mt-2 mb-6 text-muted-foreground">Руслан уточнит наличие, состояние и пришлёт смету до вашего города.</p>
        <LeadForm context={ctx.context} />
      </motion.div>
    </motion.div>
  );
}
