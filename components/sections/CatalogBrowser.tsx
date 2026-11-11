"use client";

import Image from "next/image";
import Link from "next/link";
import { useMemo, useState } from "react";
import { Check, Heart, Search, ShoppingBag, SlidersHorizontal, X } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { formatPrice, productHref, useCart, useFavorites, type ShopProduct } from "@/components/ui/Shop";
import { asset, cn } from "@/lib/utils";

export interface CatalogBrowserProps {
  eyebrow?: string;
  title: string;
  text?: string;
  /** All catalog records (usually `catalog.products` from content/catalog.ts). */
  products: ShopProduct[];
  /** Base path of product pages: "/catalog" → /catalog/<slug>. */
  basePath: string;
  /** "sidebar": filters in a left column (large catalogs); "top": compact filter bar above the grid. */
  layout?: "sidebar" | "top";
  /** Cards per page. */
  pageSize?: number;
  /** Shown under the grid when the data is a demo, e.g. "Демо-данные: цены и наличие условные". */
  demoNote?: string;
  currency?: string;
}

type Sort = "popular" | "price-asc" | "price-desc" | "name";
const SORTS: { id: Sort; label: string }[] = [
  { id: "popular", label: "Популярные" },
  { id: "price-asc", label: "Сначала дешевле" },
  { id: "price-desc", label: "Сначала дороже" },
  { id: "name", label: "По названию" },
];

/** Spec labels that make sense as filters: present in most products, 2–8 distinct values, and values that repeat
 *  (a spec unique to every product, like an exact width, is not a useful filter). Numbers sort by value. */
function facetsOf(products: ShopProduct[]) {
  const values = new Map<string, Set<string>>();
  const seen = new Map<string, number>();
  for (const p of products)
    for (const s of p.specs) {
      if (!values.has(s.label)) values.set(s.label, new Set());
      values.get(s.label)!.add(s.value);
      seen.set(s.label, (seen.get(s.label) ?? 0) + 1);
    }
  return [...values.entries()]
    .filter(([label, v]) => v.size >= 2 && v.size <= 8 && v.size < (seen.get(label) ?? 0) && (seen.get(label) ?? 0) >= products.length / 2)
    .slice(0, 5)
    .map(([label, v]) => ({ label, values: [...v].sort((a, b) => a.localeCompare(b, "ru", { numeric: true })) }));
}

