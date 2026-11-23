# Аудит global.che168.com — API, парсинг, БД, фото

Дата: 2026-11-15  
Объекты: [каталог EN](https://global.che168.com/en/used-cars), [карточка примера](https://global.che168.com/en/detail/60515670)

---

## 1. Архитектура сайта

| Слой | Технология |
|------|------------|
| Фронт | **Next.js** (App Router, RSC/SSR) |
| UI | Ant Design |
| Публичный API | `https://globalapi.che168.com/api/v1/*` |
| Защита API | **Tencent Cloud EdgeOne** (капча при «холодных» запросах с `curl`/скриптов без cookies) |
| Изображения | CDN `erscglobal2.autoimg.cn/escimg/auto/...` |
| Аналитика | GTM, GA, che168/al.autohome event |

**Важно:** listing SSR отдаёт JSON-LD (`ItemList` / `Car`) для SEO, но полный каталог и фильтры — через клиентский вызов `/api/v1/search`. Детальная карточка при SSR уже содержит данные; в рантайме догружаются отчёты, порты, рекомендации.

**Практика для парсера:** ходить в `globalapi` с **сессией браузера** (Playwright/Puppeteer) или прокидывать cookies после прохождения EdgeOne. Прямой `curl` с IP дата-центра часто получает HTML «Security Verification».

---

## 2. Карта API (из бандла `2422-aaffa4ef9d107841.js`)

Базовый URL: `https://globalapi.che168.com`

### Общие query-параметры (почти все методы)

| Параметр | Пример | Назначение |
|----------|--------|------------|
| `_appid` | `global.m` | Идентификатор клиента |
| `deviceid` | UUID (localStorage `che_session`) | Сессия/антибот |
| `language` | `en`, `ru`, `zh`, … | Локаль ответов |
| `fromsource` | `0` | Канал трафика |

### Эндпоинты

| Метод | Путь | Назначение |
|-------|------|------------|
| GET | `/api/v1/search` | **Каталог** (пагинация + фильтры) |
| GET | `/api/v1/carinfo/{infoid}` | **Деталь объявления** + галерея |
| GET | `/api/v1/brand` | Справочник марок (`bid`, `name`, `icon`) |
| GET | `/api/v1/series` | Серии по `brandid` |
| GET | `/api/v1/series/hot`, `/top` | Популярные серии |
| GET | `/api/v2/series/hot` | v2 hot series |
| GET | `/api/v1/specparam` | Тех. параметры комплектации (`specid`) |
| GET | `/api/v1/specconfig` | Опции/комплектация (`specid`) |
| GET | `/api/v1/recommend` | Похожие авто (`infoid`) |
| GET | `/api/v1/report/summary` | Сводка отчёта (**`paramkey`**, не `infoid`) |
| GET | `/api/v1/getcarreport` | Отчёт (на тесте — «无效的车源ID» без доп. параметров) |
| GET | `/api/v1/sale/info`, `/sale/list` | Контакты экспорт-консультантов |
| GET | `/api/v1/country/*` | Страны, детали, hot series |
| GET | `/api/v1/shipping/port/list`, `overseas/port/list`, `quote/info` | Логистика |
| GET | `/api/v1/home/overview`, `/lang` | Главная, языки |
| POST | `/api/v1/quote/record/add` | Заявка на котировку |

Формат ответа (типовой):

```json
{
  "returncode": 0,
  "message": "成功",
  "result": { }
}
```

`returncode !== 0` — ошибка бизнес-логики (часто HTTP 200).

---

## 3. Каталог: `/api/v1/search`

### Пагинация (проверено)

| Параметр | Описание |
|----------|----------|
| `pageindex` | Страница, с 1 |
| `pagesize` | До ~100 за запрос (при `100` вернулось 97 записей) |
| `sort` | `0` — recommended, `1` — другой порядок (цена/дата — уточнять по UI) |

### Фильтры (имена из JS + проверка)

| Параметр | Единицы / примечание |
|----------|----------------------|
| `vehicle_list` | `1` — расширенный пул (~240k объявлений); без него — меньший набор |
| `keywords` | Поиск по тексту (`avalon` → 458) |
| `brandid`, `seriesid` | ID из `/brand`, `/series` |
| `min_price`, `max_price` | **Тысячи USD** (UI: «Price (grand)») |
| `min_mileage`, `max_mileage` | **Тысячи км** (UI: «Mileage (K)»); `1–999` → пробег «1000» км |
| `min_year`, `max_year` | Год |
| `min_regdate`, `max_regdate` | Дата регистрации (формат уточнить по UI) |
| `structure` | Кузов (ID; `4` → 1826 авто) |
| `fueltype` | Тип топлива (ID) |
| `gearbox`, `transmission` | КПП |
| `setcount` | Кол-во мест |
| `level` | Класс авто |
| `min_horse_power`, `max_horse_power` | Л.с. |
| `report_status` | Статус отчёта (`1` → ~79k) |
| `cert_status`, `claim_status`, `mr_status` | Флаги из карточки листинга (см. ниже) |

Дефолт на `/en/used-cars` (Network):  
`report_status=1,3`, `vehicle_list=1`, `pagesize=24`, `sort=0`.

### Поля элемента `result.carlist[]`

| Поле | Тип | Описание |
|------|-----|----------|
| `infoid` | int | **PK объявления** |
| `carname` | string | Полное название |
| `brandname`, `seriesname`, `specname` | string | Иерархия |
| `price` | string | USD |
| `mileage` | string | км |
| `regdate` | string | `YYYY.MM` |
| `fuelname` | string | Топливо |
| `imageurl` | url | Превью 640×480 webp |
| `cname` | string | Город (slug) |
| `dealerid` | int | Дилер |
| `cert_status`, `claim_status`, `mr_status` | int | Статусы (1/3/10 на примерах) |
| `wechat_url`, `whatsapp_url` | url | Контакты |

Мета пагинации: `totalcount`, `pagecount`, `queryid`.

**Объём (на момент аудита):** с `vehicle_list=1` ≈ **239 808** объявлений; с дефолтными фильтрами сайта — порядка **4–5k** (export/verified subset).

---

## 4. Деталь: `/api/v1/carinfo/{infoid}`

Пример: `60515670` (Toyota Avalon).

### Поля `result` (основные для БД)

| Поле | Пример | Группа |
|------|--------|--------|
| `infoid` | 60515670 | Listing |
| `brandid`, `brandname` | 3, Toyota | Brand |
| `seriesid`, `seriesname` | 4962, Avalon | Series |
| `specid`, `specname` | 55424, 2023 2.5L… | Spec |
| `carname` | полное имя | Listing |
| `price` | 19850 | Price |
| `export_fee`, `port_fee` | 400, 530 | Fees |
| `mileage` | 24800 | Usage |
| `regdate`, `producedate`, `yearname` | 2023.10, … | Dates |
| `fuelname`, `engine`, `gearbox` | Gasoline, 2.5L… | Powertrain |
| `drivingmode`, `steering` | FWD, Left | Chassis |
| `structure`, `structuredoor`, `level` | Sedan, 4, Mid-size | Body |
| `dimension`, `curbweight` | 4990*1850*1450, 1630 | Size |
| `color` | Silver | Appearance |
| `setcount` | 5 | Seats |
| `vincode` | masked | Legal |
| `cid`, `cname`, `dealeid` | 610100, xian, 466721 | Geo/dealer |
| `certification_status` | 1 | Trust |
| `paramkey` | token | Reports |
| `report_url` | activitym.che168.com… | External report |
| `catepiclist[]` | массив URL фото | **Gallery** |
| `wechat_url`, `whatsapp_url` | … | Contacts |

### Галерея

`catepiclist`: массив объектов `{ title, list: string[] }`.  
24 фото в примере; URL вида `.../1400x0_c42_autohomecar__<hash>.jpg.webp`.

---

## 5. Комплектация и характеристики

### `/api/v1/specparam?specid={id}`

`result.paramtypeitems[]` → группы (Basic Specifications, Body, Engine, …)  
→ `paramitems[]`: `{ id, name, value, sublist }`  
Сотни полей (МSRP ¥, разгон, расход, габариты и т.д.) — **привязка к `specid`**, общая для всех объявлений этой комплектации.

### `/api/v1/specconfig?specid={id}`

`result.configtypeitems[]` (Passive Safety, ADAS, Interior, …)  
→ `configitems[]`: `{ configid, name, disptype, valueitems[] }`  
Значения: `●`, текст, `sublist` для вложенных опций.

### `/api/v1/report/summary?paramkey={key}`

```json
{
  "claimsCount": 1,
  "feeMaxStr": "1000",
  "insuranceUrl": "https://czym.che168.com/...reporttype=100...",
  "maintenanceUrl": "https://czym.che168.com/...reporttype=500...",
  "latestMileage": "24475",
  "tnum": 8
}
```

`paramkey` берётся из `carinfo`.

---

## 6. Фото: превью и «оригинал»

Шаблон пути:

```
https://erscglobal2.autoimg.cn/escimg/auto/{g}/{M}/{MM}/{DD}/{WxH}_c42_autohomecar__{HASH}.jpg[.webp][?format=webp&w=N]
```

| Вариант | URL | Content-Type | ~Размер (пример) |
|---------|-----|--------------|------------------|
| Превью листинг | `640x480_...jpg.webp` | image/webp | 15 KB |
| Превью UI | `...?format=webp&w=540` | image/webp | 15 KB |
| Деталь / большое | `1400x0_...jpg.webp` | image/webp | 28 KB |
| Максимум webp | `0x0_...jpg.webp` | image/webp | 28 KB |
| **Оригинал JPEG** | `0x0_...jpg` (без `.webp`) | **image/jpeg** | **56 KB** |

Функции для пайплайна:

```text
preview_webp(url)  → нормализовать к 640x480 + .webp
large_webp(url)    → 1400x0 или 0x0 + .webp
original_jpeg(url) → 0x0 + .jpg (убрать .webp и query)
```

Имя файла для хранения: `{infoid}_{index}_{hash}.jpg`.

CDN обычно доступен без auth; EdgeOne на **API**, не на статике.

---

## 7. Модель данных (нормализация)

### Иерархия каталога Autohome

```
Brand (brandid)
  └── Series (seriesid)
        └── Spec / Trim (specid)  ← specparam, specconfig
              └── Listing (infoid)  ← конкретное б/у объявление
```

### Предлагаемая схема PostgreSQL (сокращённо)

```sql
-- Справочники
CREATE TABLE brands (
  brand_id    INT PRIMARY KEY,
  name        TEXT NOT NULL,
  py          TEXT,
  icon_url    TEXT
);

CREATE TABLE series (
  series_id   INT PRIMARY KEY,
  brand_id    INT REFERENCES brands(brand_id),
  name        TEXT NOT NULL,
  icon_url    TEXT
);

CREATE TABLE specs (
  spec_id     INT PRIMARY KEY,
  series_id   INT REFERENCES series(series_id),
  name        TEXT NOT NULL,
  year_label  TEXT,
  raw_param   JSONB,   -- кэш specparam
  raw_config  JSONB    -- кэш specconfig
);

-- Объявления
CREATE TABLE listings (
  infoid            BIGINT PRIMARY KEY,
  spec_id           INT REFERENCES specs(spec_id),
  dealer_id         INT,
  title             TEXT,
  price_usd         NUMERIC(12,2),
  export_fee_usd    NUMERIC(12,2),
  port_fee_usd      NUMERIC(12,2),
  mileage_km        INT,
  reg_date          TEXT,
  produce_date      TIMESTAMPTZ,
  city_slug         TEXT,
  city_id           INT,
  color             TEXT,
  vin_masked        TEXT,
  fuel_name         TEXT,
  engine            TEXT,
  gearbox           TEXT,
  driving_mode      TEXT,
  steering          TEXT,
  body_type         TEXT,
  doors             SMALLINT,
  seats             SMALLINT,
  class_level       TEXT,
  dimension         TEXT,
  curb_weight_kg    INT,
  cert_status       SMALLINT,
  claim_status      SMALLINT,
  mr_status         SMALLINT,
  certification     SMALLINT,
  paramkey          TEXT,
  report_url        TEXT,
  detail_url        TEXT,
  whatsapp_url      TEXT,
  wechat_url        TEXT,
  scraped_at        TIMESTAMPTZ DEFAULT now(),
  raw_carinfo       JSONB
);

CREATE TABLE listing_photos (
  id            BIGSERIAL PRIMARY KEY,
  infoid        BIGINT REFERENCES listings(infoid),
  sort_order    INT,
  preview_url   TEXT,
  large_url     TEXT,
  original_url  TEXT,
  file_path     TEXT,
  UNIQUE (infoid, sort_order)
);

CREATE TABLE listing_reports (
  infoid              BIGINT PRIMARY KEY REFERENCES listings(infoid),
  claims_count        INT,
  latest_mileage_km   INT,
  insurance_url       TEXT,
  maintenance_url     TEXT,
  raw_summary         JSONB
);

-- Фильтруемые атрибуты (денормализация для индексов)
CREATE TABLE listing_facets (
  infoid        BIGINT PRIMARY KEY REFERENCES listings(infoid),
  brand_id      INT,
  series_id     INT,
  fuel_type_id  INT,
  structure_id  INT,
  year          SMALLINT,
  price_bucket  INT,
  mileage_k     INT,
  horsepower    INT
);
```

### Индексы для фильтров (как на сайте)

- `listings (price_usd)`, `(mileage_km)`, `(reg_date)`
- `listing_facets (brand_id, series_id, fuel_type_id, structure_id, year, price_bucket, mileage_k)`
- GIN `specs.raw_param` / `listings.raw_carinfo` для full-text по `keywords`

Маппинг UI → API: хранить таблицу `filter_option_dim (dim, option_id, label_en)` — значения `structure`, `fueltype`, `level` подгружать из ответов search + UI (ID эмпирически, как `structure=4`).

---

## 8. Алгоритм полного парсинга

```
1. Инициализация сессии
   - Открыть https://global.che168.com/en/used-cars в headless браузере
   - Сохранить deviceid (UUID) — стабильный для сессии

2. Синхронизация справочников (раз в сутки)
   - GET /api/v1/brand → brands
   - Для каждого brand_id: GET /api/v1/series?brandid=… → series

3. Обход каталога
   - GET /api/v1/search?vehicle_list=1&pageindex=N&pagesize=100&language=en&…
   - UPSERT listings (поля из carlist)
   - Очередь infoid для детализации

4. Детализация (worker pool, rate limit ~2–5 rps)
   - GET /api/v1/carinfo/{infoid}
   - UPSERT listings + listing_photos
   - Если новый spec_id: GET specparam, specconfig
   - GET /api/v1/report/summary?paramkey=…

5. Медиа
   - Для каждого photo: download original_jpeg + preview_webp
   - SHA256 dedup, storage S3/диск

6. Инкремент
   - Ежедневно: page 1..N пока infoid not in DB или updated
   - Помечать снятые: infoid не встречается N дней → status=inactive
```

Оценка объёма: ~240k × (1 carinfo + ~24 фото) — планировать батчи и resume по `pageindex` / checkpoint table.

---

## 9. Риски и ограничения

1. **EdgeOne** — без браузерной сессии API недоступен.
2. **ToS / robots** — массовый скрейпинг может нарушать условия; нужен rate limit и согласование с Autohome.
3. **Динамический каталог** — `totalcount` меняется; нужен `scraped_at` и переобход.
4. **Отчёты** — внешние домены (`czym.che168.com`, `activitym.che168.com`), отдельный парсинг PDF/HTML.
5. **Локали** — `language=en|ru|…` влияет на строковые поля; для мульти-языка дублировать или хранить `language` в listings.
6. **`getcarreport`** — на тесте требует параметры, отличные от `infoid`; использовать `report_url` / `paramkey`.

---

## 10. Минимальные примеры запросов (в браузере)

```http
GET https://globalapi.che168.com/api/v1/search?_appid=global.m&deviceid=<UUID>&language=en&fromsource=0&pageindex=1&pagesize=24&sort=0&vehicle_list=1&report_status=1,3

GET https://globalapi.che168.com/api/v1/carinfo/60515670?_appid=global.m&deviceid=<UUID>&language=en&fromsource=0

GET https://globalapi.che168.com/api/v1/specparam?specid=55424&_appid=global.m&deviceid=<UUID>&language=en&fromsource=0

GET https://globalapi.che168.com/api/v1/report/summary?paramkey=a5SY5LJVglcku4hvpkGcyJHRqDSbyHyY&_appid=global.m&deviceid=<UUID>&language=en&fromsource=0
```

---

## 11. Следующие шаги (реализация)

1. Репозиторий: Python (`httpx` + Playwright) или Node (`playwright`).
2. Модуль `Che168Client` с cookie jar и общими query params.
3. Миграции под схему выше + таблица `scrape_checkpoints`.
4. CLI: `sync-brands`, `sync-listings`, `sync-details`, `download-photos`.
5. Опционально: REST API поверх БД с теми же фильтрами, что `search`.

---

*Аудит выполнен анализом Network, reverse JS API map и живых ответов API.*