import Link from "next/link";
import { Container } from "@/components/layout/Container";
import { cn } from "@/lib/utils";

export interface CtaSplitNoteProps {
  eyebrow?: string;
  title: string;
  text?: string;
  noteTitle: string;
  noteText: string;
  cta: { label: string; href: string };
  note?: "overlap" | "aside";
  weight?: "display" | "headline";
}

/** A large sentence and a note card that either overlaps it or sits beside it. */
export function CtaSplitNote({
  eyebrow,
  title,
  text,
  noteTitle,
  noteText,
  cta,
  note = "overlap",
  weight = "display",
}: CtaSplitNoteProps) {
  const sentence = (
    <div className="min-w-0">
      {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="cta-split-note.eyebrow">{eyebrow}</p>}
      <h2 className={cn("break-words font-heading", weight === "display" ? "text-display" : "text-headline")} data-editable-id="cta-split-note.title">{title}</h2>
      {text && <p className="mt-6 max-w-xl text-lg break-words text-muted-foreground" data-editable-id="cta-split-note.text">{text}</p>}
    </div>
  );
  const card = (
    <aside className={cn("min-w-0 max-w-md rounded-token border border-border bg-surface p-7 shadow-premium", note === "overlap" && "relative z-10 md:-mt-16 md:ml-auto")}>
      <h3 className="text-title break-words">{noteTitle}</h3>
      <p className="mt-3 break-words text-muted-foreground">{noteText}</p>
      <Link href={cta.href} className="mt-6 inline-flex h-12 items-center justify-center rounded-token bg-primary px-5 text-sm font-medium text-primary-foreground">{cta.label}</Link>
    </aside>
  );

  if (note === "aside") {
    return (
      <section data-component-id="cta-split-note" className="py-(--section-y)">
        <Container className="grid items-center gap-10 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)]">
          {sentence}
          {card}
        </Container>
      </section>
    );
  }

  return (
    <section data-component-id="cta-split-note" className="py-(--section-y)">
      <Container>
        {sentence}
        {card}
      </Container>
    </section>
  );
}
