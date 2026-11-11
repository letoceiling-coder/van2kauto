import Link from "next/link";
import { Plus } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { cn } from "@/lib/utils";

export interface FaqWithCtaItem {
  question: string;
  answer: string;
}

export interface FaqWithCtaProps {
  eyebrow?: string;
  title: string;
  text?: string;
  items: FaqWithCtaItem[];
  ctaTitle: string;
  ctaText: string;
  ctaLink: { label: string; href: string };
  cta?: "side" | "bottom";
  list?: "plain" | "boxed";
}

/** Accordion plus a card for the person who did not find an answer. */
export function FaqWithCta({
  eyebrow,
  title,
  text,
  items,
  ctaTitle,
  ctaText,
  ctaLink,
  cta = "side",
  list = "plain",
}: FaqWithCtaProps) {
  const questions = (
    <div className={cn(list === "boxed" ? "grid gap-3" : "divide-y divide-border border-y border-border")}>
      {items.map((item) => (
        <details key={item.question} className={cn("group", list === "boxed" ? "rounded-token border border-border bg-surface px-5 py-4" : "py-5")}>
          <summary className="flex cursor-pointer list-none items-start justify-between gap-4 text-lg [&::-webkit-details-marker]:hidden">
            <span className="min-w-0 break-words">{item.question}</span>
            <Plus className="mt-1 size-5 shrink-0 transition-transform duration-300 group-open:rotate-45" aria-hidden="true" />
          </summary>
          <p className="mt-4 break-words text-muted-foreground">{item.answer}</p>
        </details>
      ))}
    </div>
  );

  const card = (
    <aside className="min-w-0 radius-xl bg-foreground p-8 text-background">
      <h3 className="text-title break-words" data-editable-id="faq-with-cta.ctaTitle">{ctaTitle}</h3>
      <p className="mt-3 break-words text-background/80" data-editable-id="faq-with-cta.ctaText">{ctaText}</p>
      <Link href={ctaLink.href} className="mt-6 inline-flex h-12 items-center rounded-token bg-background px-5 text-sm font-medium text-foreground">{ctaLink.label}</Link>
    </aside>
  );

  return (
    <section data-component-id="faq-with-cta" className="py-(--section-y)">
      <Container>
        <div className="max-w-2xl">
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="faq-with-cta.eyebrow">{eyebrow}</p>}
          <h2 className="text-headline break-words" data-editable-id="faq-with-cta.title">{title}</h2>
          {text && <p className="mt-5 text-lg break-words text-muted-foreground" data-editable-id="faq-with-cta.text">{text}</p>}
        </div>
        {cta === "side" ? (
          <div className="mt-12 grid items-start gap-8 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,0.7fr)]">
            {questions}
            {card}
          </div>
        ) : (
          <div className="mt-12 grid gap-8">
            {questions}
            {card}
          </div>
        )}
      </Container>
    </section>
  );
}
