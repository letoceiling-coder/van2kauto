#!/usr/bin/env node
// Скачивает опубликованную витрину из админки и пишет content/showcase.json в формате Car (content/cars.ts).
// Запускается перед `next build` (deploy/deploy.sh, deploy/rebuild.sh).
// Если API недоступен — оставляет прошлый файл, чтобы сборка не сломалась и не обнулила каталог.
// SHOWCASE_STRICT=1 — падать при ошибке (для CI).
import { readFile, writeFile } from "node:fs/promises";
import { fileURLToPath, pathToFileURL } from "node:url";
import path from "node:path";

const root = path.dirname(path.dirname(fileURLToPath(import.meta.url)));
const out = path.join(root, "content", "showcase.json");
const url = process.env.SHOWCASE_API_URL || "https://vanauto-api.neeklo.ru/api/v1/showcase";
const strict = process.env.SHOWCASE_STRICT === "1";

const BODY = new Set(["crossover", "sedan", "hatchback", "minivan", "kei", "suv", "wagon", "coupe", "pickup"]);
const ENGINE = new Set(["petrol", "diesel", "hybrid", "phev", "ev", "erev"]);

export function toCar(x) {
  const images = (x.images || []).filter((i) => i && i.src).map((i) => ({ src: i.src, alt: i.alt || `${x.brand} ${x.model} ${x.year}`, kind: "listing" }));
  return {
    slug: x.slug,
    brand: x.brand,
    model: x.model,
    ...(x.generation ? { generation: x.generation } : {}),
    trim: x.trim || "",
    year: Number(x.year),
    country: ["china", "korea", "japan"].includes(x.country) ? x.country : "china",
    condition: x.condition === "new" ? "new" : "used",
    body: BODY.has(x.body) ? x.body : "crossover",
    engine: ENGINE.has(x.engine) ? x.engine : "petrol",
    engineLabel: x.engine_label || "",
    power: Number(x.power_hp) || 0,
    drive: ["FWD", "RWD", "AWD"].includes(x.drive) ? x.drive : "FWD",
    steering: x.steering === "right" ? "right" : "left",
    mileage: Number(x.mileage_km) || 0,
    price: Number(x.price_from_rub) || 0,
    status: ["order", "auction", "stock"].includes(x.status) ? x.status : "order",
    ...(x.hit ? { hit: true } : {}),
    ...(x.badge ? { badge: x.badge } : {}),
    summary: x.summary || "",
    highlights: Array.isArray(x.highlights) ? x.highlights : [],
    images,
    eta: Array.isArray(x.eta) && x.eta.length === 2 ? [Number(x.eta[0]), Number(x.eta[1])] : [30, 45],
    priceFrom: true,
    utilizationOk: x.utilization_ok ?? null,
  };
}

async function main() {
  let prev = null;
  try {
    prev = JSON.parse(await readFile(out, "utf8"));
  } catch {}

  const headers = { Accept: "application/json" };
  try {
    const res = await fetch(url, { headers, signal: AbortSignal.timeout(20000) });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json = await res.json();
    const cars = (json.data || []).filter((x) => x.slug && x.price_from_rub && (x.images || []).length > 0).map(toCar);
    // Пустая витрина на проде = на сайте оказался бы демо-каталог под видом реального. Не допускаем.
    if (cars.length === 0 && strict) throw new Error("витрина пуста — демо-каталог на проде не публикуем");
    const data = { generatedAt: new Date().toISOString(), version: json.meta?.version ?? null, cars };
    await writeFile(out, JSON.stringify(data, null, 2) + "\n");
    console.log(`showcase: ${cars.length} карточек (версия ${data.version}) → content/showcase.json`);
  } catch (e) {
    const msg = `showcase: не удалось получить ${url}: ${e.message}`;
    if (strict) {
      console.error(msg);
      process.exit(1);
    }
    console.warn(`${msg}. Оставляю прошлый файл (${prev?.cars?.length ?? 0} карточек).`);
  }
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) main();
