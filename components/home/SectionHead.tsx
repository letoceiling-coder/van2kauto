import { Reveal } from "@/components/motion/Reveal";
import { cn } from "@/lib/utils";

export function Eyebrow({ children, className }: { children: React.ReactNode; className?: string }) {
  return (
    <p className={cn("inline-flex items-center gap-2.5 text-[11px] font-semibold tracking-[0.22em] text-accent uppercase", className)}>
      <span className="h-px w-6 bg-gradient-to-r from-transparent to-accent" />
      {children}
    </p>
  );
}

export function SectionHead({ eyebrow, title, text, aside, className, watermark }: { eyebrow?: string; title: React.ReactNode; text?: string; aside?: React.ReactNode; className?: string; watermark?: string }) {
  return (
    <div className={cn("relative mb-10 flex flex-col gap-6 md:mb-14 md:flex-row md:items-end md:justify-between", className)}>
      {watermark && (
        <span aria-hidden="true" className="pointer-events-none absolute -top-[0.55em] -left-1 font-display text-[clamp(4rem,13vw,10rem)] leading-none font-bold tracking-tight text-outline select-none">
          {watermark}
        </span>
      )}
      <Reveal className="relative max-w-2xl">
        {eyebrow && <Eyebrow>{eyebrow}</Eyebrow>}
        <h2 className="text-headline text-metal mt-4 pb-[0.06em]">{title}</h2>
        {text && <p className="mt-4 max-w-xl text-[17px] leading-relaxed text-muted-foreground">{text}</p>}
      </Reveal>
      {aside && <Reveal delay={0.1} className="relative shrink-0">{aside}</Reveal>}
    </div>
  );
}
