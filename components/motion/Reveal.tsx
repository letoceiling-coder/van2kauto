/**
 * Появление при прокрутке без JS-фреймворка и без «прыжка» при гидрации.
 * Разметка одинакова на сервере и клиенте; скрытие включается только когда работает JS (html.js),
 * а элементы, видимые сразу при загрузке, показываются без анимации (см. RevealScript).
 */
export function Reveal({ children, delay = 0, className }: { children: React.ReactNode; delay?: number; className?: string }) {
  return (
    <div data-reveal="" className={className} style={delay ? ({ "--reveal-delay": `${delay}s` } as React.CSSProperties) : undefined}>
      {children}
    </div>
  );
}
