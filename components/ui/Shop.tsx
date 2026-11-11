"use client";

import Link from "next/link";
import { useSyncExternalStore } from "react";
import { ShoppingBag } from "lucide-react";
import type { MediaImage } from "@/components/ui/Media";
import { cn } from "@/lib/utils";

/** One spec row of a product ("Двигатель" → "Бензин"). Specs with few distinct values become catalog filters. */
export interface ShopSpec {
  label: string;
  value: string;
}

/** Catalog record. Prices are numbers (filters, sorting, totals); text is formatted with formatPrice. */
export interface ShopProduct {
  /** URL slug, latin: "toyota-alphard-2024" → /catalog/toyota-alphard-2024 */
  slug: string;
  name: string;
  category: string;
  price: number;
  oldPrice?: number;
  badge?: string;
  /** Short line under the name on cards. */
  summary: string;
  description: string;
  images: MediaImage[];
  specs: ShopSpec[];
  // ---- marketplace fields (all optional: simple shops keep working without them) ----
  brand?: string;
  /** 0–5 */
  rating?: number;
  reviewsCount?: number;
  /** Selectable options: { name: "Размер", options: ["S","M","L"] }, { name: "Цвет", options: [...] } */
  variants?: { name: string; options: string[] }[];
  /** in = в наличии, low = мало, out = нет, preorder = предзаказ */
  stock?: "in" | "low" | "out" | "preorder";
  /** Delivery in N days (0 = today, 1 = tomorrow) */
  deliveryDays?: number;
  reviews?: { author: string; rating: number; date: string; text: string }[];
}

/** "Завтра", "Послезавтра", "Через 3 дня" — delivery promise like on marketplaces (no clock: same text on server and client). */
export function deliveryLabel(days = 2): string {
  if (days <= 0) return "Сегодня";
  if (days === 1) return "Завтра";
  if (days === 2) return "Послезавтра";
  const n = days % 100, n1 = days % 10;
  const word = n > 10 && n < 20 ? "дней" : n1 === 1 ? "день" : n1 >= 2 && n1 <= 4 ? "дня" : "дней";
  return `Через ${days} ${word}`;
}

/** Russian plural: plural(3, "отзыв", "отзыва", "отзывов") -> "отзыва". */
export function plural(n: number, one: string, few: string, many: string): string {
  const a = Math.abs(n) % 100, b = a % 10;
  return a > 10 && a < 20 ? many : b === 1 ? one : b >= 2 && b <= 4 ? few : many;
}

export const discountPercent = (p: { price: number; oldPrice?: number }) =>
  p.oldPrice && p.oldPrice > p.price ? Math.round((1 - p.price / p.oldPrice) * 100) : 0;

/** Cart line id for a product + chosen options ("kurtka-x|Размер: M"). */
export const lineId = (slug: string, picked?: Record<string, string>) =>
  picked && Object.keys(picked).length ? `${slug}|${Object.entries(picked).map(([k, v]) => `${k}: ${v}`).join(", ")}` : slug;

export function formatPrice(value: number, currency = "₽") {
  return `${Math.round(value).toLocaleString("ru-RU")} ${currency}`;
}

export const productHref = (basePath: string, slug: string) => `${basePath.replace(/\/$/, "")}/${slug}`;

// ---------- tiny persistent store (localStorage), hydration-safe via useSyncExternalStore ----------
type Listener = () => void;
function createStore<T>(key: string, empty: T) {
  const listeners = new Set<Listener>();
  let cache: T | undefined;
  const read = (): T => {
    if (cache !== undefined) return cache;
    try {
      const raw = window.localStorage.getItem(key);
      cache = raw ? (JSON.parse(raw) as T) : empty;
    } catch {
      cache = empty;
    }
    return cache;
  };
  const write = (next: T) => {
    cache = next;
    try {
      window.localStorage.setItem(key, JSON.stringify(next));
    } catch {
      /* private mode: keep in memory */
    }
    listeners.forEach((l) => l());
  };
  const subscribe = (l: Listener) => {
    listeners.add(l);
    const onStorage = (e: StorageEvent) => {
      if (e.key === key) {
        cache = undefined;
        l();
      }
    };
    window.addEventListener("storage", onStorage);
    return () => {
      listeners.delete(l);
      window.removeEventListener("storage", onStorage);
    };
  };
  const use = () => useSyncExternalStore(subscribe, read, () => empty);
  return { use, read, write };
}

