/** Мягкий вход страницы при навигации. CSS-анимация (не JS): не ждёт гидрации и не прячет контент от LCP. */
export default function Template({ children }: { children: React.ReactNode }) {
  return <div className="page-enter">{children}</div>;
}
