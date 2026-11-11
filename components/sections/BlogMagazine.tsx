import Image from "next/image";
import Link from "next/link";
import { Container } from "@/components/layout/Container";
import type { MediaImage } from "@/components/ui/Media";
import { asset } from "@/lib/utils";

export interface MagazinePost {
  title: string;
  excerpt: string;
  rubric: string;
  date: string;
  href: string;
  image: MediaImage;
}

export interface BlogMagazineProps {
  eyebrow?: string;
  title: string;
  text?: string;
  posts: MagazinePost[];
  lead?: "image" | "type";
  list?: "side" | "below";
}

function Chip({ children }: { children: string }) {
  return <span className="rounded-full bg-secondary px-3 py-1 text-xs text-secondary-foreground">{children}</span>;
}

function Lead({ post, lead }: { post: MagazinePost; lead: "image" | "type" }) {
  if (lead === "type") {
    return (
      <Link href={post.href} className="radius-xl block min-w-0 bg-muted p-8 sm:p-12">
        <Chip>{post.rubric}</Chip>
        <p className="mt-6 font-heading text-display break-words">{post.rubric}</p>
        <h3 className="mt-6 font-heading text-title break-words">{post.title}</h3>
        <p className="mt-4 max-w-xl text-muted-foreground break-words">{post.excerpt}</p>
        <p className="mt-6 text-sm text-muted-foreground">{post.date}</p>
      </Link>
    );
  }
  return (
    <Link href={post.href} className="group block min-w-0">
      <span className="radius-xl relative block aspect-[16/10] overflow-hidden bg-muted">
        <Image src={asset(post.image.src)} alt={post.image.alt} fill sizes="(min-width: 1024px) 60vw, 100vw" className="object-cover transition-transform duration-700 group-hover:scale-[1.03] motion-reduce:transform-none" style={{ objectPosition: post.image.focal ?? "50% 50%" }} />
      </span>
      <span className="mt-5 flex items-center gap-3">
        <Chip>{post.rubric}</Chip>
        <span className="text-sm text-muted-foreground">{post.date}</span>
      </span>
      <h3 className="mt-4 font-heading text-title break-words">{post.title}</h3>
      <p className="mt-3 text-muted-foreground break-words">{post.excerpt}</p>
    </Link>
  );
}

function Others({ posts }: { posts: MagazinePost[] }) {
  return (
    <ul className="divide-y divide-border">
      {posts.map((post, keyIdx) => (
        <li key={`${post.href}-${keyIdx}`} className="py-5">
          <Link href={post.href} className="block min-w-0">
            <Chip>{post.rubric}</Chip>
            <h3 className="mt-3 font-heading text-lg break-words">{post.title}</h3>
            <p className="mt-2 text-sm text-muted-foreground break-words">{post.excerpt}</p>
            <p className="mt-2 text-xs text-muted-foreground">{post.date}</p>
          </Link>
        </li>
      ))}
    </ul>
  );
}

/** Magazine lead plus a rubric list for journals, law portals, and clinics. */
export function BlogMagazine({ eyebrow, title, text, posts, lead = "image", list = "side" }: BlogMagazineProps) {
  const [first, ...rest] = posts;
  return (
    <section data-component-id="blog-magazine" className="py-(--section-y)">
      <Container>
        <header className="mb-10 max-w-3xl">
          {eyebrow && <p className="eyebrow mb-4 text-accent" data-editable-id="blog-magazine.eyebrow">{eyebrow}</p>}
          <h2 className="text-headline font-heading break-words" data-editable-id="blog-magazine.title">{title}</h2>
          {text && <p className="mt-5 text-lg text-muted-foreground" data-editable-id="blog-magazine.text">{text}</p>}
        </header>
        {first && list === "side" ? (
          <div className="grid items-start gap-10 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)]">
            <Lead post={first} lead={lead} />
            <Others posts={rest} />
          </div>
        ) : first ? (
          <div className="space-y-10">
            <Lead post={first} lead={lead} />
            <Others posts={rest} />
          </div>
        ) : null}
      </Container>
    </section>
  );
}
