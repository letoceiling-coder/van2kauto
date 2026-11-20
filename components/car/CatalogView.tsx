"use client";

import { useEffect, useMemo, useState } from "react";
import { AnimatePresence, motion } from "motion/react";
import { Search, SlidersHorizontal, X, RotateCcw } from "lucide-react";
import { CarCard } from "./CarCard";
import { useLead } from "@/components/site/Lead";
import { type Body, type Car, type Condition, type Country, type Engine, cars as ALL, countries, labels, formatPrice } from "@/content/cars";
import { cn } from "@/lib/utils";

type Sort = "relevance" | "price-asc" | "price-desc" | "year" | "mileage";
interface Filters {
  q: string;
  country: Country[];
  condition: Condition[];
  body: Body[];
  engine: Engine[];
  maxPrice: number;
  drive: "" | "AWD";
  steering: "" | "left" | "right";
}

const MAX = Math.ceil(Math.max(...ALL.map((c) => c.price)) / 500_000) * 500_000;
const EMPTY: Filters = { q: "", country: [], condition: [], body: [], engine: [], maxPrice: MAX, drive: "", steering: "" };

const SYNONYMS: Record<string, string> = {
  "фольксваген": "volkswagen", "фольц": "volkswagen", vw: "volkswagen", "ауди": "audi", "бмв": "bmw", "хонда": "honda", "киа": "kia", "мазда": "mazda", "сузуки": "suzuki", "джимни": "jimny", "бид": "byd", "чанган": "changan", "тайрон": "tayron", "тару": "tharu", "гольф": "golf", "спортейдж": "sportage", "фрид": "freed", "степвагон": "step wgn", "stepwgn": "step wgn", "зикр": "zeekr", "джили": "geely", "монжаро": "monjaro", "тойота": "toyota", "альфард": "alphard", "хендай": "hyundai", "хендэ": "hyundai", "палисад": "palisade", "сигал": "seagull", "дельфин": "dolphin", "dolphine": "dolphin", "maxda": "mazda", "qiyuan": "qiyuan", "киюань": "qiyuan",
};

function norm(s: string) {
  let t = s.toLowerCase().replace(/[-_]/g, " ").trim();
  for (const [k, v] of Object.entries(SYNONYMS)) t = t.replace(k, v);
  return t;
}
function matches(c: Car, q: string) {
  if (!q) return true;
  const hay = norm(`${c.brand} ${c.model} ${c.trim} ${c.generation ?? ""} ${countries[c.country].label}`);
  return norm(q).split(/\s+/).every((w) => hay.includes(w) || hay.replace(/\s/g, "").includes(w));
}

function toggle<T>(arr: T[], v: T) {
  return arr.includes(v) ? arr.filter((x) => x !== v) : [...arr, v];
}

