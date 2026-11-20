/**
 * Число со стабильной шириной (tabular-nums). Раньше считало от 0 — на первом экране это давало «0» до загрузки JS
 * и прыгающую ширину. Теперь сразу итоговое значение: спокойно и без сдвигов.
 */
export function Counter({ to, suffix = "", prefix = "" }: { to: number; suffix?: string; prefix?: string; duration?: number }) {
  return (
    <span className="tnum">
      {prefix}
      {new Intl.NumberFormat("ru-RU").format(to).replace(/ /g, " ")}
      {suffix}
    </span>
  );
}
