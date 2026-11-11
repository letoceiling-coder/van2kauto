"use client";

import Image from "next/image";
import { useState } from "react";
import { Container } from "@/components/layout/Container";
import type { MediaImage } from "@/components/ui/Media";
import { asset, cn } from "@/lib/utils";

export interface FeaturesListPreviewProps {
  eyebrow?: string;
  title: string;
  text?: string;
  items: { title: string; text: string; image: MediaImage }[];
  preview?: "right" | "left";
  list?: "divided" | "cards";
}

export function FeaturesListPreview({ eyebrow, title, text, items, preview = "right", list = "divided" }: FeaturesListPreviewProps) {
  const [active, setActive] = useState(0);
  const current = items[active] ?? items[0];

  return (
    <section data-component-id="features-list-preview" className="overflow-hidden py-(--section-y)">
      <Container>
        <div className="mb-10 min-w-0 max-w-2xl">
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="preview.eyebrow">{eyebrow}</p>}
          <h2 className="font-heading text-headline break-words" data-editable-id="preview.title">{title}</h2>
          {text && <p className="mt-4 break-words text-lg text-muted-foreground">{text}</p>}
        </div>
        <div className="grid items-start gap-8 lg:grid-cols-2">
          <ul className={cn("min-w-0", preview === "left" && "lg:order-2", list === "cards" ? "space-y-3" : "divide-y divide-border border-y border-border")}>
            {items.map((item, k) => (
              <li key={item.title + k}>
                <button
                  type="button"
                  onMouseEnter={() => setActive(k)}
                  onClick={() => setActive(k)}
                  aria-pressed={k === active}
                  className={cn("w-full min-w-0 py-4 text-left", list === "cards" && "radius-xl border border-border bg-surface px-4", k === active && list === "cards" && "border-primary")}
                >
                  <span className="block break-words font-heading text-title" data-editable-id={`preview.${k}.title`}>{item.title}</span>
                  <span className={cn("mt-1 block break-words text-sm text-muted-foreground", k !== active && "hidden")}>{item.text}</span>
                </button>
              </li>
            ))}
          </ul>
          {current && (
            <figure className={cn("min-w-0", preview === "left" && "lg:order-1")}>
              <div className="relative overflow-hidden rounded-token bg-muted" style={{ aspectRatio: "4 / 3" }}>
                <Image src={asset(current.image.src)} alt={current.image.alt} fill sizes="(min-width: 1024px) 50vw, 100vw" className="object-cover" style={{ objectPosition: current.image.focal ?? "50% 50%" }} />
              </div>
              <figcaption className="mt-3 break-words text-sm text-muted-foreground">{current.text}</figcaption>
            </figure>
          )}
        </div>
      </Container>
    </section>
  );
}
