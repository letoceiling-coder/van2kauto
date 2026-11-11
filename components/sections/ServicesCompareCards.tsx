import Link from "next/link";
import { Check } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { cn } from "@/lib/utils";

export interface ServicesCompareCardsProps {
  eyebrow?: string;
  title: string;
  text?: string;
  chooseLabel: string;
  items: { title: string; text: string; price: string; href: string; points: string[]; featured?: boolean; badge?: string }[];
  /** scale: the featured card grows; badge: the featured card shows a label. */
  highlight?: "scale" | "badge";
  /** center: centered type; left: left-aligned type. */
  align?: "center" | "left";
}

/** Three tall service tiers with a featured card. */
export function ServicesCompareCards({ eyebrow, title, text, chooseLabel, items, highlight = "scale", align = "center" }: ServicesCompareCardsProps) {
  return (
    <section data-component-id="services-compare-cards" className="py-(--section-y)">
      <Container>
        <div className={cn("max-w-3xl", align === "center" && "mx-auto text-center")}>
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="services-compare-cards.eyebrow">{eyebrow}</p>}
          <h2 className="font-heading text-headline break-words" data-editable-id="services-compare-cards.title">{title}</h2>
          {text && <p className={cn("mt-5 max-w-2xl text-lg break-words text-muted-foreground", align === "center" && "mx-auto")} data-editable-id="services-compare-cards.text">{text}</p>}
        </div>
        <ul className="mt-12 grid items-stretch gap-4 md:grid-cols-3 md:py-6">
          {items.map((item, index) => (
            <li
              key={item.title}
              className={cn(
                "flex min-w-0 flex-col rounded-token border border-border bg-surface p-6",
                align === "center" ? "items-center text-center" : "items-start text-left",
                highlight === "scale" && item.featured && "z-10 shadow-premium md:scale-105",
                highlight === "badge" && item.featured && "border-accent",
              )}
            >
              {highlight === "badge" && item.badge && (
                <span className="mb-4 inline-flex rounded-full bg-accent px-3 py-1 text-xs font-medium text-accent-foreground">{item.badge}</span>
              )}
              <h3 className="font-heading text-title break-words" data-editable-id={`services-compare-cards.items.${index}.title`}>{item.title}</h3>
              <p className="mt-3 break-words text-sm text-muted-foreground">{item.text}</p>
              <p className="mt-6 font-heading text-title tabular-nums">{item.price}</p>
              <ul className="mt-6 w-full space-y-3">
                {item.points.map((point) => (
                  <li key={point} className={cn("flex gap-2 text-sm", align === "center" && "justify-center")}>
                    <Check className="mt-0.5 size-4 shrink-0 text-accent" aria-hidden="true" />
                    <span className="min-w-0 break-words">{point}</span>
                  </li>
                ))}
              </ul>
              <Link href={item.href} className="mt-8 inline-flex h-11 items-center justify-center rounded-full bg-primary px-5 text-sm font-medium text-primary-foreground">{chooseLabel}</Link>
            </li>
          ))}
        </ul>
      </Container>
    </section>
  );
}
