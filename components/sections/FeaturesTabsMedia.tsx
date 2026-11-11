"use client";

import Image from "next/image";
import { useState } from "react";
import { Container } from "@/components/layout/Container";
import type { MediaImage } from "@/components/ui/Media";
import { asset, cn } from "@/lib/utils";

export interface FeaturesTabsMediaProps {
  eyebrow?: string;
  title: string;
  text?: string;
  items: { label: string; title: string; text: string; image: MediaImage }[];
  tabs?: "top" | "side";
  image?: "right" | "background";
}

export function FeaturesTabsMedia({ eyebrow, title, text, items, tabs = "top", image = "right" }: FeaturesTabsMediaProps) {
  const [index, setIndex] = useState(0);
  const current = items[index] ?? items[0];

  return (
    <section data-component-id="features-tabs-media" className="overflow-hidden py-(--section-y)">
      <Container>
        <div className="mb-8 min-w-0 max-w-2xl">
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="tabs.eyebrow">{eyebrow}</p>}
          <h2 className="font-heading text-headline break-words" data-editable-id="tabs.title">{title}</h2>
          {text && <p className="mt-4 break-words text-lg text-muted-foreground">{text}</p>}
        </div>
        <div className={cn(tabs === "side" ? "grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)]" : "flex flex-col gap-6")}>
          <div role="tablist" aria-label={title} className={cn("flex gap-2", tabs === "side" ? "flex-row overflow-x-auto lg:flex-col lg:overflow-visible" : "overflow-x-auto")}>
            {items.map((item, k) => (
              <button
                key={item.label + k}
                type="button"
                role="tab"
                aria-selected={k === index}
                onClick={() => setIndex(k)}
                className={cn(
                  "shrink-0 break-words px-4 py-3 text-left text-sm",
                  tabs === "side" ? "rounded-token border border-border" : "rounded-full",
                  k === index ? "bg-primary text-primary-foreground" : "bg-muted text-foreground",
                )}
              >
                {item.label}
              </button>
            ))}
          </div>
          {current && (
            <div className={cn("min-w-0 overflow-hidden rounded-token", image === "right" ? "grid items-center gap-6 bg-surface p-5 sm:p-8 lg:grid-cols-2" : "relative min-h-80 bg-black")}>
              {image === "background" && (
                <Image src={asset(current.image.src)} alt={current.image.alt} fill sizes="(min-width: 1024px) 60vw, 100vw" className="object-cover" style={{ objectPosition: current.image.focal ?? "50% 50%" }} />
              )}
              <div className={cn("min-w-0", image === "background" && "relative z-10 m-5 bg-gradient-to-t from-black/60 p-5 text-white sm:m-8")}>
                <h3 className="break-words font-heading text-title" data-editable-id="tabs.panel">{current.title}</h3>
                <p className={cn("mt-3 break-words", image === "right" && "text-muted-foreground")}>{current.text}</p>
              </div>
              {image === "right" && (
                <div className="relative min-h-56 overflow-hidden rounded-token bg-muted">
                  <Image src={asset(current.image.src)} alt={current.image.alt} fill sizes="(min-width: 1024px) 40vw, 100vw" className="object-cover" style={{ objectPosition: current.image.focal ?? "50% 50%" }} />
                </div>
              )}
            </div>
          )}
        </div>
      </Container>
    </section>
  );
}
