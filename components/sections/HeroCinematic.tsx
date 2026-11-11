import Image from "next/image";
import Link from "next/link";
import { ArrowDown, ArrowUpRight } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { Reveal } from "@/components/motion/Reveal";
import { TextReveal } from "@/components/motion/TextReveal";
import type { MediaImage } from "@/components/ui/Media";
import { asset } from "@/lib/utils";

export interface HeroCinematicProps {
  eyebrow?: string;
  title: string;
  text?: string;
  primaryCta?: { label: string; href: string };
  secondaryCta?: { label: string; href: string };
  image: MediaImage;
  /** Optional background video (mp4/webm); the image is its poster and the fallback. */
  videoSrc?: string;
  stats?: { value: string; label: string }[];
  note?: string;
}

/** Full-screen cinematic hero: photo or video with a slow Ken Burns zoom, display headline, glass card with key facts. */
export function HeroCinematic({ eyebrow, title, text, primaryCta, secondaryCta, image, videoSrc, stats, note }: HeroCinematicProps) {
  return (
    <section data-component-id="hero-cinematic" data-overlay-hero className="grain relative isolate flex min-h-[100svh] items-end overflow-hidden bg-black text-white">
      <div className="absolute inset-0 -z-10">
        <div className="animate-kenburns absolute inset-0">
          {videoSrc ? (
            <video className="size-full object-cover" src={asset(videoSrc)} poster={asset(image.src)} autoPlay muted loop playsInline aria-hidden="true" />
          ) : (
            <Image src={asset(image.src)} alt={image.alt} fill priority sizes="100vw" className="object-cover" style={{ objectPosition: image.focal ?? "50% 50%" }} />
          )}
        </div>
        <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/35 to-black/20" />
        <div className="absolute inset-0 bg-gradient-to-r from-black/55 via-transparent to-transparent" />
      </div>

      <Container className="relative grid w-full gap-10 pt-36 pb-14 sm:pb-20 lg:grid-cols-12 lg:items-end lg:gap-12">
        <div className="lg:col-span-8">
          {eyebrow && (
            <Reveal>
              <p className="eyebrow mb-6 text-white/80" data-editable-id="hero.eyebrow">
                <span className="h-px w-8 bg-accent" />
                {eyebrow}
              </p>
            </Reveal>
          )}
          <TextReveal text={title} className="text-display max-w-[14ch]" />
          {text && (
            <Reveal delay={0.25}>
              <p className="mt-7 max-w-xl text-lg text-white/75 sm:text-xl" data-editable-id="hero.text">{text}</p>
            </Reveal>
          )}
          {(primaryCta || secondaryCta) && (
            <Reveal delay={0.35}>
              <div className="mt-10 flex flex-wrap gap-3">
                {primaryCta && (
                  <Link href={primaryCta.href} className="group inline-flex h-14 items-center gap-3 rounded-full bg-white pr-2 pl-8 font-medium text-black transition-transform hover:-translate-y-0.5">
                    {primaryCta.label}
                    <span className="grid size-10 place-items-center rounded-full bg-black text-white transition-transform duration-300 group-hover:rotate-45">
                      <ArrowUpRight className="size-4" />
                    </span>
                  </Link>
                )}
                {secondaryCta && (
                  <Link href={secondaryCta.href} className="glass-dark inline-flex h-14 items-center rounded-full px-8 font-medium text-white transition-colors hover:bg-white/15">
                    {secondaryCta.label}
                  </Link>
                )}
              </div>
            </Reveal>
          )}
        </div>

        {stats && stats.length > 0 && (
          <Reveal delay={0.45} className="lg:col-span-4">
            <div className="glass-dark radius-xl p-6 sm:p-7">
              <dl className="grid grid-cols-2 gap-x-6 gap-y-6">
                {stats.slice(0, 4).map((s) => (
                  <div key={s.label}>
                    <dt className="text-xs text-white/60">{s.label}</dt>
                    <dd className="mt-1 font-heading text-3xl tracking-tight sm:text-4xl">{s.value}</dd>
                  </div>
                ))}
              </dl>
              {note && <p className="mt-6 border-t border-white/15 pt-5 text-sm text-white/70">{note}</p>}
            </div>
          </Reveal>
        )}
      </Container>

      <div className="pointer-events-none absolute bottom-6 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-[0.65rem] tracking-[0.3em] text-white/60 uppercase md:flex">
        <span>Листайте</span>
        <ArrowDown className="animate-float size-4" />
      </div>
    </section>
  );
}
