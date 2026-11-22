import Image from "next/image";
import { TextReveal } from "@/components/motion/TextReveal";
import { Reveal } from "@/components/motion/Reveal";
import { asset } from "@/lib/utils";

/** Шапка внутренних страниц: фото с затемнением, крупный водяной знак, без пустоты. */
export function PageHero({ eyebrow, title, text, image, watermark, children }: { eyebrow: string; title: string; text?: string; image?: string; watermark?: string; children?: React.ReactNode }) {
  return (
    <section className="relative isolate overflow-hidden pt-24 pb-6 sm:pt-36 sm:pb-16">
      {image && (
        <>
          <Image src={asset(image)} alt="" fill priority sizes="100vw" className="animate-kenburns -z-20 object-cover opacity-70" />
          <div className="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgb(9_9_10/0.55),rgb(9_9_10/0.6)_50%,#09090a)]" />
          <div className="absolute inset-0 -z-10 bg-[radial-gradient(70%_80%_at_0%_100%,#09090a,transparent)]" />
        </>
      )}
      {watermark && (
        <span aria-hidden="true" className="pointer-events-none absolute right-0 bottom-0 -z-10 hidden translate-y-[18%] font-display md:block text-[clamp(6rem,22vw,18rem)] leading-none font-bold tracking-tight text-outline select-none">
          {watermark}
        </span>
      )}
      <div className="mx-auto max-w-site px-4 sm:px-6 lg:px-8">
        <Reveal>
          <p className="inline-flex items-center gap-2.5 text-[11px] font-semibold tracking-[0.22em] text-accent uppercase"><span className="h-px w-6 bg-accent" />{eyebrow}</p>
        </Reveal>
        <TextReveal text={title} className="text-display mt-3 max-w-[18ch] font-semibold sm:mt-5" />
        {text && (
          <Reveal delay={0.2}>
            <p className="mt-3 max-w-2xl text-[15px] leading-[1.45] text-foreground/70 sm:mt-5 sm:text-lg">{text}</p>
          </Reveal>
        )}
        {children && <Reveal delay={0.3} className="mt-5 sm:mt-8">{children}</Reveal>}
      </div>
    </section>
  );
}
