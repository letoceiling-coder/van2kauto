"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { Heart, Menu, X, ArrowUpRight } from "lucide-react";
import { Logo } from "./Logo";
import { useLead } from "./Lead";
import { useFavorites } from "./favorites";
import { nav } from "@/content/nav";
import { cn } from "@/lib/utils";

export function Header() {
  const [scrolled, setScrolled] = useState(false);
  const pathname = usePathname();
  // Меню открыто только на той странице, где его открыли — переход по ссылке закрывает его без эффекта.
  const [openAt, setOpenAt] = useState<string | null>(null);
  const open = openAt === pathname;
  const setOpen = (fn: (v: boolean) => boolean) => setOpenAt(fn(open) ? pathname : null);
  const lead = useLead();
  const { list } = useFavorites();

  useEffect(() => {
    const on = () => setScrolled(window.scrollY > 24);
    on();
    window.addEventListener("scroll", on, { passive: true });
    return () => window.removeEventListener("scroll", on);
  }, []);
  useEffect(() => {
    document.documentElement.style.overflow = open ? "hidden" : "";
  }, [open]);

  return (
    <header className="fixed inset-x-0 top-0 z-50">
      <div className={cn("relative border-b transition-[background-color,border-color] duration-300", scrolled || open ? "border-white/[0.06] bg-background/92 md:bg-background/75 md:backdrop-blur-xl" : "border-transparent")}>
        <span aria-hidden="true" className={cn("pointer-events-none absolute inset-x-0 -bottom-px h-px bg-[linear-gradient(90deg,transparent,rgb(235_17_29/0.55),transparent)] transition-opacity duration-500", scrolled ? "opacity-100" : "opacity-0")} />
        <div className="mx-auto flex h-16 max-w-site items-center gap-6 px-4 sm:px-6 lg:h-[72px] lg:px-8">
          <Logo className="h-11" />
          <nav aria-label="Основная навигация" className="ml-auto hidden items-center gap-1 xl:flex">
            {nav.map((l) => {
              const active = pathname === l.href || pathname.startsWith(l.href + "/");
              return (
                <Link key={l.href} href={l.href} className={cn("relative isolate rounded-full px-3.5 py-2 text-sm transition-colors", active ? "text-foreground" : "text-foreground/65 hover:text-foreground")}>
                  {active && <motion.span layoutId="nav-pill" className="absolute inset-0 -z-10 rounded-full bg-white/[0.07]" transition={{ type: "spring", damping: 30, stiffness: 380 }} />}
                  {l.label}
                </Link>
              );
            })}
          </nav>
          <div className="ml-auto flex items-center gap-2 xl:ml-2">
            <Link href="/favorites" aria-label={`Избранное: ${list.length}`} className="relative hidden size-11 place-items-center rounded-full border border-white/10 transition hover:border-white/25 sm:grid">
              <Heart className="size-[18px]" />
              {list.length > 0 && <span className="absolute -top-0.5 -right-0.5 grid size-5 place-items-center rounded-full bg-accent text-[11px] font-bold text-accent-foreground">{list.length}</span>}
            </Link>
            <button onClick={() => lead({ title: "Подобрать автомобиль" })} className="btn-accent shine hidden h-11 items-center gap-1.5 rounded-full pr-4 pl-5 text-sm font-semibold transition active:scale-[0.97] sm:inline-flex">
              Подобрать авто <ArrowUpRight className="size-4" />
            </button>
            <button onClick={() => setOpen((v) => !v)} aria-expanded={open} aria-label={open ? "Закрыть меню" : "Открыть меню"} className="grid size-11 place-items-center rounded-full border border-white/10 xl:hidden">
              {open ? <X className="size-5" /> : <Menu className="size-5" />}
            </button>
          </div>
        </div>
      </div>
      <AnimatePresence>
        {open && (
          <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="fixed inset-x-0 top-16 bottom-0 overflow-y-auto bg-background xl:hidden">
            <nav aria-label="Меню" className="flex flex-col px-4 pt-4 pb-32 sm:px-6">
              {[{ label: "Главная", href: "/" }, ...nav, { label: "Подбор по бюджету", href: "/selection" }, { label: "Избранное", href: "/favorites" }].map((l, i) => (
                <motion.div key={l.href} initial={{ opacity: 0, y: 14 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.03 * i, duration: 0.35 }}>
                  <Link href={l.href} className="flex items-center justify-between border-b border-white/[0.06] py-4 text-2xl font-semibold tracking-tight">
                    {l.label}
                    <ArrowUpRight className="size-5 text-muted-foreground" />
                  </Link>
                </motion.div>
              ))}
              <button onClick={() => lead({ title: "Подобрать автомобиль" })} className="btn-accent mt-8 h-14 rounded-full font-semibold">
                Оставить заявку
              </button>
            </nav>
          </motion.div>
        )}
      </AnimatePresence>
    </header>
  );
}
