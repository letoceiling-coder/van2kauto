import Link from "next/link";
import { ArrowUpRight } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { Reveal } from "@/components/motion/Reveal";

export interface CtaAuroraProps {
  eyebrow?: string;
  title: string;
  text?: string;
  primaryCta: { label: string; href: string };
  secondaryCta?: { label: string; href: string };
  note?: string;
}

/** Inverted call-to-action panel with slowly drifting aurora light, grain and a large headline. */
export function CtaAurora({ eyebrow, title, text, primaryCta, secondaryCta, note }: CtaAuroraProps) {
  return (
    <section data-component-id="cta-aurora" className="py-(--section-y)">
      <Container>
        <Reveal>
          <div className="grain radius-xl relative isolate overflow-hidden bg-foreground px-6 py-20 text-center text-background sm:px-12 sm:py-28 lg:py-32">
            <div aria-hidden="true" className="absolute inset-0 -z-10">
              <div className="animate-aurora absolute -top-1/3 -left-1/4 size-[70%] rounded-full bg-accent/45 blur-[110px]" />
              <div className="animate-aurora absolute -right-1/4 -bottom-1/3 size-[70%] rounded-full bg-primary/60 blur-[120px] [animation-delay:-9s]" />
              <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_35%,color-mix(in_oklch,var(--foreground)_70%,transparent))]" />
            </div>
            {eyebrow && <p className="eyebrow mb-6 text-background/70" data-editable-id="cta.eyebrow">{eyebrow}</p>}
            <h2 className="text-display mx-auto max-w-[15ch]" data-editable-id="cta.title">{title}</h2>
            {text && <p className="mx-auto mt-7 max-w-xl text-lg text-background/70" data-editable-id="cta.text">{text}</p>}
            <div className="mt-11 flex flex-wrap justify-center gap-3">
              <Link href={primaryCta.href} className="group inline-flex h-14 items-center gap-3 rounded-full bg-background pr-2 pl-8 font-medium text-foreground transition-transform hover:-translate-y-0.5">
                {primaryCta.label}
                <span className="grid size-10 place-items-center rounded-full bg-accent text-accent-foreground transition-transform duration-300 group-hover:rotate-45">
                  <ArrowUpRight className="size-4" />
                </span>
              </Link>
              {secondaryCta && (
                <Link href={secondaryCta.href} className="inline-flex h-14 items-center rounded-full border border-background/25 px-8 font-medium transition-colors hover:bg-background/10">
                  {secondaryCta.label}
                </Link>
              )}
            </div>
            {note && <p className="mt-8 text-sm text-background/55">{note}</p>}
          </div>
        </Reveal>
      </Container>
    </section>
  );
}
