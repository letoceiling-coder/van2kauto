"use client";

import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import { CheckCircle2, Minus, Plus, ShoppingBag, Trash2 } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { formatPrice, productHref, useCart, type CartLine, type ShopProduct } from "@/components/ui/Shop";
import { asset } from "@/lib/utils";

export interface CartCheckoutProps {
  title: string;
  text?: string;
  /** Catalog records (to resolve cart lines added by slug). Menus/showcases add lines with a snapshot, so it may be empty. */
  products?: ShopProduct[];
  /** Base path of product pages and the catalog: "/catalog". */
  basePath: string;
  /** Order endpoint (POST, JSON). Without it the order is sent as an e-mail draft to `email`. */
  formEndpoint?: string;
  email?: string;
  submitLabel?: string;
  successText?: string;
  emptyText?: string;
  /** Link back to the goods: "Перейти в каталог", "Вернуться в меню". */
  backLabel?: string;
  /** e.g. "Оплата после подтверждения менеджером". */
  note?: string;
  currency?: string;
}

/** Cart + order form: quantities, totals, contact form; the order goes to the endpoint or e-mail, then the cart is cleared. */
export function CartCheckout({ title, text, products = [], basePath, formEndpoint, email, submitLabel = "Оформить заказ", successText = "Заказ принят! Менеджер свяжется с вами для подтверждения.", emptyText = "В корзине пока пусто.", backLabel = "Перейти в каталог", note, currency = "₽" }: CartCheckoutProps) {
  const cart = useCart();
  const [state, setState] = useState<"idle" | "sending" | "done" | "error">("idle");
  // catalog record first; items added from menus/showcases carry their own snapshot (name, price, photo)
  const fromSnapshot = (l: CartLine): ShopProduct | undefined =>
    l.name && typeof l.price === "number"
      ? { slug: l.slug, name: l.name, category: "", price: l.price, summary: "", description: "", images: l.image ? [l.image] : [], specs: [] }
      : undefined;
  const lines = cart.lines.map((l) => ({ ...l, p: products.find((p) => p.slug === l.slug) ?? fromSnapshot(l) })).filter((l): l is typeof l & { p: ShopProduct } => !!l.p);
  const linkable = (slug: string) => products.some((p) => p.slug === slug);
  const total = lines.reduce((s, l) => s + l.p.price * l.qty, 0);

  async function onSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = Object.fromEntries(new FormData(e.currentTarget)) as Record<string, string>;
    const order = { ...form, items: lines.map((l) => ({ slug: l.slug, name: l.p.name, qty: l.qty, price: l.p.price })), total };
    if (!formEndpoint) {
      const body = [...order.items.map((i) => `${i.name} × ${i.qty} — ${formatPrice(i.price * i.qty, currency)}`), `Итого: ${formatPrice(total, currency)}`, "", `Имя: ${form.name}`, `Телефон: ${form.phone}`, form.comment ? `Комментарий: ${form.comment}` : ""].join("\n");
      window.location.assign(`mailto:${email ?? ""}?subject=${encodeURIComponent("Заказ с сайта")}&body=${encodeURIComponent(body)}`);
      cart.clear();
      setState("done");
      return;
    }
    setState("sending");
    try {
      const r = await fetch(formEndpoint, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(order) });
      if (!r.ok) throw new Error(String(r.status));
      cart.clear();
      setState("done");
    } catch {
      setState("error");
    }
  }

  return (
    <section data-component-id="cart-checkout" className="py-(--section-y)">
      <Container>
        <div className="mb-10 max-w-2xl">
          <h1 className="text-headline" data-editable-id="cart.title">{title}</h1>
          {text && <p className="mt-4 text-lg text-muted-foreground" data-editable-id="cart.text">{text}</p>}
        </div>

        {state === "done" ? (
          <div className="grid place-items-center rounded-token border border-border px-6 py-16 text-center" role="status">
            <CheckCircle2 className="size-10 text-accent" />
            <p className="mt-4 max-w-md text-lg">{successText}</p>
            <Link href={basePath} className="mt-6 inline-flex h-11 items-center rounded-full bg-primary px-6 text-sm font-medium text-primary-foreground">{backLabel}</Link>
          </div>
        ) : lines.length === 0 ? (
          <div className="grid place-items-center rounded-token border border-dashed border-border px-6 py-16 text-center">
            <ShoppingBag className="size-10 text-muted-foreground" />
            <p className="mt-4 text-lg">{emptyText}</p>
            <Link href={basePath} className="mt-6 inline-flex h-11 items-center rounded-full bg-primary px-6 text-sm font-medium text-primary-foreground">{backLabel}</Link>
          </div>
        ) : (
          <div className="grid gap-10 lg:grid-cols-[1fr_24rem] lg:gap-14">
            <ul className="divide-y divide-border border-y border-border">
              {lines.map(({ p, qty }) => (
                <li key={p.slug} className="flex gap-4 py-5">
                  <div className="relative w-24 shrink-0 overflow-hidden rounded-token bg-muted sm:w-32" style={{ aspectRatio: "4 / 3" }}>
                    {p.images[0] && <Image src={asset(p.images[0].src)} alt={p.images[0].alt} fill sizes="128px" className="object-cover" />}
                  </div>
                  <div className="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="min-w-0">
                      <p className="text-xs text-muted-foreground">{p.category}</p>
                      {linkable(p.slug) ? <Link href={productHref(basePath, p.slug)} className="font-medium break-words hover:underline">{p.name}</Link> : <p className="font-medium break-words">{p.name}</p>}
                      <p className="mt-1 text-sm text-muted-foreground tabular-nums">{formatPrice(p.price, currency)}</p>
                    </div>
                    <div className="flex items-center gap-3">
                      <div className="flex items-center rounded-full border border-border">
                        <button type="button" onClick={() => cart.setQty(p.slug, qty - 1)} aria-label="Меньше" className="grid size-9 place-items-center"><Minus className="size-4" /></button>
                        <span className="w-7 text-center text-sm tabular-nums" aria-live="polite">{qty}</span>
                        <button type="button" onClick={() => cart.setQty(p.slug, qty + 1)} aria-label="Больше" className="grid size-9 place-items-center"><Plus className="size-4" /></button>
                      </div>
                      <p className="w-28 text-right font-medium tabular-nums sm:w-32">{formatPrice(p.price * qty, currency)}</p>
                      <button type="button" onClick={() => cart.remove(p.slug)} aria-label={`Удалить ${p.name}`} className="grid size-9 place-items-center text-muted-foreground hover:text-foreground"><Trash2 className="size-4" /></button>
                    </div>
                  </div>
                </li>
              ))}
            </ul>

            <form onSubmit={onSubmit} className="grid gap-4 self-start rounded-token border border-border bg-muted p-6 lg:sticky lg:top-24">
              <div className="flex items-baseline justify-between gap-4 border-b border-border pb-4">
                <span className="text-muted-foreground">Итого</span>
                <span className="text-2xl font-medium tabular-nums">{formatPrice(total, currency)}</span>
              </div>
              <input name="name" required placeholder="Имя" autoComplete="name" className="h-12 rounded-token border border-border bg-background px-4 outline-none focus:border-primary" />
              <input name="phone" required type="tel" placeholder="Телефон" autoComplete="tel" className="h-12 rounded-token border border-border bg-background px-4 outline-none focus:border-primary" />
              <textarea name="comment" rows={3} placeholder="Комментарий к заказу" className="rounded-token border border-border bg-background px-4 py-3 outline-none focus:border-primary" />
              <button type="submit" disabled={state === "sending"} className="h-12 rounded-full bg-primary font-medium text-primary-foreground disabled:opacity-60">
                {state === "sending" ? "Отправляем…" : submitLabel}
              </button>
              {state === "error" && <p className="text-sm text-accent" role="alert">Не удалось отправить. Попробуйте ещё раз или позвоните нам.</p>}
              {note && <p className="text-xs text-muted-foreground">{note}</p>}
            </form>
          </div>
        )}
      </Container>
    </section>
  );
}
