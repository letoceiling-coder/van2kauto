import Link from "next/link";
import { asset, cn } from "@/lib/utils";

/**
 * Фирменный логотип ВАН23АВТО — надпись из оригинального логотипа (public/brand/wordmark.webp, 640×57).
 * Размеры заданы атрибутами, поэтому место под логотип зарезервировано до загрузки — без сдвига вёрстки.
 */
export function Logo({ className, tagline }: { className?: string; tagline?: boolean }) {
  return (
    <Link href="/" aria-label="ВАН23АВТО — на главную" className={cn("group inline-flex shrink-0 flex-col justify-center", className)}>
      {/* eslint-disable-next-line @next/next/no-img-element -- статичный логотип с фиксированными размерами */}
      <img
        src={asset("/brand/wordmark.webp")}
        alt="ВАН23АВТО"
        width={640}
        height={57}
        decoding="async"
        fetchPriority="high"
        className="h-[20px] w-auto transition-opacity duration-300 group-hover:opacity-85 sm:h-[24px]"
      />
      {tagline && <span className="mt-2 text-[11px] font-medium tracking-[0.28em] text-muted-foreground uppercase">Привоз авто из-за рубежа</span>}
    </Link>
  );
}
