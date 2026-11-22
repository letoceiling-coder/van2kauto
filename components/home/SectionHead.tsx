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
    <div className={cn("relative mb-6 flex flex-col gap-4 md:mb-14 md:flex-row md:items-end md:justify-between md:gap-6", className)}>
      {watermark && (
        <span aria-hidden="true" className="pointer-events-none absolute -top-[0.55em] -left-1 hidden font-display md:block text-[clamp(4rem,13vw,10rem)] leading-none font-bold tracking-tight text-outline select-none">
          {watermark}
        </span>
      )}
      <Reveal className="relative max-w-2xl">
        {eyebrow && <Eyebrow>{eyebrow}</Eyebrow>}
        <h2 className="text-headline mt-2 sm:mt-4">{title}</h2>
        {text && <p className="mt-2 max-w-xl text-[15px] leading-[1.45] text-muted-foreground sm:mt-4 sm:text-[17px]">{text}</p>}
      </Reveal>
      {aside && <Reveal delay={0.1} className="relative shrink-0">{aside}</Reveal>}
    </div>
  );
}
