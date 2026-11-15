/**
 * Глобальный фон. Только градиенты без filter/blur и без blend-режимов — дёшево для GPU на телефонах.
 * Дрейф бликов включается только на десктопе (md+) и при разрешённой анимации.
 */
export function Backdrop() {
  return (
    <div aria-hidden="true" className="pointer-events-none fixed inset-0 -z-50 overflow-hidden bg-background">
      <div className="backdrop-glow-a absolute -top-[30%] -left-[30%] h-[90%] w-[90%]" />
      <div className="backdrop-glow-b absolute top-[25%] -right-[35%] h-[100%] w-[90%]" />
      <div className="backdrop-grid absolute inset-0" />
    </div>
  );
}
