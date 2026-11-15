"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { Heart, LayoutGrid, Sparkles, Home } from "lucide-react";
import { useFavorites } from "./favorites";
import { cn } from "@/lib/utils";

const items = [
  { href: "/", label: "Главная", icon: Home },
  { href: "/catalog", label: "Каталог", icon: LayoutGrid },
  { href: "/favorites", label: "Избранное", icon: Heart },
  { href: "/selection", label: "Подбор", icon: Sparkles },
];

/** Нижняя навигация на телефоне (ТЗ §3: «Каталог · Избранное · Подбор»). */
export function MobileDock() {
  const pathname = usePathname();
  const { list } = useFavorites();
  return (
    <nav aria-label="Быстрая навигация" className="pb-safe fixed inset-x-0 bottom-0 z-40 px-3 md:hidden">
      <div className="mx-auto flex max-w-md items-center justify-between rounded-[1.6rem] border border-white/10 bg-[#121417]/85 p-1.5 shadow-[0_20px_60px_-10px_rgb(0_0_0/0.7)] backdrop-blur-xl">
        {items.map(({ href, label, icon: Icon }) => {
          const active = href === "/" ? pathname === "/" : pathname.startsWith(href);
          return (
            <Link key={href} href={href} className={cn("relative flex h-13 flex-1 flex-col items-center justify-center gap-0.5 rounded-[1.2rem] text-[11px] font-medium transition", active ? "bg-white/[0.08] text-foreground" : "text-foreground/55")}>
              <Icon className={cn("size-5", active && "text-accent")} />
              {label}
              {href === "/favorites" && list.length > 0 && <span className="absolute top-1.5 right-[calc(50%-18px)] size-2 rounded-full bg-accent" />}
            </Link>
          );
        })}
      </div>
    </nav>
  );
}
