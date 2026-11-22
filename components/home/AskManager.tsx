"use client";

import Link from "next/link";
import { useCallback, useEffect, useRef, useState } from "react";
import { ArrowUp, Check, Loader2, MessageCircle, Sparkles } from "lucide-react";
import { Eyebrow } from "./SectionHead";
import { OpenNow } from "@/components/site/OpenNow";
import { API_URL, readUtm, rememberUtm, ymClientId } from "@/components/site/Lead";
import { company } from "@/content/nav";
import { cn } from "@/lib/utils";

type Msg = { id: string; from: "assistant" | "client" | "manager"; text: string; author?: string | null };
type ServerMsg = { id: number; from: "client" | "manager" | "assistant"; text: string; author?: string | null };

const STORE = "v2k_chat_token";

/** Мгновенные ответы ассистента на частые вопросы — без сети. Всё остальное уходит менеджеру. */
const QUICK: { q: string; a: string }[] = [
  { q: "Сколько стоит привезти авто?", a: "Цена «от» в каталоге уже под ключ до Анапы: автомобиль, экспорт, доставка, таможня, утильсбор, брокер и наша комиссия. Точную смету по конкретному лоту — с курсом и сроком действия — менеджер пришлёт до оплаты." },
  { q: "Сколько ждать машину?", a: "Обычно 25–50 дней от предоплаты до выдачи в Анапе: Китай быстрее, Япония и Корея — чуть дольше из-за морской логистики. О любом сдвиге сообщаем сами." },
  { q: "Что значит «проходной»?", a: "Это авто до 160 л.с. и возрастом 3–5 лет: по нему действует льготный утильсбор и пошлина, поэтому итоговая цена заметно ниже. Именно такие модели мы показываем в топе." },
  { q: "Как проходит оплата?", a: "Договор с юрлицом, оплата поэтапно по реквизитам из договора: предоплата, выкуп авто, таможня. На каждый платёж — документ." },
  { q: "Можно проверить машину до покупки?", a: "Да. Осмотр на площадке с фото и видео, отчёт об истории и пробеге — до выкупа. Фото осмотра показываем отдельно от фото объявления." },
];

const GREETING: Msg = {
  id: "greet",
  from: "assistant",
  text: `Здравствуйте! Я ассистент ВАН23АВТО. Отвечу на частые вопросы сразу, а любой свой вопрос напишите — его получит менеджер ${company.manager} и ответит прямо здесь, в этом чате.`,
};