/** Store/catalog page: search, category chips, filters built from product specs, price range, favorites, sorting, pagination. */
export function CatalogBrowser({ eyebrow, title, text, products, basePath, layout = "sidebar", pageSize = 9, demoNote, currency = "₽" }: CatalogBrowserProps) {
  const cart = useCart();
  const fav = useFavorites();
  const categories = useMemo(() => [...new Set(products.map((p) => p.category))], [products]);
  const facets = useMemo(() => facetsOf(products), [products]);

  const [q, setQ] = useState("");
  const [cat, setCat] = useState<string | null>(null);
  const [picked, setPicked] = useState<Record<string, string[]>>({});
  const [minPrice, setMinPrice] = useState("");
  const [maxPrice, setMaxPrice] = useState("");
  const [onlyFav, setOnlyFav] = useState(false);
  const [sort, setSort] = useState<Sort>("popular");
  const [page, setPage] = useState(1);
  const [panel, setPanel] = useState(false);
  const [added, setAdded] = useState<string | null>(null);

  const touch = <T,>(fn: (v: T) => void) => (v: T) => {
    fn(v);
    setPage(1);
  };
  const activeCount = Object.values(picked).reduce((s, v) => s + v.length, 0) + (minPrice ? 1 : 0) + (maxPrice ? 1 : 0) + (onlyFav ? 1 : 0);
  const reset = () => {
    setQ("");
    setCat(null);
    setPicked({});
    setMinPrice("");
    setMaxPrice("");
    setOnlyFav(false);
    setPage(1);
  };

  const list = useMemo(() => {
    const needle = q.trim().toLowerCase();
    const lo = Number(minPrice.replace(/\D/g, "")) || 0;
    const hi = Number(maxPrice.replace(/\D/g, "")) || Infinity;
    const out = products.filter((p) => {
      if (cat && p.category !== cat) return false;
      if (onlyFav && !fav.slugs.includes(p.slug)) return false;
      if (p.price < lo || p.price > hi) return false;
      for (const [label, vals] of Object.entries(picked)) {
        if (!vals.length) continue;
        const v = p.specs.find((s) => s.label === label)?.value;
        if (!v || !vals.includes(v)) return false;
      }
      if (needle && ![p.name, p.category, p.summary, ...p.specs.map((s) => s.value)].join(" ").toLowerCase().includes(needle)) return false;
      return true;
    });
    if (sort === "price-asc") out.sort((a, b) => a.price - b.price);
    if (sort === "price-desc") out.sort((a, b) => b.price - a.price);
    if (sort === "name") out.sort((a, b) => a.name.localeCompare(b.name, "ru"));
    return out;
  }, [products, q, cat, picked, minPrice, maxPrice, onlyFav, sort, fav.slugs]);

  const pages = Math.max(1, Math.ceil(list.length / pageSize));
  const cur = Math.min(page, pages);
  const shown = list.slice((cur - 1) * pageSize, cur * pageSize);

  const toggleValue = (label: string, value: string) =>
    touch(setPicked)({ ...picked, [label]: picked[label]?.includes(value) ? picked[label].filter((v) => v !== value) : [...(picked[label] ?? []), value] });

  const filters = (
    <div className="flex flex-col gap-7">
      {facets.map((f) => (
        <fieldset key={f.label}>
          <legend className="mb-3 text-xs tracking-[0.14em] text-muted-foreground uppercase">{f.label}</legend>
          <div className="flex flex-wrap gap-2">
            {f.values.map((v) => {
              const on = picked[f.label]?.includes(v);
              return (
                <button key={v} type="button" aria-pressed={!!on} onClick={() => toggleValue(f.label, v)}
                  className={cn("h-9 rounded-full border px-3.5 text-sm transition-colors", on ? "border-primary bg-primary text-primary-foreground" : "border-border hover:bg-foreground/5")}>
                  {v}
                </button>
              );
            })}
          </div>
        </fieldset>
      ))}
      <fieldset>
        <legend className="mb-3 text-xs tracking-[0.14em] text-muted-foreground uppercase">Цена, {currency}</legend>
        <div className="grid grid-cols-2 gap-2">
          <input inputMode="numeric" placeholder="от" value={minPrice} onChange={(e) => touch(setMinPrice)(e.target.value)} aria-label="Цена от"
            className="h-10 w-full min-w-0 rounded-token border border-border bg-background px-3 text-sm tabular-nums outline-none focus:border-primary" />
          <input inputMode="numeric" placeholder="до" value={maxPrice} onChange={(e) => touch(setMaxPrice)(e.target.value)} aria-label="Цена до"
            className="h-10 w-full min-w-0 rounded-token border border-border bg-background px-3 text-sm tabular-nums outline-none focus:border-primary" />
        </div>
      </fieldset>
      <label className="flex cursor-pointer items-center gap-3 text-sm">
        <input type="checkbox" checked={onlyFav} onChange={(e) => touch(setOnlyFav)(e.target.checked)} className="size-4 accent-primary" />
        Только избранное {fav.slugs.length ? `(${fav.slugs.length})` : ""}
      </label>
      {activeCount > 0 && (
        <button type="button" onClick={reset} className="inline-flex items-center gap-1.5 self-start text-sm text-muted-foreground underline-offset-4 hover:text-foreground hover:underline">
          <X className="size-4" /> Сбросить фильтры
        </button>
      )}
    </div>
  );

  const sidebar = layout === "sidebar" && facets.length > 0;

  return (
    <section data-component-id="catalog-browser" className="py-(--section-y)">
      <Container>
        <div className="mb-8 max-w-2xl lg:mb-12">
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="catalog.eyebrow">{eyebrow}</p>}
          <h1 className="text-headline" data-editable-id="catalog.title">{title}</h1>
          {text && <p className="mt-5 text-lg text-muted-foreground" data-editable-id="catalog.text">{text}</p>}
        </div>

        {/* search + sort + mobile filter toggle */}
        <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
          <label className="relative flex-1">
            <Search className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" />
            <input type="search" value={q} onChange={(e) => touch(setQ)(e.target.value)} placeholder="Поиск по названию и характеристикам" aria-label="Поиск"
              className="h-12 w-full rounded-full border border-border bg-background pr-4 pl-11 text-sm outline-none focus:border-primary" />
          </label>
          <div className="flex gap-2">
            <button type="button" onClick={() => setPanel((v) => !v)} aria-expanded={panel}
              className={cn("inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-full border border-border px-5 text-sm sm:flex-none", sidebar && "lg:hidden")}>
              <SlidersHorizontal className="size-4" /> Фильтры{activeCount ? ` · ${activeCount}` : ""}
            </button>
            <select value={sort} onChange={(e) => setSort(e.target.value as Sort)} aria-label="Сортировка"
              className="h-12 flex-1 rounded-full border border-border bg-background px-4 text-sm sm:flex-none">
              {SORTS.map((s) => <option key={s.id} value={s.id}>{s.label}</option>)}
            </select>
          </div>
        </div>

        {categories.length > 1 && (
          <div className="-mx-4 mb-8 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:px-0" role="tablist" aria-label="Категории">
            {[null, ...categories].map((c) => (
              <button key={c ?? "all"} type="button" role="tab" aria-selected={cat === c} onClick={() => touch(setCat)(c)}
                className={cn("h-10 shrink-0 rounded-full px-5 text-sm transition-colors", cat === c ? "bg-foreground text-background" : "border border-border hover:bg-foreground/5")}>
                {c ?? "Все"}
              </button>
            ))}
          </div>
        )}

        <div className={cn("grid gap-10", sidebar && "lg:grid-cols-[16rem_1fr]")}>
          {(sidebar || panel) && (
            <aside className={cn(sidebar ? (panel ? "block" : "hidden lg:block") : "block", "rounded-token border border-border p-5 lg:sticky lg:top-24 lg:self-start", !sidebar && "mb-2")} aria-label="Фильтры">
              {filters}
            </aside>
          )}

          <div className="min-w-0">
            <p className="mb-5 text-sm text-muted-foreground" aria-live="polite">Найдено: {list.length}</p>
            {shown.length === 0 ? (
              <div className="grid place-items-center rounded-token border border-dashed border-border px-6 py-16 text-center">
                <p className="text-lg font-medium">Ничего не нашлось</p>
                <p className="mt-2 max-w-sm text-sm text-muted-foreground">Попробуйте изменить запрос или сбросить фильтры.</p>
                <button type="button" onClick={reset} className="mt-6 h-11 rounded-full bg-primary px-6 text-sm font-medium text-primary-foreground">Сбросить фильтры</button>
              </div>
            ) : (
              <ul className={cn("grid grid-cols-1 gap-x-5 gap-y-10 sm:grid-cols-2", sidebar ? "xl:grid-cols-3" : "lg:grid-cols-3 xl:grid-cols-4")}>
                {shown.map((p) => {
                  const href = productHref(basePath, p.slug);
                  const liked = fav.has(p.slug);
                  return (
                    <li key={p.slug} className="group flex flex-col">
                      <div className="radius-xl relative overflow-hidden bg-muted" style={{ aspectRatio: "4 / 3" }}>
                        <Link href={href} aria-label={p.name} className="absolute inset-0">
                          {p.images[0] && (
                            <Image src={asset(p.images[0].src)} alt={p.images[0].alt} fill sizes="(min-width: 1280px) 25vw, (min-width: 640px) 50vw, 100vw"
                              className="object-cover transition-transform duration-700 ease-out group-hover:scale-105" style={{ objectPosition: p.images[0].focal ?? "50% 50%" }} />
                          )}
                        </Link>
                        {p.badge && <span className="glass pointer-events-none absolute top-3 left-3 rounded-full px-3 py-1 text-xs font-medium">{p.badge}</span>}
                        <button type="button" onClick={() => fav.toggle(p.slug)} aria-pressed={liked} aria-label={liked ? "Убрать из избранного" : "В избранное"}
                          className="glass absolute top-3 right-3 grid size-9 place-items-center rounded-full transition-transform active:scale-90">
                          <Heart className={cn("size-4", liked && "fill-accent text-accent")} />
                        </button>
                      </div>
                      <div className="mt-4 flex flex-1 flex-col">
                        <p className="text-xs text-muted-foreground">{p.category}</p>
                        <h3 className="mt-1 font-medium break-words"><Link href={href} className="hover:underline underline-offset-4">{p.name}</Link></h3>
                        <p className="mt-1.5 line-clamp-2 text-sm text-muted-foreground">{p.summary}</p>
                        <div className="mt-auto flex items-end justify-between gap-3 pt-4">
                          <div className="min-w-0">
                            <p className="font-medium tabular-nums">{formatPrice(p.price, currency)}</p>
                            {p.oldPrice && p.oldPrice > p.price && <p className="text-xs text-muted-foreground tabular-nums line-through">{formatPrice(p.oldPrice, currency)}</p>}
                          </div>
                          <button type="button" aria-label={`В корзину: ${p.name}`}
                            onClick={() => { cart.add(p.slug); setAdded(p.slug); setTimeout(() => setAdded((s) => (s === p.slug ? null : s)), 1600); }}
                            className="inline-flex h-10 shrink-0 items-center gap-2 rounded-full bg-primary px-4 text-sm font-medium text-primary-foreground transition-transform active:scale-95">
                            {added === p.slug ? <Check className="size-4" /> : <ShoppingBag className="size-4" />}
                            <span>{added === p.slug ? "Добавлено" : "В корзину"}</span>
                          </button>
                        </div>
                      </div>
                    </li>
                  );
                })}
              </ul>
            )}

            {pages > 1 && (
              <nav className="mt-12 flex flex-wrap items-center justify-center gap-2" aria-label="Страницы каталога">
                {Array.from({ length: pages }, (_, i) => i + 1).map((n) => (
                  <button key={n} type="button" aria-current={n === cur ? "page" : undefined} onClick={() => { setPage(n); window.scrollTo({ top: 0, behavior: "smooth" }); }}
                    className={cn("size-10 rounded-full text-sm tabular-nums", n === cur ? "bg-foreground text-background" : "border border-border hover:bg-foreground/5")}>
                    {n}
                  </button>
                ))}
              </nav>
            )}
            {demoNote && <p className="mt-10 text-xs text-muted-foreground">{demoNote}</p>}
          </div>
        </div>
      </Container>
    </section>
  );
}
