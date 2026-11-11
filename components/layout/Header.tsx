"use client";

import Link from "next/link";
import { useState } from "react";
import { Menu, X } from "lucide-react";
import { Container } from "@/components/layout/Container";
import { navigation, site } from "@/content/site-data";

export function Header() {
  const [open, setOpen] = useState(false);
  return (
    <header data-component-id="layout.header" className="sticky top-0 z-40 border-b border-border/60 bg-background/80 backdrop-blur">
      <Container className="flex h-16 items-center justify-between gap-4">
        <Link href="/" className="font-heading text-lg font-semibold tracking-tight" data-editable-id="site.name">
          {site.name}
        </Link>
        <nav className="hidden items-center gap-8 text-sm md:flex" aria-label="Основная навигация">
          {navigation.map((item) => (
            <Link key={item.href} href={item.href} className="text-muted-foreground transition-colors hover:text-foreground">
              {item.label}
            </Link>
          ))}
        </nav>
        <button
          type="button"
          className="inline-flex size-10 items-center justify-center rounded-token md:hidden"
          aria-label={open ? "Закрыть меню" : "Открыть меню"}
          aria-expanded={open}
          onClick={() => setOpen((v) => !v)}
        >
          {open ? <X className="size-5" /> : <Menu className="size-5" />}
        </button>
      </Container>
      {open && (
        <nav className="border-t border-border md:hidden" aria-label="Мобильная навигация">
          <Container className="flex flex-col py-2">
            {navigation.map((item) => (
              <Link key={item.href} href={item.href} className="py-3 text-base" onClick={() => setOpen(false)}>
                {item.label}
              </Link>
            ))}
          </Container>
        </nav>
      )}
    </header>
  );
}
