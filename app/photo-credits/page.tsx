import type { Metadata } from "next";
import credits from "@/content/photo-credits.json";

export const metadata: Metadata = { title: "Авторы фотографий", robots: { index: false } };

export default function Page() {
  return (
    <section className="mx-auto max-w-site px-4 pt-28 sm:px-6 sm:pt-36 lg:px-8">
      <h1 className="text-headline">Авторы фотографий</h1>
      <p className="mt-3 max-w-2xl text-muted-foreground">Иллюстрации моделей в каталоге — фотографии из Wikimedia Commons под свободными лицензиями. Фото конкретных лотов менеджер присылает отдельно.</p>
      <ul className="mt-8 divide-y divide-white/[0.06] text-sm">
        {(credits as { file: string; author: string; license: string; sourceUrl: string; title: string }[]).map((c) => (
          <li key={c.file} className="flex flex-col gap-1 py-3 sm:flex-row sm:justify-between">
            <a href={c.sourceUrl} target="_blank" rel="noopener noreferrer" className="text-foreground/85 hover:text-accent">{c.title.replace(/^File:/, "")}</a>
            <span className="text-muted-foreground">{c.author} · {c.license}</span>
          </li>
        ))}
      </ul>
    </section>
  );
}
