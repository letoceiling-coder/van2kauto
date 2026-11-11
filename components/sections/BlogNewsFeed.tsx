import Link from "next/link";
import { Container } from "@/components/layout/Container";
import { cn } from "@/lib/utils";

export interface NewsItem {
  date: string;
  title: string;
  text: string;
  href: string;
}

export interface BlogNewsFeedProps {
  eyebrow?: string;
  title: string;
  items: NewsItem[];
  date?: "column" | "inline";
  lead?: "feature" | "same";
}

function Row({ item, date, feature }: { item: NewsItem; date: "column" | "inline"; feature: boolean }) {
  const heading = <span className={cn("block font-heading break-words", feature ? "text-headline" : "text-lg")}>{item.title}</span>;
  const body = <span className="mt-2 block text-muted-foreground break-words">{item.text}</span>;
  return (
    <Link href={item.href} className={cn("block min-w-0 border-b border-border", feature ? "py-10" : "py-5")}>
      {date === "column" ? (
        <span className="grid gap-2 sm:grid-cols-[9rem_minmax(0,1fr)] sm:gap-6">
          <span className="text-sm text-muted-foreground">{item.date}</span>
          <span className="min-w-0">
            {heading}
            {body}
          </span>
        </span>
      ) : (
        <span className="block min-w-0">
          <span className="text-sm text-muted-foreground">{item.date}</span>
          <span className="mt-2 block">{heading}</span>
          {body}
        </span>
      )}
    </Link>
  );
}

/** Dated news. The newest item is larger when lead is feature. */
export function BlogNewsFeed({ eyebrow, title, items, date = "column", lead = "feature" }: BlogNewsFeedProps) {
  return (
    <section data-component-id="blog-news-feed" className="py-(--section-y)">
      <Container>
        <header className="mb-6 max-w-3xl">
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="blog-news-feed.eyebrow">{eyebrow}</p>}
          <h2 className="text-headline font-heading break-words" data-editable-id="blog-news-feed.title">{title}</h2>
        </header>
        {lead === "same" ? (
          <div className="border-t border-border">
            {items.map((item, keyIdx) => (
              <Row key={`${item.href}-${keyIdx}`} item={item} date={date} feature={false} />
            ))}
          </div>
        ) : (
          <div className="border-t border-border">
            {items.map((item, index) => (
              <Row key={`${item.href}-${index}`} item={item} date={date} feature={index === 0} />
            ))}
          </div>
        )}
      </Container>
    </section>
  );
}
