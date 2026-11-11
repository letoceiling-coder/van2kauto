"use client";

import { useState } from "react";
import { Mail, MapPin, Phone } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { SectionHeading } from "@/components/ui/SectionHeading";

export interface ContactSplitProps {
  eyebrow?: string;
  title: string;
  text?: string;
  phone?: string;
  email?: string;
  address?: string;
  /** Form endpoint (POST, JSON). Without it the form falls back to a mailto: link to `email`. */
  formEndpoint?: string;
  submitLabel?: string;
  successText?: string;
}

export function ContactSplit({ eyebrow, title, text, phone, email, address, formEndpoint, submitLabel = "Отправить", successText = "Спасибо! Мы свяжемся с вами в ближайшее время." }: ContactSplitProps) {
  const [state, setState] = useState<"idle" | "sending" | "done" | "error">("idle");

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.currentTarget).entries());
    if (!formEndpoint) {
      if (email) window.location.href = `mailto:${email}?subject=${encodeURIComponent("Заявка с сайта")}&body=${encodeURIComponent(`${data.name}\n${data.contact}\n\n${data.message}`)}`;
      setState("done");
      return;
    }
    setState("sending");
    try {
      const r = await fetch(formEndpoint, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(data) });
      setState(r.ok ? "done" : "error");
    } catch {
      setState("error");
    }
  }

  const field = "w-full rounded-token border border-border bg-surface px-4 py-3 text-base outline-none transition-colors focus:border-foreground";
  return (
    <section id="contact" data-component-id="contact-split" className="py-(--section-y)">
      <Container className="grid gap-12 lg:grid-cols-2 lg:gap-20">
        <div>
          <SectionHeading eyebrow={eyebrow} title={title} text={text} id="contact" />
          <ul className="mt-10 space-y-4">
            {phone && (
              <li className="flex items-center gap-3"><Phone className="size-5 text-accent" aria-hidden="true" /><a href={`tel:${phone.replace(/[^\d+]/g, "")}`}>{phone}</a></li>
            )}
            {email && (
              <li className="flex items-center gap-3"><Mail className="size-5 text-accent" aria-hidden="true" /><a href={`mailto:${email}`} className="break-all">{email}</a></li>
            )}
            {address && (
              <li className="flex items-start gap-3"><MapPin className="mt-0.5 size-5 shrink-0 text-accent" aria-hidden="true" /><span>{address}</span></li>
            )}
          </ul>
        </div>
        {state === "done" ? (
          <div className="flex items-center rounded-token border border-border bg-surface p-10 text-lg">{successText}</div>
        ) : (
          <form onSubmit={onSubmit} className="grid gap-4 rounded-token border border-border bg-muted p-6 sm:p-8">
            <label className="grid gap-2 text-sm">Имя<input name="name" required autoComplete="name" className={field} /></label>
            <label className="grid gap-2 text-sm">Телефон или email<input name="contact" required className={field} /></label>
            <label className="grid gap-2 text-sm">Сообщение<textarea name="message" rows={4} className={field} /></label>
            <button type="submit" disabled={state === "sending"} className="mt-2 h-12 rounded-token bg-primary px-6 font-medium text-primary-foreground transition-opacity hover:opacity-90 disabled:opacity-60">
              {state === "sending" ? "Отправка…" : submitLabel}
            </button>
            {state === "error" && <p className="text-sm text-accent">Не удалось отправить. Позвоните или напишите нам напрямую.</p>}
          </form>
        )}
      </Container>
    </section>
  );
}
