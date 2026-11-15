"use client";

import { Heart } from "lucide-react";
import { motion } from "motion/react";
import { useFavorites } from "@/components/site/favorites";
import { cn } from "@/lib/utils";

export function FavButton({ slug, className, label }: { slug: string; className?: string; label?: boolean }) {
  const { has, toggle } = useFavorites();
  const on = has(slug);
  return (
    <button
      type="button"
      onClick={(e) => {
        e.preventDefault();
        e.stopPropagation();
        toggle(slug);
      }}
      aria-pressed={on}
      aria-label={on ? "Убрать из избранного" : "Добавить в избранное"}
      className={cn("inline-flex items-center justify-center gap-2 rounded-full transition active:scale-90", className)}
    >
      <motion.span key={String(on)} initial={{ scale: 0.6 }} animate={{ scale: 1 }} transition={{ type: "spring", stiffness: 500, damping: 15 }}>
        <Heart className={cn("size-[18px]", on && "fill-accent text-accent")} />
      </motion.span>
      {label && <span>{on ? "В избранном" : "В избранное"}</span>}
    </button>
  );
}
