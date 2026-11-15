"use client";

import { Share2 } from "lucide-react";
import { FavButton } from "./FavButton";
import { LeadButton } from "@/components/site/Lead";

/** Нижняя панель карточки авто на телефоне: заменяет навигацию, держит цену и CTA под большим пальцем. */
export function LotBar({ slug, price, context, title }: { slug: string; price: string; context: string; title: string }) {
  async function share() {
    const data = { title, url: window.location.href };
    try {
      if (navigator.share) await navigator.share(data);
      else await navigator.clipboard.writeText(data.url);
    } catch {}
  }
  return (
    <div className="pb-safe fixed inset-x-0 bottom-0 z-40 border-t border-white/[0.08] bg-[#15181b]/[0.98] px-3 pt-2.5 sm:hidden">
      <div className="flex items-center gap-2">
        <FavButton slug={slug} className="size-12 shrink-0 border border-white/10" />
        <button onClick={share} aria-label="Поделиться" className="grid size-12 shrink-0 place-items-center rounded-full border border-white/10 active:scale-90"><Share2 className="size-[18px]" /></button>
        <LeadButton context={context} title={`Уточнить по ${title}`} className="btn-accent flex h-12 min-w-0 flex-1 flex-col items-center justify-center rounded-full leading-tight font-semibold active:scale-[0.98]">
          <span className="text-[15px]">Уточнить по авто</span>
          <span className="tnum text-[11px] font-medium opacity-70">{price} · до Анапы</span>
        </LeadButton>
      </div>
    </div>
  );
}
