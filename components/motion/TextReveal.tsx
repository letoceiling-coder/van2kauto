/** Заголовок, который поднимается по словам. Работает на той же CSS-механике, что и Reveal. */
export function TextReveal({ text, as = "h1", className }: { text: string; as?: "h1" | "h2" | "p"; className?: string }) {
  const Tag = as;
  const words = text.split(" ");
  return (
    <Tag data-reveal="words" className={className} aria-label={text}>
      {words.map((w, i) => (
        <span key={i} className="reveal-word" aria-hidden="true">
          <span style={{ "--i": i } as React.CSSProperties}>
            {w}
            {i < words.length - 1 ? " " : ""}
          </span>
        </span>
      ))}
    </Tag>
  );
}