export function CatalogView({ initialCountry, initialCondition, lockCountry }: { initialCountry?: Country; initialCondition?: Condition; lockCountry?: boolean }) {
  const [f, setF] = useState<Filters>({ ...EMPTY, country: initialCountry ? [initialCountry] : [], condition: initialCondition ? [initialCondition] : [] });
  const [sort, setSort] = useState<Sort>("relevance");
  const [sheet, setSheet] = useState(false);
  const lead = useLead();

  // Начальные параметры из URL (?q=, ?country=) — статический экспорт, читаем на клиенте.
  useEffect(() => {
    const p = new URLSearchParams(window.location.search);
    const q = p.get("q");
    const country = p.get("country") as Country | null;
    if (q || (country && country in countries)) {
      // eslint-disable-next-line react-hooks/set-state-in-effect
      setF((s) => ({ ...s, q: q ?? s.q, country: country && country in countries ? [country] : s.country }));
    }
    // Фильтр из адреса применён — показываем сетку (её прятал RevealScript, чтобы не мигал полный список).
    requestAnimationFrame(() => document.documentElement.classList.remove("catalog-pending"));
  }, []);

  useEffect(() => {
    document.documentElement.style.overflow = sheet ? "hidden" : "";
  }, [sheet]);

  const list = useMemo(() => {
    const r = ALL.filter(
      (c) =>
        matches(c, f.q) &&
        (!f.country.length || f.country.includes(c.country)) &&
        (!f.condition.length || f.condition.includes(c.condition)) &&
        (!f.body.length || f.body.includes(c.body)) &&
        (!f.engine.length || f.engine.includes(c.engine)) &&
        c.price <= f.maxPrice &&
        (!f.drive || c.drive === f.drive) &&
        (!f.steering || c.steering === f.steering),
    );
    const by: Record<Sort, (a: Car, b: Car) => number> = {
      relevance: (a, b) => Number(!!b.hit) - Number(!!a.hit),
      "price-asc": (a, b) => a.price - b.price,
      "price-desc": (a, b) => b.price - a.price,
      year: (a, b) => b.year - a.year,
      mileage: (a, b) => a.mileage - b.mileage,
    };
    return [...r].sort(by[sort]);
  }, [f, sort]);

  // Анимация входа переигрывается только когда меняется сам набор/порядок машин, а не на каждый символ поиска.
  const gridKey = list.map((c) => c.slug).join(",");
  const activeCount =
    (lockCountry ? 0 : f.country.length) + f.condition.length + f.body.length + f.engine.length + (f.maxPrice < MAX ? 1 : 0) + (f.drive ? 1 : 0) + (f.steering ? 1 : 0);
  const reset = () => setF({ ...EMPTY, country: lockCountry && initialCountry ? [initialCountry] : [] });

  const panel = (
    <div className="grid gap-7">
      {!lockCountry && (
        <Group title="Страна">
          {(Object.keys(countries) as Country[]).map((c) => (
            <Chip key={c} on={f.country.includes(c)} onClick={() => setF({ ...f, country: toggle(f.country, c) })}>{countries[c].label}</Chip>
          ))}
        </Group>
      )}
      <Group title="Состояние">
        {(["new", "used"] as Condition[]).map((c) => (
          <Chip key={c} on={f.condition.includes(c)} onClick={() => setF({ ...f, condition: toggle(f.condition, c) })}>{labels.condition[c]}</Chip>
        ))}
      </Group>
      <div>
        <div className="mb-3 flex items-center justify-between text-sm">
          <span className="font-medium">Бюджет до Анапы</span>
          <span className="tnum text-accent">до {formatPrice(f.maxPrice)}</span>
        </div>
        <input type="range" min={1_000_000} max={MAX} step={100_000} value={f.maxPrice} onChange={(e) => setF({ ...f, maxPrice: Number(e.target.value) })} aria-label="Максимальный бюджет" className="w-full accent-[#eb111d]" />
      </div>
      <Group title="Кузов">
        {(Object.keys(labels.body) as Body[]).filter((b) => ALL.some((c) => c.body === b)).map((b) => (
          <Chip key={b} on={f.body.includes(b)} onClick={() => setF({ ...f, body: toggle(f.body, b) })}>{labels.body[b]}</Chip>
        ))}
      </Group>
      <Group title="Двигатель">
        {(Object.keys(labels.engine) as Engine[]).filter((e) => ALL.some((c) => c.engine === e)).map((e) => (
          <Chip key={e} on={f.engine.includes(e)} onClick={() => setF({ ...f, engine: toggle(f.engine, e) })}>{e === "erev" ? "EREV" : labels.engine[e]}</Chip>
        ))}
      </Group>
      <Group title="Привод и руль">
        <Chip on={f.drive === "AWD"} onClick={() => setF({ ...f, drive: f.drive ? "" : "AWD" })}>Полный привод</Chip>
        <Chip on={f.steering === "left"} onClick={() => setF({ ...f, steering: f.steering === "left" ? "" : "left" })}>Левый руль</Chip>
        <Chip on={f.steering === "right"} onClick={() => setF({ ...f, steering: f.steering === "right" ? "" : "right" })}>Правый руль</Chip>
      </Group>
    </div>
  );

  return (
    <div className="grid gap-8 lg:grid-cols-[280px_1fr]">
      <aside className="hidden lg:block">
        <div className="card sticky top-24 p-6">
          <div className="mb-6 flex items-center justify-between">
            <p className="font-semibold">Фильтры</p>
            {activeCount > 0 && (
              <button onClick={reset} className="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-accent"><RotateCcw className="size-3.5" />Сбросить</button>
            )}
          </div>
          {panel}
        </div>
      </aside>

      <div className="min-w-0">
        {/* Липкая панель: на телефоне остаётся под шапкой при скролле выдачи */}
        <div className="sticky top-16 z-30 -mx-4 bg-background/95 px-4 pt-2 pb-3 sm:static sm:mx-0 sm:bg-transparent sm:p-0">
          <div className="flex gap-2">
            <div className="flex h-12 min-w-0 flex-1 items-center gap-2.5 rounded-full border border-white/10 bg-surface pr-1.5 pl-4 transition focus-within:border-accent/60 sm:h-13 sm:pl-5">
              <Search className="size-[18px] shrink-0 text-muted-foreground" />
              <label htmlFor="cat-q" className="sr-only">Поиск по марке или модели</label>
              <input id="cat-q" type="search" enterKeyHint="search" value={f.q} onChange={(e) => setF({ ...f, q: e.target.value })} placeholder="Марка или модель" className="h-full min-w-0 flex-1 bg-transparent outline-none placeholder:text-muted-foreground/70 [&::-webkit-search-cancel-button]:hidden" />
              {f.q && <button onClick={() => setF({ ...f, q: "" })} aria-label="Очистить поиск" className="grid size-9 shrink-0 place-items-center rounded-full hover:bg-white/5"><X className="size-4" /></button>}
            </div>
            <button onClick={() => setSheet(true)} aria-label="Фильтры" className="relative grid size-12 shrink-0 place-items-center rounded-full border border-white/10 bg-surface lg:hidden">
              <SlidersHorizontal className="size-[18px]" />
              {activeCount > 0 && <span className="absolute -top-1 -right-1 grid size-5 place-items-center rounded-full bg-accent text-[11px] font-bold text-accent-foreground">{activeCount}</span>}
            </button>
            <label htmlFor="cat-sort" className="sr-only">Сортировка</label>
            <select id="cat-sort" value={sort} onChange={(e) => setSort(e.target.value as Sort)} className="hidden h-13 cursor-pointer appearance-none rounded-full border border-white/10 bg-surface px-5 text-sm outline-none sm:block">
              <option value="relevance">По актуальности</option>
              <option value="price-asc">Сначала дешевле</option>
              <option value="price-desc">Сначала дороже</option>
              <option value="year">Сначала новее</option>
              <option value="mileage">По пробегу</option>
            </select>
          </div>
          <div className="no-scrollbar mt-3 -mx-4 flex gap-2 overflow-x-auto px-4 sm:mx-0 sm:mt-4 sm:px-0">
            {!lockCountry &&
              (Object.keys(countries) as Country[]).map((c) => (
                <Chip key={c} on={f.country.includes(c)} onClick={() => setF({ ...f, country: toggle(f.country, c) })}>{countries[c].label}</Chip>
              ))}
            <Chip on={f.condition.includes("new")} onClick={() => setF({ ...f, condition: toggle(f.condition, "new") })}>Новые</Chip>
            <Chip on={f.condition.includes("used")} onClick={() => setF({ ...f, condition: toggle(f.condition, "used") })}>С пробегом</Chip>
            <Chip on={f.engine.includes("ev")} onClick={() => setF({ ...f, engine: toggle(f.engine, "ev") })}>Электро</Chip>
            <Chip on={f.maxPrice <= 2_500_000} onClick={() => setF({ ...f, maxPrice: f.maxPrice <= 2_500_000 ? MAX : 2_500_000 })}>До 2,5 млн</Chip>
          </div>
        </div>

        <div className="mt-4 mb-5 flex items-center justify-between gap-3 sm:mt-6">
          <p className="text-sm text-muted-foreground" aria-live="polite">
            Найдено <span className="tnum font-semibold text-foreground">{list.length}</span> {plural(list.length, ["автомобиль", "автомобиля", "автомобилей"])}
          </p>
          <label htmlFor="cat-sort-m" className="sr-only">Сортировка</label>
          <select id="cat-sort-m" value={sort} onChange={(e) => setSort(e.target.value as Sort)} className="h-9 appearance-none rounded-full bg-transparent pr-1 text-right text-sm text-foreground/80 outline-none sm:hidden">
            <option value="relevance">По актуальности ↓</option>
            <option value="price-asc">Сначала дешевле ↓</option>
            <option value="price-desc">Сначала дороже ↓</option>
            <option value="year">Сначала новее ↓</option>
            <option value="mileage">По пробегу ↓</option>
          </select>
        </div>

        {list.length ? (
          // Без layout-анимаций: при смене фильтра сетка не «прыгает», карточки просто проявляются.
          <div key={gridKey} data-catalog-results="" className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            {list.map((c, i) => (
              <div key={c.slug} className="card-in" style={{ animationDelay: `${Math.min(i, 8) * 40}ms` }}>
                <CarCard car={c} priority={i < 2} />
              </div>
            ))}
          </div>
        ) : (
          <div className="card grid place-items-center px-6 py-16 text-center">
            <p className="text-2xl font-semibold tracking-tight">Ничего не нашлось</p>
            <p className="mt-2 max-w-sm text-muted-foreground">Измените один из фильтров — или оставьте запрос, и мы найдём лот на площадках под ваш бюджет.</p>
            <div className="mt-6 flex flex-wrap justify-center gap-2">
              <button onClick={reset} className="h-12 rounded-full border border-white/15 px-6 text-sm font-semibold">Сбросить фильтры</button>
              <button onClick={() => lead({ title: "Найти автомобиль под запрос", context: f.q || undefined })} className="btn-accent h-12 rounded-full px-6 text-sm font-semibold">Найти под запрос</button>
            </div>
          </div>
        )}
      </div>

      <AnimatePresence>
        {sheet && (
          <motion.div className="fixed inset-0 z-[90] lg:hidden" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
            <div className="absolute inset-0 bg-black/70" onClick={() => setSheet(false)} />
            <motion.div
              role="dialog"
              aria-modal="true"
              aria-label="Фильтры"
              initial={{ y: "100%" }}
              animate={{ y: 0 }}
              exit={{ y: "100%" }}
              transition={{ type: "spring", damping: 32, stiffness: 320 }}
              className="absolute inset-x-0 bottom-0 flex max-h-[88svh] flex-col rounded-t-[1.75rem] border-t border-white/10 bg-surface"
            >
              <div className="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                <p className="text-lg font-semibold">Фильтры</p>
                <div className="flex items-center gap-2">
                  {activeCount > 0 && <button onClick={reset} className="px-2 text-sm text-muted-foreground">Сбросить</button>}
                  <button onClick={() => setSheet(false)} aria-label="Закрыть" className="grid size-10 place-items-center rounded-full bg-white/5"><X className="size-5" /></button>
                </div>
              </div>
              <div className="overflow-y-auto px-5 py-6">{panel}</div>
              <div className="pb-safe border-t border-white/[0.06] px-5 pt-3">
                <button onClick={() => setSheet(false)} className="btn-accent h-14 w-full rounded-full font-semibold">
                  Показать {list.length} {plural(list.length, ["авто", "авто", "авто"])}
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

function Group({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <fieldset>
      <legend className="mb-3 text-sm font-medium">{title}</legend>
      <div className="flex flex-wrap gap-2">{children}</div>
    </fieldset>
  );
}

function Chip({ on, onClick, children }: { on: boolean; onClick: () => void; children: React.ReactNode }) {
  return (
    <button type="button" onClick={onClick} aria-pressed={on} className={cn("h-10 shrink-0 rounded-full border px-4 text-sm whitespace-nowrap transition active:scale-95", on ? "border-accent bg-accent font-medium text-accent-foreground" : "border-white/10 bg-white/[0.02] text-foreground/80 hover:border-white/25")}>
      {children}
    </button>
  );
}

function plural(n: number, f: [string, string, string]) {
  const m10 = n % 10, m100 = n % 100;
  return m10 === 1 && m100 !== 11 ? f[0] : m10 >= 2 && m10 <= 4 && (m100 < 10 || m100 >= 20) ? f[1] : f[2];
}
