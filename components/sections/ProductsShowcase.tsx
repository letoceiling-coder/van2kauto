"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useMemo, useState } from "react";
import { AnimatePresence, motion, useReducedMotion } from "motion/react";
import { ArrowUpRight, Check, Heart, ShoppingBag } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { Reveal } from "@/components/motion/Reveal";
import type { MediaImage } from "@/components/ui/Media";
import { parsePrice, useCart } from "@/components/ui/Shop";
import { asset, cn } from "@/lib/utils";

export interface ShowcaseProduct {
  name: string;
  category: string;
  price: string;
  oldPrice?: string;
  badge?: string;
  image: MediaImage;
  /** Second photo shown on hover (detail, in use, other angle). */
  hoverImage?: MediaImage;
  /** Product page; makes the photo and the name clickable. */
  href?: string;
}

export interface ProductsShowcaseProps {
  eyebrow?: string;
  title: string;
  text?: string;
  cta?: { label: string; href: string };
  products: ShowcaseProduct[];
  /** Card photo frame. */
  cardShape?: "portrait" | "square" | "landscape";
  /** Cards per row on desktop. */
  columns?: 3 | 4;
}

const CARD = { portrait: "4 / 5", square: "1 / 1", landscape: "4 / 3" } as const;

/** E-commerce product grid: category chips, photo swap on hover, badges, wishlist, add-to-bag with a toast. */
export function ProductsShowcase({ eyebrow, title, text, cta, products, cardShape = "portrait", columns = 4 }: ProductsShowcaseProps) {
  const reduce = useReducedMotion();
  const cats = useMemo(() => ["Все", ...Array.from(new Set(products.map((p) => p.category)))], [products]);
  const [cat, setCat] = useState("Все");
  const [liked, setLiked] = useState<Record<string, boolean>>({});
  const [toast, setToast] = useState<string | null>(null);
  const cart = useCart();
  // catalog cards link to /catalog/<slug>: same id as the product page, so the cart merges them
  const cartId = (p: ShowcaseProduct) => p.href?.match(/\/catalog\/([^/#?]+)/)?.[1] ?? `item:${p.name}`;
  useEffect(() => {
    if (!toast) return;
    const t = setTimeout(() => setToast(null), 2200);
    return () => clearTimeout(t);
  }, [toast]);
  const list = cat === "Все" ? products : products.filter((p) => p.category === cat);

  return (
    <section data-component-id="products-showcase" className="py-(--section-y)">
      <Container>
        <div className="mb-10 flex flex-col gap-6 lg:mb-14 lg:flex-row lg:items-end lg:justify-between">
          <Reveal>
            {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="products.eyebrow">{eyebrow}</p>}
            <h2 className="text-headline max-w-[16ch]" data-editable-id="products.title">{title}</h2>
            {text && <p className="mt-5 max-w-xl text-lg text-muted-foreground" data-editable-id="products.text">{text}</p>}
          </Reveal>
          {cta && (
            <Link href={cta.href} className="group inline-flex items-center gap-2 self-start text-sm font-medium lg:self-auto">
              <span className="border-b border-current pb-0.5">{cta.label}</span>
              <ArrowUpRight className="size-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
            </Link>
          )}
        </div>

        {cats.length > 2 && (
          <div className="-mx-4 mb-8 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0" role="tablist" aria-label="Категории">
            {cats.map((c) => (
              <button
                key={c}
                type="button"
                role="tab"
                aria-selected={c === cat}
                onClick={() => setCat(c)}
                className={cn("relative h-10 shrink-0 rounded-full px-5 text-sm transition-colors", c === cat ? "text-primary-foreground" : "border border-border text-foreground hover:bg-foreground/5")}
              >
                {c === cat && <motion.span layoutId="products-chip" className="absolute inset-0 rounded-full bg-primary" transition={reduce ? { duration: 0 } : { type: "spring", stiffness: 400, damping: 34 }} />}
                <span className="relative">{c}</span>
              </button>
            ))}
          </div>
        )}

        <motion.ul layout className={cn("grid grid-cols-2 gap-x-3 gap-y-10 sm:gap-x-5", columns === 3 ? "lg:grid-cols-3" : "lg:grid-cols-4")}>
          <AnimatePresence mode="popLayout">
            {list.map((p) => (
              <motion.li
                key={p.name}
                layout
                initial={{ opacity: 0, y: 16 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, scale: 0.96 }}
                transition={reduce ? { duration: 0 } : { duration: 0.4, ease: [0.22, 1, 0.36, 1] }}
                className="group"
              >
                <div className="radius-xl relative overflow-hidden bg-muted" style={{ aspectRatio: CARD[cardShape] ?? CARD.portrait }}>
                  {p.href && <Link href={p.href} aria-label={p.name} className="absolute inset-0 z-[1]" />}
                  <Image src={asset(p.image.src)} alt={p.image.alt} fill sizes="(min-width: 1024px) 25vw, 50vw" className={cn("object-cover transition-[opacity,transform] duration-700 ease-out group-hover:scale-105", p.hoverImage && "group-hover:opacity-0")} style={{ objectPosition: p.image.focal ?? "50% 50%" }} />
                  {p.hoverImage && (
                    <Image src={asset(p.hoverImage.src)} alt={p.hoverImage.alt} fill sizes="(min-width: 1024px) 25vw, 50vw" className="object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100" style={{ objectPosition: p.hoverImage.focal ?? "50% 50%" }} />
                  )}
                  {p.badge && <span className="glass pointer-events-none absolute top-3 left-3 z-[2] rounded-full px-3 py-1 text-xs font-medium">{p.badge}</span>}
                  <button
                    type="button"
                    aria-label={liked[p.name] ? "Убрать из избранного" : "В избранное"}
                    aria-pressed={!!liked[p.name]}
                    onClick={() => setLiked((s) => ({ ...s, [p.name]: !s[p.name] }))}
                    className="glass absolute top-3 right-3 z-[2] grid size-9 place-items-center rounded-full transition-transform active:scale-90"
                  >
                    <Heart className={cn("size-4", liked[p.name] && "fill-accent text-accent")} />
                  </button>
                  <div className="absolute inset-x-3 bottom-3 z-[2] transition-all duration-500 lg:translate-y-3 lg:opacity-0 lg:group-hover:translate-y-0 lg:group-hover:opacity-100">
                    <button
                      type="button"
                      onClick={() => { cart.add(cartId(p), 1, { name: p.name, price: parsePrice(p.price), image: p.image }); setToast(p.name); }}
                      className="flex h-11 w-full items-center justify-center gap-2 rounded-full bg-primary text-sm font-medium text-primary-foreground shadow-premium"
                    >
                      <ShoppingBag className="size-4" />
                      <span className="hidden sm:inline">В корзину</span>
                    </button>
                  </div>
                </div>
                <div className="mt-4 flex items-start justify-between gap-3">
                  <div className="min-w-0 flex-1 break-words">
                    <p className="text-xs text-muted-foreground">{p.category}</p>
                    <h3 className="mt-1 text-[0.95rem] leading-snug font-medium sm:text-base">{p.href ? <Link href={p.href} className="hover:underline underline-offset-4">{p.name}</Link> : p.name}</h3>
                  </div>
                  <div className="max-w-[50%] text-right break-words">
                    <p className="font-medium tabular-nums">{p.price}</p>
                    {p.oldPrice && <p className="text-xs text-muted-foreground tabular-nums line-through">{p.oldPrice}</p>}
                  </div>
                </div>
              </motion.li>
            ))}
          </AnimatePresence>
        </motion.ul>
      </Container>

      <AnimatePresence>
        {toast && (
          <motion.div
            role="status"
            initial={{ opacity: 0, y: 24 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: 24 }}
            className="fixed bottom-5 left-1/2 z-50 flex max-w-[calc(100vw-2rem)] -translate-x-1/2 items-center gap-3 rounded-full bg-foreground py-2 pr-5 pl-2 text-sm text-background shadow-premium"
          >
            <span className="grid size-8 shrink-0 place-items-center rounded-full bg-accent text-accent-foreground">
              <Check className="size-4" />
            </span>
            <span className="truncate">«{toast}» в корзине</span>
          </motion.div>
        )}
      </AnimatePresence>
    </section>
  );
}
