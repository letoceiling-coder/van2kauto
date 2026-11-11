import Image from "next/image";
import Link from "next/link";
import type { MediaImage } from "@/components/ui/Media";
import { asset, cn } from "@/lib/utils";

export interface FooterClosingPhotoLink {
  label: string;
  href: string;
}

export interface FooterClosingPhotoGroup {
  title: string;
  links: FooterClosingPhotoLink[];
}

export interface FooterClosingPhotoProps {
  brand: string;
  tagline: string;
  image: MediaImage;
  items: FooterClosingPhotoLink[];
  groups: FooterClosingPhotoGroup[];
  copyright: string;
  frame?: "bleed" | "inset";
  links?: "row" | "columns";
}

/** Photo band with a wordmark and links over a dark gradient. */
export function FooterClosingPhoto({
  brand,
  tagline,
  image,
  items,
  groups,
  copyright,
  frame = "bleed",
  links = "row",
}: FooterClosingPhotoProps) {
  return (
    <footer data-component-id="footer-closing-photo" className={cn(frame === "inset" && "bg-background px-3 py-3 sm:px-6 sm:py-6")}>
      <div className={cn("relative overflow-hidden", frame === "inset" && "mx-auto max-w-site radius-xl")}>
        <div className={cn("relative z-10 flex flex-col justify-end", frame === "bleed" ? "min-h-[36rem]" : "min-h-[28rem]")}>
          <Image
            src={asset(image.src)}
            alt={image.alt}
            fill
            sizes="100vw"
            className="object-cover"
            style={{ objectPosition: image.focal ?? "50% 50%" }}
          />
          <div className="absolute inset-0 z-0 bg-gradient-to-t from-black/70 to-transparent" />
          <div className="relative z-10 mx-auto w-full max-w-site px-4 py-10 text-white sm:px-6 sm:py-14 lg:px-8">
            <p className="max-w-full font-heading text-[clamp(3rem,9vw,7.5rem)] leading-[0.85] tracking-tight break-words" data-editable-id="footer.brand">
              {brand}
            </p>
            <p className="mt-4 max-w-xl text-sm opacity-80 sm:text-base" data-editable-id="footer.tagline">{tagline}</p>
            {links === "row" ? (
              <nav className="mt-8 flex flex-wrap gap-x-6 gap-y-3" aria-label="Подвал">
                {items.map((item) => (
                  <Link key={item.href + item.label} href={item.href} className="text-sm opacity-90 hover:opacity-100">
                    {item.label}
                  </Link>
                ))}
              </nav>
            ) : (
              <div className="mt-8 grid gap-8 sm:grid-cols-3">
                {groups.map((group, i) => (
                  <nav key={group.title} aria-label={group.title} className="min-w-0">
                    <p className="eyebrow opacity-70" data-editable-id={`footer.col-${i}`}>{group.title}</p>
                    <ul className="mt-3 flex flex-col gap-2">
                      {group.links.map((link) => (
                        <li key={link.href + link.label}>
                          <Link href={link.href} className="text-sm break-words opacity-90 hover:opacity-100">
                            {link.label}
                          </Link>
                        </li>
                      ))}
                    </ul>
                  </nav>
                ))}
              </div>
            )}
            <p className="mt-8 text-xs opacity-70">{copyright}</p>
          </div>
        </div>
      </div>
    </footer>
  );
}
