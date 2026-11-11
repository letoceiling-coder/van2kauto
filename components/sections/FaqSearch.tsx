"use client";

import { useState } from "react";
import { Search } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { cn } from "@/lib/utils";

export interface FaqSearchItem {
  question: string;
  answer: string;
}

export interface FaqSearchProps {
  eyebrow?: string;
  title: string;
  text?: string;
  items: FaqSearchItem[];
  searchLabel: string;
  searchPlaceholder: string;
  emptyText: string;
  list?: "accordion" | "open";
  field?: "wide" | "compact";
}

/** Filters the questions that are already in props. Nothing is fetched. */
export function FaqSearch({
  eyebrow,
  title,
  text,
  items,
  searchLabel,
  searchPlaceholder,
  emptyText,
  list = "accordion",
  field = "wide",
}: FaqSearchProps) {
  const [query, setQuery] = useState("");
  const needle = query.trim().toLowerCase();
  const shown = needle ? items.filter((item) => `${item.question} ${item.answer}`.toLowerCase().includes(needle)) : items;

  return (
    <section data-component-id="faq-search" className="py-(--section-y)">
      <Container>
        <div className="grid gap-8 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:items-start">
          <div className="min-w-0">
            {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="faq-search.eyebrow">{eyebrow}</p>}
            <h2 className="text-headline break-words" data-editable-id="faq-search.title">{title}</h2>
            {text && <p className="mt-5 text-lg break-words text-muted-foreground" data-editable-id="faq-search.text">{text}</p>}
            <label className={cn("relative mt-8 block", field === "compact" ? "max-w-xs" : "w-full")}>
              <Search className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
              <input
                type="search"
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                placeholder={searchPlaceholder}
                aria-label={searchLabel}
                className="h-12 w-full rounded-full border border-border bg-background pr-4 pl-11 text-sm outline-none focus:border-foreground"
              />
            </label>
          </div>
          <div className="min-w-0">
            {shown.length === 0 ? (
              <p className="break-words text-muted-foreground">{emptyText}</p>
            ) : list === "open" ? (
              <div className="grid gap-4">
                {shown.map((item) => (
                  <article key={item.question} className="rounded-token border border-border bg-surface p-5">
                    <h3 className="text-lg break-words">{item.question}</h3>
                    <p className="mt-3 break-words text-muted-foreground">{item.answer}</p>
                  </article>
                ))}
              </div>
            ) : (
              <div className="divide-y divide-border border-y border-border">
                {shown.map((item) => (
                  <details key={item.question} className="group py-5">
                    <summary className="flex cursor-pointer list-none items-start justify-between gap-4 text-lg [&::-webkit-details-marker]:hidden">
                      <span className="min-w-0 break-words">{item.question}</span>
                      <span className="mt-2 size-2 shrink-0 border-r-2 border-b-2 border-foreground transition-transform group-open:rotate-45" aria-hidden="true" />
                    </summary>
                    <p className="mt-4 max-w-2xl break-words text-muted-foreground">{item.answer}</p>
                  </details>
                ))}
              </div>
            )}
          </div>
        </div>
      </Container>
    </section>
  );
}
