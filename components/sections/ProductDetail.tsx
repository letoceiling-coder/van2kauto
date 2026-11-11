"use client";

import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import { ArrowLeft, ArrowUpRight, Check, Heart, ShoppingBag } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { formatPrice, productHref, useCart, useFavorites, type ShopProduct } from "@/components/ui/Shop";
import { asset, cn } from "@/lib/utils";

export interface ProductDetailProps {
  product: ShopProduct;
  /** Up to 4 similar products (same category first). */
  related?: ShopProduct[];
  /** Base path of product pages / the catalog: "/catalog". */
  basePath: string;
  cartHref: string;
  backLabel?: string;
  relatedTitle?: string;
  /** Secondary action, e.g. { label: "Задать вопрос менеджеру", href: "/#contact" }. */
  contact?: { label: string; href: string };
  /** Small print, e.g. "Демо-данные: характеристики и цены условные". */
  note?: string;
  currency?: string;
}

/** Product page: gallery with thumbnails, price, add to cart, favorites, specs table, description and related products. */
export function ProductDetail({ product: p, related = [], basePath, cartHref, backLabel = "Назад в каталог", relatedTitle = "Похожие", contact, note, currency = "₽" }: ProductDetailProps) {
  const [active, setActive] = useState(0);
  const [justAdded, setJustAdded] = useState(false);
  const cart = useCart();
  const fav = useFavorites();
  const img = p.images[Math.min(active, p.images.length - 1)];
  const inCart = cart.qtyOf(p.slug);
  const liked = fav.has(p.slug);

  return (
    <section data-component-id="product-detail" className="py-(--section-y)">
      <Container>
        <Link href={basePath} className="mb-8 inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground">
          <ArrowLeft className="size-4" /> {backLabel}
        </Link>

        <div className="grid gap-10 lg:grid-cols-[1.15fr_1fr] lg:gap-16">
          <div className="min-w-0">
            <div className="radius-xl relative overflow-hidden bg-muted" style={{ aspectRatio: "4 / 3" }}>
              {img && <Image key={img.src} src={asset(img.src)} alt={img.alt} fill priority sizes="(min-width: 1024px) 55vw, 100vw" className="object-cover" style={{ objectPosition: img.focal ?? "50% 50%" }} />}
              {p.badge && <span className="glass absolute top-4 left-4 rounded-full px-3 py-1 text-xs font-medium">{p.badge}</span>}
            </div>
            {p.images.length > 1 && (
              <div className="mt-3 grid grid-cols-4 gap-3 sm:grid-cols-5">
                {p.images.map((m, i) => (
                  <button key={m.src + i} type="button" onClick={() => setActive(i)} aria-label={`Фото ${i + 1}`} aria-current={i === active}
                    className={cn("relative overflow-hidden rounded-token bg-muted ring-offset-2 ring-offset-background transition", i === active ? "ring-2 ring-primary" : "opacity-70 hover:opacity-100")} style={{ aspectRatio: "1 / 1" }}>
                    <Image src={asset(m.src)} alt="" fill sizes="120px" className="object-cover" />
                  </button>
                ))}
              </div>
            )}
          </div>

          <div className="min-w-0 lg:sticky lg:top-24 lg:self-start">
            <p className="eyebrow text-accent">{p.category}</p>
            <h1 className="mt-3 text-headline break-words" data-editable-id="product.name">{p.name}</h1>
            <p className="mt-4 text-lg text-muted-foreground">{p.summary}</p>
            <div className="mt-6 flex flex-wrap items-baseline gap-3">
              <p className="text-3xl font-medium tabular-nums">{formatPrice(p.price, currency)}</p>
              {p.oldPrice && p.oldPrice > p.price && <p className="text-muted-foreground tabular-nums line-through">{formatPrice(p.oldPrice, currency)}</p>}
            </div>

            <div className="mt-8 flex flex-wrap gap-3">
              <button type="button" onClick={() => { cart.add(p.slug); setJustAdded(true); setTimeout(() => setJustAdded(false), 1600); }}
                className="inline-flex h-13 flex-1 items-center justify-center gap-2 rounded-full bg-primary px-7 font-medium text-primary-foreground shadow-premium transition-transform active:scale-[0.98] sm:flex-none">
                {justAdded ? <Check className="size-5" /> : <ShoppingBag className="size-5" />}
                {justAdded ? "Добавлено" : "В корзину"}
              </button>
              <button type="button" onClick={() => fav.toggle(p.slug)} aria-pressed={liked} aria-label={liked ? "Убрать из избранного" : "В избранное"}
                className="grid size-13 place-items-center rounded-full border border-border transition-colors hover:bg-foreground/5">
                <Heart className={cn("size-5", liked && "fill-accent text-accent")} />
              </button>
            </div>
            {inCart > 0 && (
              <Link href={cartHref} className="mt-4 inline-flex items-center gap-1.5 text-sm underline underline-offset-4">
                В корзине: {inCart} — оформить заказ <ArrowUpRight className="size-4" />
              </Link>
            )}
            {contact && (
              <Link href={contact.href} className="mt-4 block text-sm text-muted-foreground hover:text-foreground">{contact.label}</Link>
            )}

            {p.specs.length > 0 && (
              <dl className="mt-10 divide-y divide-border border-y border-border">
                {p.specs.map((s) => (
                  <div key={s.label} className="flex justify-between gap-6 py-3 text-sm">
                    <dt className="text-muted-foreground">{s.label}</dt>
                    <dd className="text-right font-medium break-words">{s.value}</dd>
                  </div>
                ))}
              </dl>
            )}
            <p className="mt-8 leading-relaxed whitespace-pre-line text-muted-foreground" data-editable-id="product.description">{p.description}</p>
            {note && <p className="mt-6 text-xs text-muted-foreground">{note}</p>}
          </div>
        </div>

        {related.length > 0 && (
          <div className="mt-24">
            <h2 className="text-title mb-8">{relatedTitle}</h2>
            <ul className="grid grid-cols-2 gap-x-4 gap-y-8 lg:grid-cols-4">
              {related.slice(0, 4).map((r) => (
                <li key={r.slug} className="group">
                  <Link href={productHref(basePath, r.slug)} className="block">
                    <div className="radius-xl relative overflow-hidden bg-muted" style={{ aspectRatio: "4 / 3" }}>
                      {r.images[0] && <Image src={asset(r.images[0].src)} alt={r.images[0].alt} fill sizes="(min-width: 1024px) 25vw, 50vw" className="object-cover transition-transform duration-700 group-hover:scale-105" />}
                    </div>
                    <p className="mt-3 text-sm font-medium break-words">{r.name}</p>
                    <p className="mt-1 text-sm text-muted-foreground tabular-nums">{formatPrice(r.price, currency)}</p>
                  </Link>
                </li>
              ))}
            </ul>
          </div>
        )}
      </Container>
    </section>
  );
}
