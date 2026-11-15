import Link from "next/link";
import { cn } from "@/lib/utils";

export function Logo({ className }: { className?: string }) {
  return (
    <Link href="/" aria-label="VAN2KAUTO — на главную" className={cn("group inline-flex items-center gap-2.5", className)}>
      <span className="relative grid size-9 place-items-center overflow-hidden rounded-xl bg-accent text-accent-foreground">
        <svg viewBox="0 0 24 24" className="size-5" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <path d="M3 15.5 5.2 10a2 2 0 0 1 1.9-1.3h9.8A2 2 0 0 1 18.8 10l2.2 5.5" />
          <path d="M3 15.5h18v2.2a1 1 0 0 1-1 1h-1.6M3 15.5v2.2a1 1 0 0 0 1 1h1.6M8.5 18.7h7" />
          <circle cx="7" cy="18.7" r="1.4" />
          <circle cx="17" cy="18.7" r="1.4" />
        </svg>
        <span className="absolute inset-0 -translate-x-full bg-white/40 transition-transform duration-500 group-hover:translate-x-full" />
      </span>
      <span className="font-display text-[1.05rem] font-semibold tracking-[0.06em]">
        VAN<span className="text-accent">2</span>KAUTO
      </span>
    </Link>
  );
}
