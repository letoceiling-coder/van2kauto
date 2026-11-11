import { cn } from "@/lib/utils";

export interface SectionHeadingProps {
  eyebrow?: string;
  title: string;
  text?: string;
  align?: "left" | "center";
  className?: string;
  id?: string;
}

export function SectionHeading({ eyebrow, title, text, align = "left", className, id }: SectionHeadingProps) {
  return (
    <div className={cn("max-w-3xl", align === "center" && "mx-auto text-center", className)}>
      {eyebrow && (
        <p className="mb-4 text-xs font-medium uppercase tracking-[0.2em] text-accent" data-editable-id={id ? `${id}.eyebrow` : undefined}>
          {eyebrow}
        </p>
      )}
      <h2 className="text-3xl leading-[1.1] sm:text-4xl lg:text-5xl" data-editable-id={id ? `${id}.title` : undefined}>
        {title}
      </h2>
      {text && (
        <p className="mt-5 text-base text-muted-foreground sm:text-lg" data-editable-id={id ? `${id}.text` : undefined}>
          {text}
        </p>
      )}
    </div>
  );
}
