import type { Metadata } from "next";
import Link from "next/link";
import { WifiOff } from "lucide-react";

export const metadata: Metadata = { title: "Нет соединения", robots: { index: false } };

export default function Offline() {
  return (
    <section className="mx-auto flex min-h-[70svh] max-w-md flex-col items-center justify-center px-4 text-center">
      <span className="grid size-14 place-items-center rounded-full bg-white/[0.06]"><WifiOff className="size-6 text-muted-foreground" /></span>
      <h1 className="text-headline mt-5">Нет соединения</h1>
      <p className="mt-2 text-muted-foreground">Проверьте интернет. Открытые раньше страницы доступны и без сети.</p>
      <Link href="/" className="btn-accent mt-6 inline-flex h-12 items-center rounded-full px-6 font-semibold">На главную</Link>
    </section>
  );
}
