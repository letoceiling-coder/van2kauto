import Image from "next/image";
import Link from "next/link";
import { Container } from "@/components/layout/Container";
import type { MediaImage } from "@/components/ui/Media";
import { asset, cn } from "@/lib/utils";

export interface CtaPhotoBandProps {
  eyebrow?: string;
  title: string;
  text?: string;
  cta: { label: string; href: string };
  image: MediaImage;
  height?: "tall" | "slim";
  align?: "left" | "center";
}

/** Full-bleed photo with a short invitation. Not a first screen, so it does not claim the hero overlay flag. */
export function CtaPhotoBand({ eyebrow, title, text, cta, image, height = "tall", align = "left" }: CtaPhotoBandProps) {
  return (
    <section
      data-component-id="cta-photo-band"
      className={cn(
        "relative isolate flex overflow-hidden py-(--section-y)",
        height === "tall" ? "min-h-[36rem] items-end" : "min-h-64 items-center",
      )}
    >
      <Image src={asset(image.src)} alt={image.alt} fill sizes="100vw" className="object-cover" style={{ objectPosition: image.focal ?? "50% 40%" }} />
      <div aria-hidden="true" className="absolute inset-0 bg-gradient-to-t from-black/70" />
      <Container className={cn("relative text-white", align === "center" && "flex flex-col items-center text-center")}>
        <div className="min-w-0 max-w-2xl">
          {eyebrow && <p className="eyebrow mb-4 text-white opacity-80" data-editable-id="cta-photo-band.eyebrow">{eyebrow}</p>}
          <h2 className="text-headline break-words" data-editable-id="cta-photo-band.title">{title}</h2>
          {text && <p className="mt-4 text-lg break-words text-white opacity-80" data-editable-id="cta-photo-band.text">{text}</p>}
          <Link href={cta.href} className="mt-8 inline-flex h-12 items-center justify-center rounded-token bg-background px-6 text-sm font-medium text-foreground">{cta.label}</Link>
        </div>
      </Container>
    </section>
  );
}
