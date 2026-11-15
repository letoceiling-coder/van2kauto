import { clsx, type ClassValue } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/** Prefix a public asset path with the project basePath (needed for <img>/<video> and next/image src). */
export function asset(path: string) {
  if (/^(https?:)?\/\//.test(path) || path.startsWith("data:")) return path;
  const base = process.env.NEXT_PUBLIC_BASE_PATH ?? "";
  return `${base}${path.startsWith("/") ? path : `/${path}`}`;
}

/** 800px-версия фото (рядом лежит <name>-sm.webp) — для карточек и превью. */
export function small(path: string) {
  return path.startsWith("/media/") && !path.endsWith("-sm.webp") ? path.replace(/\.webp$/, "-sm.webp") : path;
}
