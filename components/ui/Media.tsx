import Image from "next/image";
import { asset, cn } from "@/lib/utils";

export interface MediaImage {
  src: string;
  alt: string;
  /** CSS object-position, e.g. "50% 30%" (focal point). */
  focal?: string;
}

export interface MediaProps {
  image: MediaImage;
  ratio?: string; // CSS aspect-ratio, e.g. "4 / 5"
  className?: string;
  rounded?: boolean;
  priority?: boolean;
  sizes?: string;
}

/** Responsive image in a fixed aspect-ratio frame (no layout shift). Uses the project basePath. */
export function Media({ image, ratio = "4 / 3", className, rounded = true, priority, sizes = "(min-width: 1024px) 50vw, 100vw" }: MediaProps) {
  return (
    <div className={cn("relative overflow-hidden bg-muted", rounded && "rounded-token", className)} style={{ aspectRatio: ratio }}>
      <Image
        src={asset(image.src)}
        alt={image.alt}
        fill
        sizes={sizes}
        priority={priority}
        className="object-cover"
        style={{ objectPosition: image.focal ?? "50% 50%" }}
      />
    </div>
  );
}