/** A cart line. name/price/image are a snapshot taken when the item was added, so menus and showcases
 *  work without a catalog and the checkout can always show what is in the cart. */
export interface CartLine {
  slug: string;
  qty: number;
  name?: string;
  price?: number;
  image?: MediaImage;
}
export type CartSnapshot = Pick<CartLine, "name" | "price" | "image">;

/** "6 900 ₽" / "от 1 200 ₽" -> 6900 / 1200 (cards often keep prices as text). */
export const parsePrice = (text: string) => Number(String(text).replace(/[^\d]/g, "")) || 0;
const NO_LINES: CartLine[] = [];
const NO_SLUGS: string[] = [];
const cartStore = createStore<CartLine[]>("shop:cart", NO_LINES);
const favStore = createStore<string[]>("shop:favorites", NO_SLUGS);

export function useCart() {
  const lines = cartStore.use();
  const set = (next: CartLine[]) => cartStore.write(next.filter((l) => l.qty > 0));
  return {
    lines,
    count: lines.reduce((s, l) => s + l.qty, 0),
    /** Sum of the snapshot prices (lines without a price snapshot count as 0 here; the checkout resolves them from the catalog). */
    total: lines.reduce((s, l) => s + (l.price ?? 0) * l.qty, 0),
    qtyOf: (slug: string) => lines.find((l) => l.slug === slug)?.qty ?? 0,
    add: (slug: string, qty = 1, snap?: CartSnapshot) => {
      const cur = cartStore.read();
      set(cur.some((l) => l.slug === slug) ? cur.map((l) => (l.slug === slug ? { ...l, ...snap, qty: l.qty + qty } : l)) : [...cur, { slug, qty, ...snap }]);
    },
    setQty: (slug: string, qty: number) => set(cartStore.read().map((l) => (l.slug === slug ? { ...l, qty: Math.max(0, Math.min(99, qty)) } : l))),
    remove: (slug: string) => set(cartStore.read().filter((l) => l.slug !== slug)),
    clear: () => set([]),
  };
}

export function useFavorites() {
  const slugs = favStore.use();
  return {
    slugs,
    has: (slug: string) => slugs.includes(slug),
    toggle: (slug: string) => {
      const cur = favStore.read();
      favStore.write(cur.includes(slug) ? cur.filter((s) => s !== slug) : [...cur, slug]);
    },
  };
}

export interface CartButtonProps {
  /** Cart page, e.g. "/cart". */
  href: string;
  label?: string;
}

/** Floating cart button with the item count; appears once something is in the cart. */
export function CartButton({ href, label = "Корзина" }: CartButtonProps) {
  const { count } = useCart();
  return (
    <Link
      href={href}
      aria-label={`${label}: ${count}`}
      data-cart-count={count}
      className={cn(
        "fixed right-4 bottom-4 z-40 flex h-14 items-center gap-2 rounded-full bg-primary pr-5 pl-4 text-sm font-medium text-primary-foreground shadow-premium transition-all duration-300 sm:right-6 sm:bottom-6",
        count ? "translate-y-0 opacity-100" : "pointer-events-none translate-y-4 opacity-0",
      )}
    >
      <ShoppingBag className="size-5" />
      <span className="hidden sm:inline">{label}</span>
      <span className="grid min-w-6 place-items-center rounded-full bg-primary-foreground px-1.5 text-xs text-primary tabular-nums">{count}</span>
    </Link>
  );
}
