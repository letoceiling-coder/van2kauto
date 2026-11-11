import Image from "next/image";
import Link from "next/link";
import { Container } from "@/components/layout/Container";
import type { MediaImage } from "@/components/ui/Media";
import { asset, cn } from "@/lib/utils";

export interface HorizontalProject {
  title: string;
  meta: string;
  href: string;
  image: MediaImage;
}

export interface PortfolioHorizontalProps {
  eyebrow?: string;
  title: string;
  text?: string;
  projects: HorizontalProject[];
  item?: "wide" | "square";
  caption?: "overlay" | "below";
}

/** Horizontal case covers for studios. The strip scrolls inside the section. */
export function PortfolioHorizontal({ eyebrow, title, text, projects, item = "wide", caption = "overlay" }: PortfolioHorizontalProps) {
  return (
    <section data-component-id="portfolio-horizontal" className="py-(--section-y)">
      <Container>
        <header className="mb-10 max-w-3xl">
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="portfolio-horizontal.eyebrow">{eyebrow}</p>}
          <h2 className="text-headline font-heading break-words" data-editable-id="portfolio-horizontal.title">{title}</h2>
          {text && <p className="mt-5 text-lg text-muted-foreground" data-editable-id="portfolio-horizontal.text">{text}</p>}
        </header>
      </Container>
      <div className="min-w-0 overflow-x-auto snap-x snap-mandatory px-4 sm:px-6" aria-roledescription="carousel" aria-label={title}>
        <ul className="flex w-max gap-4 pb-2">
          {projects.map((project) => (
            <li key={project.title} className={cn("shrink-0 snap-start", item === "square" ? "w-[72vw] max-w-sm" : "w-[86vw] max-w-3xl")}>
              <Link href={project.href} className="group block min-w-0">
                <span className={cn("radius-xl relative block overflow-hidden bg-muted", item === "square" ? "aspect-square" : "aspect-[16/10]")}>
                  <Image src={asset(project.image.src)} alt={project.image.alt} fill sizes="(min-width: 1024px) 48rem, 86vw" className="object-cover transition-transform duration-700 group-hover:scale-[1.03] motion-reduce:transform-none" style={{ objectPosition: project.image.focal ?? "50% 50%" }} />
                  {caption === "overlay" && (
                    <span className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 p-5">
                      <span className="block font-heading text-title break-words text-white">{project.title}</span>
                      <span className="mt-1 block text-sm text-white/80">{project.meta}</span>
                    </span>
                  )}
                </span>
                {caption === "below" && (
                  <span className="mt-4 block">
                    <span className="block font-heading text-title break-words">{project.title}</span>
                    <span className="mt-1 block text-sm text-muted-foreground">{project.meta}</span>
                  </span>
                )}
              </Link>
            </li>
          ))}
        </ul>
      </div>
    </section>
  );
}
