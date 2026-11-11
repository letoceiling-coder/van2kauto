"use client";

import Image from "next/image";
import { useEffect, useRef, useState } from "react";
import { Container } from "@/components/layout/Container";
import { Reveal } from "@/components/motion/Reveal";
import type { MediaImage } from "@/components/ui/Media";
import { asset, cn } from "@/lib/utils";

export interface ScrollStoryStep {
  title: string;
  text: string;
  image: MediaImage;
}

export interface ScrollStoryProps {
  eyebrow?: string;
  title: string;
  steps: ScrollStoryStep[];
}

/** Scroll storytelling: a sticky picture that crossfades while the steps scroll past (desktop); stacked cards on mobile. */
export function ScrollStory({ eyebrow, title, steps }: ScrollStoryProps) {
  const [active, setActive] = useState(0);
  const refs = useRef<(HTMLDivElement | null)[]>([]);

  useEffect(() => {
    const io = new IntersectionObserver(
      (entries) => {
        for (const e of entries) if (e.isIntersecting) setActive(Number((e.target as HTMLElement).dataset.index));
      },
      { rootMargin: "-45% 0px -45% 0px" },
    );
    refs.current.forEach((el) => el && io.observe(el));
    return () => io.disconnect();
  }, [steps.length]);

  return (
    <section data-component-id="scroll-story" className="py-(--section-y)">
      <Container>
        <Reveal className="mb-14 max-w-3xl lg:mb-8">
          {eyebrow && (
            <p className="eyebrow mb-5 text-accent" data-editable-id="story.eyebrow">
              <span className="h-px w-8 bg-accent" />
              {eyebrow}
            </p>
          )}
          <h2 className="text-headline" data-editable-id="story.title">{title}</h2>
        </Reveal>

        <div className="lg:grid lg:grid-cols-2 lg:gap-20">
          <div className="hidden lg:block">
            <div className="sticky top-28 h-[calc(100svh-9rem)] max-h-[44rem]">
              <div className="radius-xl relative size-full overflow-hidden bg-muted shadow-premium">
                {steps.map((s, i) => (
                  <Image
                    key={s.image.src + i}
                    src={asset(s.image.src)}
                    alt={s.image.alt}
                    fill
                    sizes="50vw"
                    className={cn("object-cover transition-[opacity,transform] duration-700 ease-out", i === active ? "scale-100 opacity-100" : "scale-105 opacity-0")}
                    style={{ objectPosition: s.image.focal ?? "50% 50%" }}
                  />
                ))}
                <div className="glass absolute bottom-5 left-5 flex items-center gap-2 rounded-full px-3 py-1.5 text-xs tabular-nums">
                  {steps.map((_, i) => (
                    <span key={i} className={cn("h-1.5 rounded-full transition-all duration-500", i === active ? "w-6 bg-accent" : "w-1.5 bg-foreground/25")} />
                  ))}
                </div>
              </div>
            </div>
          </div>

          <div className="flex flex-col gap-6 lg:gap-0">
            {steps.map((s, i) => (
              <div
                key={s.title}
                ref={(el) => {
                  refs.current[i] = el;
                }}
                data-index={i}
                className="lg:flex lg:min-h-[60svh] lg:items-center"
              >
                <article className={cn("radius-xl border border-border bg-surface p-5 transition-opacity duration-500 sm:p-7 lg:border-0 lg:bg-transparent lg:p-0", i === active ? "lg:opacity-100" : "lg:opacity-35")}>
                  <div className="radius-xl relative mb-6 overflow-hidden bg-muted lg:hidden" style={{ aspectRatio: "4 / 3" }}>
                    <Image src={asset(s.image.src)} alt={s.image.alt} fill sizes="100vw" className="object-cover" style={{ objectPosition: s.image.focal ?? "50% 50%" }} />
                  </div>
                  <span className="font-heading text-sm text-accent tabular-nums">{String(i + 1).padStart(2, "0")} / {String(steps.length).padStart(2, "0")}</span>
                  <h3 className="mt-3 font-heading text-3xl leading-tight sm:text-4xl">{s.title}</h3>
                  <p className="mt-4 max-w-md text-lg text-muted-foreground">{s.text}</p>
                </article>
              </div>
            ))}
          </div>
        </div>
      </Container>
    </section>
  );
}