export function AskManager() {
  const [msgs, setMsgs] = useState<Msg[]>([GREETING]);
  const [token, setToken] = useState<string | null>(null);
  const [text, setText] = useState("");
  const [sending, setSending] = useState(false);
  const [error, setError] = useState("");
  const [askContact, setAskContact] = useState(false);
  const [contact, setContact] = useState("");
  const [contactDone, setContactDone] = useState(false);
  const box = useRef<HTMLDivElement>(null);
  const lastRef = useRef(0);
  const seq = useRef(0);
  const nextId = (p: string) => `${p}${++seq.current}`;

  const push = useCallback((m: Msg | Msg[]) => setMsgs((prev) => [...prev, ...(Array.isArray(m) ? m : [m])]), []);

  const merge = useCallback((server: ServerMsg[]) => {
    if (!server.length) return;
    const max = Math.max(...server.map((m) => m.id));
    if (max <= lastRef.current) return;
    lastRef.current = max;
    setMsgs((prev) => {
      const have = new Set(prev.map((m) => m.id));
      const fresh = server.filter((m) => !have.has(`s${m.id}`)).map((m) => ({ id: `s${m.id}`, from: m.from, text: m.text, author: m.author }));
      return [...prev, ...fresh];
    });
  }, []);

  // Продолжение прошлой переписки после перезагрузки страницы.
  useEffect(() => {
    let saved: string | null = null;
    try { saved = localStorage.getItem(STORE); } catch {}
    if (!saved) return;
    fetch(`${API_URL}/api/v1/chat/${saved}?after=0`, { headers: { Accept: "application/json" } })
      .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
      .then((j) => {
        setToken(saved);
        setContactDone(!!j.has_contact);
        merge(j.messages ?? []);
      })
      .catch(() => { try { localStorage.removeItem(STORE); } catch {} });
  }, [merge]);

  // Опрос ответов менеджера, пока вкладка открыта.
  useEffect(() => {
    if (!token) return;
    const tick = () => {
      if (document.visibilityState !== "visible") return;
      fetch(`${API_URL}/api/v1/chat/${token}?after=${lastRef.current}`, { headers: { Accept: "application/json" } })
        .then((r) => {
          if (r.status === 404) {
            // переписка удалена — перестаём опрашивать
            try { localStorage.removeItem(STORE); } catch {}
            setToken(null);
            return null;
          }
          return r.ok ? r.json() : null;
        })
        .then((j) => j && merge(j.messages ?? []))
        .catch(() => {});
    };
    const t = setInterval(tick, 5000);
    return () => clearInterval(t);
  }, [token, merge]);

  // Прокрутка только внутри окна чата — страница не прыгает.
  useEffect(() => {
    const el = box.current;
    if (el) el.scrollTo({ top: el.scrollHeight, behavior: "smooth" });
  }, [msgs, askContact]);

  function quick(item: (typeof QUICK)[number]) {
    push({ id: nextId("q"), from: "client", text: item.q });
    const answerId = nextId("a");
    setTimeout(() => push({ id: answerId, from: "assistant", text: item.a + "\n\nОстались вопросы? Напишите — ответит менеджер." }), 450);
  }

  async function send(e?: React.FormEvent) {
    e?.preventDefault();
    const message = text.trim();
    if (message.length < 2 || sending) return;
    setSending(true);
    setError("");
    try {
      if (!token) {
        rememberUtm();
        const res = await fetch(`${API_URL}/api/v1/chat`, {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({ message, page: location.pathname + location.search, utm: readUtm(), ym_client_id: ymClientId(), website: "" }),
        });
        if (res.status === 429) throw new Error("Слишком часто. Попробуйте через минуту.");
        if (!res.ok) throw new Error("Не отправилось. Напишите нам в Telegram.");
        const j = await res.json();
        if (j.token) {
          setToken(j.token);
          try { localStorage.setItem(STORE, j.token); } catch {}
        }
        merge(j.messages ?? []);
        setText("");
        setTimeout(() => {
          push({ id: nextId("h"), from: "assistant", text: `Передал вопрос менеджеру. ${company.manager} ответит здесь — обычно в течение 15 минут в рабочее время.` });
          if (!contactDone) setAskContact(true);
        }, 400);
      } else {
        const res = await fetch(`${API_URL}/api/v1/chat/${token}`, {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({ message, after: lastRef.current }),
        });
        if (!res.ok) throw new Error("Не отправилось. Попробуйте ещё раз.");
        merge((await res.json()).messages ?? []);
        setText("");
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : "Ошибка отправки");
    } finally {
      setSending(false);
    }
  }

  async function saveContact(e: React.FormEvent) {
    e.preventDefault();
    const v = contact.trim();
    if (!token || (!v.startsWith("@") && v.replace(/\D/g, "").length < 10)) {
      setError("Укажите телефон или @username в Telegram");
      return;
    }
    setError("");
    const res = await fetch(`${API_URL}/api/v1/chat/${token}`, {
      method: "POST",
      headers: { "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify({ contact: v, after: lastRef.current }),
    }).catch(() => null);
    if (!res?.ok) {
      setError("Не сохранилось — проверьте номер");
      return;
    }
    setContactDone(true);
    setAskContact(false);
    push({ id: nextId("c"), from: "assistant", text: "Спасибо! Если закроете страницу — менеджер свяжется по этому контакту." });
  }

  return (
    <section data-component-id="ask" id="ask" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <div className="card relative grid grid-cols-1 overflow-hidden lg:grid-cols-12">
        <span aria-hidden="true" className="absolute inset-x-10 top-0 h-px bg-[linear-gradient(90deg,transparent,rgb(235_17_29/0.7),transparent)]" />
        {/* Левая панель: живой человек */}
        <div className="relative flex flex-col gap-4 border-b border-white/[0.07] p-5 sm:gap-6 sm:p-8 lg:col-span-5 lg:border-r lg:border-b-0">
          <div>
            <Eyebrow>Вопрос без звонков</Eyebrow>
            <h2 className="text-headline mt-2 sm:mt-4">Спросите — ответит живой человек</h2>
            <p className="mt-2 text-[15px] leading-[1.45] text-muted-foreground sm:mt-4">Ассистент сразу ответит на частые вопросы. Свой вопрос напишите в чат — его получит менеджер и ответит здесь же, без звонков и навязчивых рассылок.</p>
          </div>
          <div className="mt-auto flex items-center gap-3 rounded-2xl bg-white/[0.04] p-3 sm:gap-4 sm:p-4">
            <span className="relative grid size-12 shrink-0 place-items-center rounded-full bg-accent text-lg font-semibold text-white sm:size-14">
              {company.manager.slice(0, 1)}
            </span>
            <div className="min-w-0">
              <p className="font-semibold">{company.manager}</p>
              <p className="text-sm text-muted-foreground">Менеджер по подбору · {company.city}</p>
              <div className="mt-2"><OpenNow /></div>
            </div>
          </div>
        </div>

        {/* Правая панель: чат */}
        <div className="flex flex-col lg:col-span-7">
          <div className="flex items-center gap-3 border-b border-white/[0.07] px-5 py-4 sm:px-6">
            <span className="grid size-9 place-items-center rounded-full bg-accent/15 text-accent"><Sparkles className="size-4" /></span>
            <div className="min-w-0">
              <p className="text-sm font-semibold">Ассистент ВАН23АВТО</p>
              <p className="text-xs text-muted-foreground">{token ? `Чат с менеджером открыт · ${company.manager} на связи` : "Отвечает мгновенно · подключает менеджера"}</p>
            </div>
          </div>

          <div ref={box} aria-live="polite" className="no-scrollbar h-[56svh] max-h-[420px] min-h-[300px] space-y-2.5 overflow-y-auto overscroll-contain px-4 py-4 sm:space-y-3 sm:px-6 sm:py-5">
            {msgs.map((m) => (
              <div key={m.id} className={cn("flex", m.from === "client" ? "justify-end" : "justify-start")}>
                <div className={cn(
                  "max-w-[85%] rounded-[1.1rem] px-3.5 py-2.5 text-[15px] leading-[1.4] whitespace-pre-line",
                  m.from === "client" ? "rounded-br-md bg-accent text-white" : m.from === "manager" ? "rounded-bl-md border border-accent/30 bg-accent/10" : "rounded-bl-md bg-white/[0.06]",
                )}>
                  {m.from === "manager" && <p className="mb-1 text-xs font-semibold text-accent">{m.author || company.manager} · менеджер</p>}
                  {m.text}
                </div>
              </div>
            ))}
            {askContact && !contactDone && (
              <form onSubmit={saveContact} className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                <p className="text-sm text-foreground/85">Оставьте телефон или @username — чтобы ответить, даже если закроете страницу:</p>
                <div className="mt-3 flex gap-2">
                  <input aria-label="Телефон или username в Telegram" value={contact} onChange={(e) => setContact(e.target.value)} inputMode="tel" autoComplete="tel" placeholder="+7 900 000-00-00 или @username" className="h-11 min-w-0 flex-1 rounded-full border border-border bg-background/60 px-4 text-[15px] outline-none focus:border-accent/70" />
                  <button className="grid size-11 shrink-0 place-items-center rounded-full bg-white text-black" aria-label="Сохранить контакт"><Check className="size-4" /></button>
                </div>
              </form>
            )}
          </div>

          {!token && (
            <div className="no-scrollbar flex snap-x gap-2 overflow-x-auto overscroll-x-contain px-4 pb-3 sm:px-6">
              {QUICK.map((item) => (
                <button key={item.q} type="button" onClick={() => quick(item)} className="press h-11 shrink-0 snap-start rounded-full border border-white/10 bg-white/[0.04] px-3.5 text-[13px] text-foreground/85 transition hover:border-accent/50 hover:text-foreground">
                  {item.q}
                </button>
              ))}
            </div>
          )}

          <form onSubmit={send} className="border-t border-white/[0.07] p-3 sm:p-4">
            <div className="flex items-end gap-2 rounded-[1.4rem] border border-white/10 bg-background/60 p-1.5 pl-4 transition-[border-color] focus-within:border-accent/60">
              <MessageCircle className="mb-3 size-4 shrink-0 text-muted-foreground" />
              <label htmlFor="ask-text" className="sr-only">Ваш вопрос</label>
              <textarea
                id="ask-text"
                value={text}
                onChange={(e) => setText(e.target.value)}
                onKeyDown={(e) => { if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); void send(); } }}
                rows={1}
                maxLength={2000}
                placeholder="Ваш вопрос менеджеру"
                enterKeyHint="send" className="max-h-32 min-h-11 flex-1 resize-none bg-transparent py-3 text-base outline-none placeholder:text-muted-foreground/70"
              />
              <button type="submit" disabled={sending || text.trim().length < 2} aria-label="Отправить" className="btn-accent grid size-11 shrink-0 place-items-center rounded-full disabled:opacity-40">
                {sending ? <Loader2 className="size-4 animate-spin" /> : <ArrowUp className="size-4" />}
              </button>
            </div>
            {error && <p role="alert" className="mt-2 px-2 text-sm text-red-300">{error}</p>}
            <p className="mt-2 px-2 text-[12px] text-muted-foreground">Отправляя сообщение, вы соглашаетесь на <Link href="/privacy" className="underline underline-offset-2">обработку данных</Link>.</p>
          </form>
        </div>
      </div>
    </section>
  );
}
