import Link from "next/link";
import { Container } from "@/components/layout/Container";
import { Reveal } from "@/components/motion/Reveal";

export interface HeroBasicProps {
  eyebrow?: string;
  title: string;
  text?: string;
  primaryCta?: { label: string; href: string };
}

export function HeroBasic({ eyebrow, title, text, primaryCta }: HeroBasicProps) {
  return (
    <section data-component-id="hero-basic" className="py-(--section-y)">
      <Container>
        <Reveal className="max-w-3xl">
          {eyebrow && (
            <p className="mb-5 text-sm font-medium uppercase tracking-[0.18em] text-accent" data-editable-id="home.hero.eyebrow">
              {eyebrow}
            </p>
          )}
          <h1 className="text-4xl leading-[1.05] sm:text-5xl lg:text-7xl" data-editable-id="home.hero.title">
            {title}
          </h1>
          {text && (
            <p className="mt-6 max-w-2xl text-lg text-muted-foreground sm:text-xl" data-editable-id="home.hero.text">
              {text}
            </p>
          )}
          {primaryCta && (
            <Link
              href={primaryCta.href}
              className="mt-10 inline-flex h-12 items-center rounded-token bg-primary px-6 font-medium text-primary-foreground transition-opacity hover:opacity-90"
            >
              {primaryCta.label}
            </Link>
          )}
        </Reveal>
      </Container>
    </section>
  );
}
