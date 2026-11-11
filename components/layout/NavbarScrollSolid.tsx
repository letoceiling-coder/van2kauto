"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { Menu, X } from "lucide-react";
import { cn } from "@/lib/utils";

export interface NavbarScrollSolidLink {
  label: string;
  href: string;
}

export interface NavbarScrollSolidProps {
  brand: string;
  links: NavbarScrollSolidLink[];
  cta?: NavbarScrollSolidLink;
  menuLabel?: string;
  closeLabel?: string;
  dock?: "edge" | "inset";
  height?: "slim" | "tall";
}

/** Fixed bar, transparent until scroll, then solid glass. */
export function NavbarScrollSolid({
  brand,
  links,
  cta,
  menuLabel = "Меню",
  closeLabel = "Закрыть",
  dock = "edge",
  height = "slim",
}: NavbarScrollSolidProps) {
  const [scrolled, setScrolled] = useState(false);
  const [open, setOpen] = useState(false);
  const solid = scrolled || open;
  const tall = height === "tall";

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    if (!open) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") setOpen(false);
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [open]);

  return (
    <header
      data-component-id="navbar-scroll-solid"
      className={cn("fixed z-50 text-foreground", dock === "inset" ? "inset-x-0 top-0 px-3 pt-3 sm:px-4" : "inset-x-0 top-0")}
    >
      <div
        className={cn(
          "mx-auto flex w-full items-center gap-3",
          tall ? "h-20" : "h-12",
          dock === "inset" ? "max-w-site rounded-full px-4 sm:px-5" : "px-4 sm:px-6 lg:px-8",
          solid && "glass",
          solid && dock === "inset" && "shadow-premium",
          solid && dock === "edge" && "border-b border-border",
        )}
      >
        <Link href="/" className={cn("min-w-0 truncate font-heading", tall ? "text-xl" : "text-base")} data-editable-id="nav.brand" onClick={() => setOpen(false)}>
          {brand}
        </Link>
        <nav className={cn("ml-auto hidden items-center lg:flex", tall ? "gap-8" : "gap-5")} aria-label="Основная навигация">
          {links.map((link) => (
            <Link key={link.href + link.label} href={link.href} className={cn("opacity-80 hover:opacity-100", tall ? "text-base" : "text-sm")}>
              {link.label}
            </Link>
          ))}
        </nav>
        {cta && (
          <Link href={cta.href} className="ml-4 hidden rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground lg:inline-flex">
            {cta.label}
          </Link>
        )}
        <button
          type="button"
          className="ml-auto grid size-11 shrink-0 place-items-center rounded-full lg:hidden"
          aria-label={open ? closeLabel : menuLabel}
          aria-expanded={open}
          onClick={() => setOpen((value) => !value)}
        >
          {open ? <X className="size-5" /> : <Menu className="size-5" />}
        </button>
      </div>
      {open && (
        <div
          className={cn(
            "mx-auto mt-2 max-h-[70vh] w-full overflow-x-hidden overflow-y-auto bg-background p-4 lg:hidden",
            dock === "inset" ? "max-w-site rounded-token shadow-premium" : "border-t border-border",
          )}
        >
          <nav className="flex flex-col" aria-label="Основная навигация">
            {links.map((link) => (
              <Link key={link.href + link.label} href={link.href} className="border-b border-border py-3 font-heading text-2xl break-words" onClick={() => setOpen(false)}>
                {link.label}
              </Link>
            ))}
          </nav>
          {cta && (
            <Link href={cta.href} className="mt-4 inline-flex rounded-full bg-primary px-5 py-3 text-sm font-medium text-primary-foreground" onClick={() => setOpen(false)}>
              {cta.label}
            </Link>
          )}
        </div>
      )}
    </header>
  );
}
