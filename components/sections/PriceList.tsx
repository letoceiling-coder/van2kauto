"use client";

import Link from "next/link";
import { useState } from "react";
import { ChevronDown, Search } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { cn } from "@/lib/utils";

export interface PriceGroup {
  name: string;
  items: { name: string; price: string; note?: string }[];
}

export interface PriceListProps {
  eyebrow?: string;
  title: string;
  text?: string;
  groups: PriceGroup[];
  /** Search over service names (useful for long lists). */
  searchable?: boolean;
  cta?: { label: string; href: string };
  /** Small print: "Цены ориентировочные, точная стоимость — после консультации". */
  note?: string;
  /** "accordion": groups collapse; "columns": all groups open in two columns. */
  layout?: "accordion" | "columns";
}

/** Service price list (clinics, lawyers, salons, repair): grouped rows with dotted leaders, search, collapsible groups. */
export function PriceList({ eyebrow, title, text, groups, searchable = true, cta, note, layout = "accordion" }: PriceListProps) {
  const [q, setQ] = useState("");
  const [open, setOpen] = useState<number | null>(0);
  const needle = q.trim().toLowerCase();
  const shown = groups.map((g) => ({ ...g, items: needle ? g.items.filter((i) => i.name.toLowerCase().includes(needle)) : g.items })).filter((g) => g.items.length);

  const rows = (g: PriceGroup) => (
    <ul className="divide-y divide-border">
      {g.items.map((it) => (
        <li key={it.name} className="flex items-baseline gap-3 py-4">
          <span className="min-w-0 break-words">{it.name}{it.note && <span className="block text-xs text-muted-foreground">{it.note}</span>}</span>
          <span className="mb-1 flex-1 border-b border-dotted border-border" aria-hidden="true" />
          <span className="shrink-0 font-medium tabular-nums">{it.price}</span>
        </li>
      ))}
    </ul>
  );

  return (
    <section data-component-id="price-list" className="py-(--section-y)">
      <Container>
        <div className="grid gap-10 lg:grid-cols-[1fr_2fr] lg:gap-16">
          <div className="lg:sticky lg:top-24 lg:self-start">
            {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="prices.eyebrow">{eyebrow}</p>}
            <h2 className="text-headline" data-editable-id="prices.title">{title}</h2>
            {text && <p className="mt-5 text-lg text-muted-foreground" data-editable-id="prices.text">{text}</p>}
            {searchable && (
              <label className="relative mt-8 block">
                <Search className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" />
                <input type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Найти услугу" aria-label="Найти услугу"
                  className="h-12 w-full rounded-full border border-border bg-background pr-4 pl-11 text-sm outline-none focus:border-primary" />
              </label>
            )}
            {cta && <Link href={cta.href} className="mt-6 inline-flex h-12 items-center rounded-full bg-primary px-6 text-sm font-medium text-primary-foreground">{cta.label}</Link>}
            {note && <p className="mt-6 text-xs text-muted-foreground">{note}</p>}
          </div>

          <div className="min-w-0">
            {shown.length === 0 && <p className="text-muted-foreground">Ничего не найдено — уточните запрос или позвоните нам.</p>}
            {layout === "columns" || needle ? (
              <div className="grid gap-10 md:grid-cols-2">
                {shown.map((g) => (
                  <div key={g.name}>
                    <h3 className="text-title mb-2">{g.name}</h3>
                    {rows(g)}
                  </div>
                ))}
              </div>
            ) : (
              <div className="divide-y divide-border border-y border-border">
                {shown.map((g, k) => (
                  <div key={g.name}>
                    <button type="button" onClick={() => setOpen(open === k ? null : k)} aria-expanded={open === k}
                      className="flex w-full items-center justify-between gap-4 py-6 text-left">
                      <span className="text-title">{g.name}</span>
                      <span className="flex items-center gap-3 text-sm text-muted-foreground">{g.items.length}<ChevronDown className={cn("size-5 transition-transform", open === k && "rotate-180")} /></span>
                    </button>
                    {open === k && <div className="pb-6">{rows(g)}</div>}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </Container>
    </section>
  );
}
