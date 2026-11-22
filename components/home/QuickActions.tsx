import Link from "next/link";
import { LayoutGrid, MessageCircle, Sparkles, Trophy } from "lucide-react";

const ACTIONS = [
  { href: "/catalog", label: "Каталог", icon: LayoutGrid },
  { href: "#top", label: "Топ-10", icon: Trophy },
  { href: "/selection", label: "Подбор", icon: Sparkles },
  { href: "#ask", label: "Спросить", icon: MessageCircle },
];

/** Быстрые действия под первым экраном — паттерн «главная-пульт» (только телефон). */
export function QuickActions() {
  return (
    <nav aria-label="Быстрые действия" data-component-id="quick" className="mx-auto grid max-w-site grid-cols-4 gap-2 px-4 pt-4 md:hidden">
      {ACTIONS.map(({ href, label, icon: Icon }) => (
        <Link key={href} href={href} className="press flex flex-col items-center gap-2 rounded-2xl py-2">
          <span className="grid size-12 place-items-center rounded-2xl bg-white/[0.06] text-foreground"><Icon className="size-[22px]" strokeWidth={1.9} /></span>
          <span className="text-[12px] font-medium text-foreground/80">{label}</span>
        </Link>
      ))}
    </nav>
  );
}
