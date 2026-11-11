"use client";

import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import { Container } from "@/components/layout/Container";
import type { MediaImage } from "@/components/ui/Media";
import { asset, cn } from "@/lib/utils";

export interface CursorCase {
  title: string;
  result: string;
  href: string;
  image: MediaImage;
}

export interface PortfolioCursorListProps {
  eyebrow?: string;
  title: string;
  text?: string;
  cases: CursorCase[];
  list?: "index" | "plain";
  preview?: "right" | "behind";
}

function pad(index: number) {
  return String(index + 1).padStart(2, "0");
}

/** Case titles with a preview that follows the active row by index, not the page cursor. */
export function PortfolioCursorList({ eyebrow, title, text, cases, list = "index", preview = "right" }: PortfolioCursorListProps) {
  const [active, setActive] = useState(0);
  const safeIndex = Math.min(active, Math.max(cases.length - 1, 0));
  const current = cases[safeIndex];
  const travel = cases.length <= 1 ? 0 : (safeIndex / (cases.length - 1)) * 58;

  const rows = (
    <ul className="relative z-10 min-w-0">
      {cases.map((item, index) => (
        <li key={`${item.href}-${index}`}>
          <Link
            href={item.href}
            aria-current={index === active ? "true" : undefined}
            onPointerEnter={(event) => {
              if (event.pointerType === "mouse") setActive(index);
            }}
            onFocus={(event) => {
              if (event.currentTarget.matches(":focus-visible")) setActive(index);
            }}
            className={cn("block min-w-0 border-b border-border py-5", list === "plain" ? "py-7" : "grid grid-cols-[3rem_minmax(0,1fr)] gap-3")}
          >
            {list === "index" && <span className="font-heading text-muted-foreground">{pad(index)}</span>}
            <span className="min-w-0">
              <span className={cn("block break-words", list === "plain" ? "font-heading text-title" : "font-heading text-lg")}>{item.title}</span>
              <span className="mt-1 block text-sm text-muted-foreground break-words">{item.result}</span>
            </span>
          </Link>
        </li>
      ))}
    </ul>
  );

  const frame = (sizes: string) =>
    current ? (
      <div className="radius-xl relative h-full min-h-40 overflow-hidden bg-muted">
        <Image src={asset(current.image.src)} alt={current.image.alt} fill sizes={sizes} className="object-cover" style={{ objectPosition: current.image.focal ?? "50% 50%" }} />
      </div>
    ) : null;

  return (
    <section data-component-id="portfolio-cursor-list" className="py-(--section-y)">
      <Container>
        <header className="mb-10 max-w-3xl">
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="portfolio-cursor-list.eyebrow">{eyebrow}</p>}
          <h2 className="text-headline font-heading break-words" data-editable-id="portfolio-cursor-list.title">{title}</h2>
          {text && <p className="mt-5 text-lg text-muted-foreground" data-editable-id="portfolio-cursor-list.text">{text}</p>}
        </header>
        {current && <div className="mb-6 lg:hidden">{frame("(min-width: 1024px) 28vw, 100vw")}</div>}
        {preview === "behind" ? (
          <div className="relative overflow-hidden">
            <div className="pointer-events-none absolute inset-x-0 hidden h-[42%] overflow-hidden opacity-40 lg:block" style={{ top: `${travel}%` }}>
              {frame("(min-width: 1024px) 40vw, 100vw")}
            </div>
            {rows}
          </div>
        ) : (
          <div className="lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,18rem)] lg:gap-10">
            {rows}
            <div className="relative hidden min-h-80 lg:block">
              <div className="absolute inset-x-0 h-[42%]" style={{ top: `${travel}%` }}>
                {frame("(min-width: 1024px) 18rem, 100vw")}
              </div>
            </div>
          </div>
        )}
      </Container>
    </section>
  );
}
