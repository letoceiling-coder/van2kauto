// Каталог VAN2KAUTO. Демо-ассортимент: цены — предварительный ориентир «под ключ до Анапы»,
// финальная смета формируется менеджером по конкретному лоту (ТЗ §6).

import showcase from "./showcase.json";

export type Country = "china" | "korea" | "japan";
export type Condition = "new" | "used";
export type Body = "crossover" | "sedan" | "hatchback" | "minivan" | "kei" | "suv" | "wagon" | "coupe" | "pickup";
export type Engine = "petrol" | "diesel" | "hybrid" | "phev" | "ev" | "erev";
export type Status = "order" | "auction" | "stock";

export interface CarImage {
  src: string;
  alt: string;
  /** Тип фото по ТЗ §4: объявление, осмотр, выдача, иллюстрация модели. */
  kind?: "listing" | "inspection" | "delivery" | "model";
}

export interface Car {
  slug: string;
  brand: string;
  model: string;
  generation?: string;
  trim: string;
  year: number;
  country: Country;
  condition: Condition;
  body: Body;
  engine: Engine;
  engineLabel: string;
  power: number;
  drive: "FWD" | "RWD" | "AWD";
  steering: "left" | "right";
  mileage: number;
  price: number;
  status: Status;
  hit?: boolean;
  badge?: string;
  summary: string;
  highlights: string[];
  images: CarImage[];
  /** Срок от предоплаты до выдачи, дней. */
  eta: [number, number];
  /** Карточка из витрины админки: цена — «от», точная по запросу. */
  priceFrom?: boolean;
  /** Проходной по утильсбору (≤160 л.с., возраст по правилу из админки); null — неизвестно. */
  utilizationOk?: boolean | null;
}

export const countries: Record<Country, { label: string; from: string; flag: string; href: string; city: string; road: string }> = {
  china: { label: "Китай", from: "из Китая", flag: "CN", href: "/cars-from-china", city: "Шанхай · Гуанчжоу", road: "ж/д + автовоз через Забайкальск" },
  korea: { label: "Корея", from: "из Кореи", flag: "KR", href: "/cars-from-korea", city: "Сеул · Пусан", road: "паром до Владивостока + ж/д" },
  japan: { label: "Япония", from: "из Японии", flag: "JP", href: "/cars-from-japan", city: "Токио · Иокогама", road: "аукцион → Владивосток + ж/д" },
};

export const labels = {
  condition: { new: "Новый", used: "С пробегом" } as Record<Condition, string>,
  body: { crossover: "Кроссовер", sedan: "Седан", hatchback: "Хэтчбек", minivan: "Минивэн", kei: "Кей-кар", suv: "Внедорожник", wagon: "Универсал", coupe: "Купе", pickup: "Пикап" } as Record<Body, string>,
  engine: { petrol: "Бензин", diesel: "Дизель", hybrid: "Гибрид", phev: "Подключаемый гибрид", ev: "Электро", erev: "Последовательный гибрид" } as Record<Engine, string>,
  drive: { FWD: "Передний", RWD: "Задний", AWD: "Полный" } as Record<Car["drive"], string>,
  steering: { left: "Левый", right: "Правый" } as Record<Car["steering"], string>,
  status: { order: "Под заказ", auction: "Аукцион", stock: "В наличии в РФ" } as Record<Status, string>,
};

const img = (slug: string, n: number, alt: string, kind: CarImage["kind"] = "model"): CarImage => ({
  src: `/media/cars/${slug}-${n}.webp`,
  alt,
  kind,
});

export const PRICE_DATE = "15.11.2026";
export const PRICE_CITY = "Анапы";

/** Демо-ассортимент: показывается, пока в витрине админки нет опубликованных карточек. */
export const demoCars: Car[] = [
  {
    slug: "changan-qiyuan-q05",
    brand: "Changan",
    model: "Qiyuan Q05",
    trim: "EV 510 km Pro",
    year: 2025,
    country: "china",
    condition: "new",
    body: "crossover",
    engine: "ev",
    engineLabel: "Электро, 160 кВт",
    power: 218,
    drive: "FWD",
    steering: "left",
    mileage: 0,
    price: 2_390_000,
    status: "order",
    hit: true,
    badge: "Хит продаж",
    summary: "Компактный электрокроссовер с запасом хода до 510 км и цифровым салоном — самый частый запрос сезона.",
    highlights: ["Запас хода до 510 км по CLTC", "Быстрая зарядка 30→80% за 25 мин", "Экран 14,6″ и адаптивный круиз"],
    images: [],
    eta: [35, 50],
  },
  {
    slug: "changan-qiyuan-a06",
    brand: "Changan",
    model: "Qiyuan A06",
    trim: "EREV 1300 km",
    year: 2025,
    country: "china",
    condition: "new",
    body: "sedan",
    engine: "erev",
    engineLabel: "Генератор 1.5 + электромотор",
    power: 218,
    drive: "RWD",
    steering: "left",
    mileage: 0,
    price: 2_490_000,
    status: "order",
    hit: true,
    badge: "Хит продаж",
    summary: "Аэродинамичный седан с последовательным гибридом: едет на электричестве, бензин только заряжает батарею.",
    highlights: ["Суммарный запас хода до 1300 км", "Cx 0,199 — один из лучших в классе", "Задний привод и мягкая подвеска"],
    images: [],
    eta: [35, 50],
  },
  {
    slug: "audi-a3l",
    brand: "Audi",
    model: "A3L",
    generation: "8Y",
    trim: "Sedan 35 TFSI Advanced",
    year: 2024,
    country: "china",
    condition: "new",
    body: "sedan",
    engine: "petrol",
    engineLabel: "1.4 TFSI, 7 DSG",
    power: 150,
    drive: "FWD",
    steering: "left",
    mileage: 0,
    price: 2_890_000,
    status: "order",
    hit: true,
    summary: "Удлинённый седан A3 китайской сборки FAW-Audi: премиальный салон, экономичный мотор и выгодный растаможенный объём.",
    highlights: ["Удлинённая база для задних пассажиров", "Virtual Cockpit и MMI", "Двигатель до 160 л.с. — щадящая пошлина"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "vw-golf-8",
    brand: "Volkswagen",
    model: "Golf",
    generation: "Mk8",
    trim: "280TSI DSG Pro",
    year: 2024,
    country: "china",
    condition: "new",
    body: "hatchback",
    engine: "petrol",
    engineLabel: "1.4 TSI, 7 DSG",
    power: 150,
    drive: "FWD",
    steering: "left",
    mileage: 0,
    price: 2_590_000,
    status: "order",
    hit: true,
    summary: "Эталонный хэтчбек гольф-класса в свежем поколении. Собирается FAW-Volkswagen для внутреннего рынка Китая.",
    highlights: ["Цифровая панель и IQ.Light", "Расход 6,1 л/100 км", "Ликвидная модель на вторичном рынке"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "vw-tayron",
    brand: "Volkswagen",
    model: "Tayron",
    trim: "380TSI 4Motion R-Line",
    year: 2025,
    country: "china",
    condition: "new",
    body: "crossover",
    engine: "petrol",
    engineLabel: "2.0 TSI, 7 DSG",
    power: 220,
    drive: "AWD",
    steering: "left",
    mileage: 0,
    price: 3_890_000,
    status: "order",
    hit: true,
    badge: "Новинка",
    summary: "Новое поколение семейного кроссовера среднего размера: больше салон, полный привод 4Motion и R-Line в стандарте.",
    highlights: ["Полный привод 4Motion", "Панорамная крыша и массаж сидений", "Багажник 652 л"],
    images: [],
    eta: [35, 50],
  },
  {
    slug: "vw-tharu",
    brand: "Volkswagen",
    model: "Tharu",
    trim: "300TSI DSG Pro",
    year: 2024,
    country: "china",
    condition: "new",
    body: "crossover",
    engine: "petrol",
    engineLabel: "1.5 TSI, 7 DSG",
    power: 160,
    drive: "FWD",
    steering: "left",
    mileage: 0,
    price: 2_790_000,
    status: "order",
    hit: true,
    summary: "Городской кроссовер SAIC-Volkswagen на платформе MQB: немецкая ходовая, просторный салон и разумная цена владения.",
    highlights: ["Платформа MQB", "Клиренс 190 мм", "Камера 360° в комплектации Pro"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "vw-t-roc",
    brand: "Volkswagen",
    model: "T-Roc",
    trim: "300TSI DSG Star",
    year: 2024,
    country: "china",
    condition: "new",
    body: "crossover",
    engine: "petrol",
    engineLabel: "1.5 TSI, 7 DSG",
    power: 160,
    drive: "FWD",
    steering: "left",
    mileage: 0,
    price: 2_690_000,
    status: "order",
    hit: true,
    summary: "Компактный кроссовер с купеобразной крышей и двухцветным кузовом — стильный выбор для города.",
    highlights: ["Двухцветный кузов", "Матричная оптика в топе", "Динамика 0–100 за 8,9 с"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "audi-q3",
    brand: "Audi",
    model: "Q3",
    generation: "F3",
    trim: "35 TFSI Progressive",
    year: 2024,
    country: "china",
    condition: "new",
    body: "crossover",
    engine: "petrol",
    engineLabel: "1.4 TFSI, 6 DSG",
    power: 150,
    drive: "FWD",
    steering: "left",
    mileage: 0,
    price: 3_690_000,
    status: "order",
    hit: true,
    summary: "Премиальный компактный кроссовер с качественным салоном и экономичным турбомотором до 160 л.с.",
    highlights: ["Отделка салона уровня старших Audi", "MMI touch 10,1″", "Выгодная пошлина при мощности до 160 л.с."],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "bmw-3-series",
    brand: "BMW",
    model: "3 Series",
    generation: "G28",
    trim: "325Li M Sport",
    year: 2024,
    country: "china",
    condition: "new",
    body: "sedan",
    engine: "petrol",
    engineLabel: "2.0 B48, 8 АКПП",
    power: 184,
    drive: "RWD",
    steering: "left",
    mileage: 0,
    price: 4_290_000,
    status: "order",
    hit: true,
    badge: "Премиум",
    summary: "Удлинённая «трёшка» BMW Brilliance с пакетом M Sport: задний привод, 8-ступенчатый автомат и просторный задний ряд.",
    highlights: ["База +110 мм к европейской версии", "M Sport пакет и спорт-сиденья", "Live Cockpit Professional"],
    images: [],
    eta: [35, 50],
  },
  {
    slug: "byd-seagull",
    brand: "BYD",
    model: "Seagull",
    trim: "Flying 405 km",
    year: 2025,
    country: "china",
    condition: "new",
    body: "hatchback",
    engine: "ev",
    engineLabel: "Электро, 55 кВт",
    power: 75,
    drive: "FWD",
    steering: "left",
    mileage: 0,
    price: 1_690_000,
    status: "order",
    hit: true,
    badge: "Выгодно",
    summary: "Самый доступный электромобиль BYD: городской хэтчбек с LFP-батареей Blade и поворотным экраном.",
    highlights: ["Батарея Blade LFP — без деградации на холоде", "Запас хода до 405 км", "Стоимость 100 км — около 80 ₽"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "byd-dolphin",
    brand: "BYD",
    model: "Dolphin",
    trim: "Knight 420 km",
    year: 2025,
    country: "china",
    condition: "new",
    body: "hatchback",
    engine: "ev",
    engineLabel: "Электро, 130 кВт",
    power: 177,
    drive: "FWD",
    steering: "left",
    mileage: 0,
    price: 2_090_000,
    status: "order",
    hit: true,
    summary: "Практичный электрохэтчбек на платформе e-Platform 3.0 с тепловым насосом и ровным полом в салоне.",
    highlights: ["Тепловой насос в стандарте", "Колёсная база 2,7 м", "V2L — питание приборов от машины"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "kia-sportage",
    brand: "Kia",
    model: "Sportage",
    generation: "NQ5",
    trim: "2.0 Prestige",
    year: 2023,
    country: "korea",
    condition: "used",
    body: "crossover",
    engine: "petrol",
    engineLabel: "2.0 MPI, 6 АКПП",
    power: 150,
    drive: "AWD",
    steering: "left",
    mileage: 28_000,
    price: 2_950_000,
    status: "order",
    hit: true,
    badge: "Хит продаж",
    summary: "Корейский Sportage нового поколения с прозрачной историей обслуживания и проверкой по базе Encar.",
    highlights: ["Отчёт Encar и страховая история", "Полный привод", "Изогнутый двойной экран 12,3″"],
    images: [],
    eta: [25, 40],
  },
  {
    slug: "honda-freed",
    brand: "Honda",
    model: "Freed",
    generation: "GT",
    trim: "e:HEV Air EX",
    year: 2024,
    country: "japan",
    condition: "used",
    body: "minivan",
    engine: "hybrid",
    engineLabel: "1.5 e:HEV",
    power: 123,
    drive: "FWD",
    steering: "right",
    mileage: 9_000,
    price: 2_390_000,
    status: "auction",
    hit: true,
    summary: "Компактный 6-местный минивэн с раздвижными дверями и гибридом e:HEV — идеален для семьи и города.",
    highlights: ["Две сдвижные двери", "Расход около 4,5 л/100 км", "Оценка аукциона 4.5 и выше"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "honda-stepwgn",
    brand: "Honda",
    model: "Step WGN",
    generation: "RP",
    trim: "e:HEV Spada",
    year: 2023,
    country: "japan",
    condition: "used",
    body: "minivan",
    engine: "hybrid",
    engineLabel: "2.0 e:HEV",
    power: 184,
    drive: "FWD",
    steering: "right",
    mileage: 21_000,
    price: 3_150_000,
    status: "auction",
    hit: true,
    summary: "Большой семейный минивэн на 7–8 мест с плоским полом, утапливаемым третьим рядом и гибридом e:HEV.",
    highlights: ["Третий ряд убирается в пол", "Электросдвижные двери", "Honda Sensing в стандарте"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "mazda-cx-3",
    brand: "Mazda",
    model: "CX-3",
    trim: "1.5 XD Touring",
    year: 2021,
    country: "japan",
    condition: "used",
    body: "crossover",
    engine: "petrol",
    engineLabel: "2.0 Skyactiv-G, 6 АКПП",
    power: 150,
    drive: "AWD",
    steering: "right",
    mileage: 38_000,
    price: 1_890_000,
    status: "auction",
    hit: true,
    summary: "Компактный кроссовер с выразительным дизайном Kodo, точным рулём и полным приводом i-Activ AWD.",
    highlights: ["Полный привод i-Activ AWD", "Проекция на лобовое стекло", "Надёжный атмосферный мотор"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "honda-n-box",
    brand: "Honda",
    model: "N-BOX",
    generation: "JF5",
    trim: "Custom Turbo",
    year: 2024,
    country: "japan",
    condition: "used",
    body: "kei",
    engine: "petrol",
    engineLabel: "0.66 турбо, вариатор",
    power: 64,
    drive: "FWD",
    steering: "right",
    mileage: 7_000,
    price: 1_290_000,
    status: "auction",
    hit: true,
    badge: "Выгодно",
    summary: "Самый продаваемый автомобиль Японии: кей-кар с огромным салоном, сдвижной дверью и расходом 5 л.",
    highlights: ["Высота салона 1,4 м", "Минимальные налоги и пошлины", "Электросдвижная дверь"],
    images: [],
    eta: [30, 45],
  },
  {
    slug: "suzuki-jimny",
    brand: "Suzuki",
    model: "Jimny",
    generation: "JB64",
    trim: "XC",
    year: 2023,
    country: "japan",
    condition: "used",
    body: "suv",
    engine: "petrol",
    engineLabel: "0.66 турбо, 4 АКПП",
    power: 64,
    drive: "AWD",
    steering: "right",
    mileage: 12_000,
    price: 2_690_000,
    status: "auction",
    hit: true,
    badge: "Культовый",
    summary: "Настоящий рамный внедорожник с понижающей передачей и неразрезными мостами в компактном кузове.",
    highlights: ["Рама и неразрезные мосты", "Понижающая передача", "Держит цену на вторичке"],
    images: [],
    eta: [30, 45],
  },
  // Дополнительные позиции с иллюстрациями модели
  {
    slug: "zeekr-001",
    brand: "Zeekr",
    model: "001",
    trim: "YOU 100 kWh AWD",
    year: 2024,
    country: "china",
    condition: "used",
    body: "hatchback",
    engine: "ev",
    engineLabel: "Электро, 2 мотора",
    power: 544,
    drive: "AWD",
    steering: "left",
    mileage: 5_000,
    price: 4_850_000,
    status: "order",
    badge: "Премиум",
    summary: "Флагманский электрический шутинг-брейк с пневмоподвеской и разгоном до сотни за 3,8 с.",
    highlights: ["544 л.с. и полный привод", "Пневмоподвеска", "Запас хода до 650 км"],
    images: [
      { src: "/media/generated/zeekr-001-silver.webp", alt: "Серебристый Zeekr 001 в движении по городской улице", kind: "model" },
      { src: "/media/generated/zeekr-001-detail.webp", alt: "Деталь кузова Zeekr 001", kind: "model" },
    ],
    eta: [35, 50],
  },
  {
    slug: "hyundai-palisade",
    brand: "Hyundai",
    model: "Palisade",
    trim: "3.8 Calligraphy",
    year: 2022,
    country: "korea",
    condition: "used",
    body: "suv",
    engine: "petrol",
    engineLabel: "3.8 V6, 8 АКПП",
    power: 295,
    drive: "AWD",
    steering: "left",
    mileage: 41_000,
    price: 4_190_000,
    status: "order",
    summary: "Большой 8-местный кроссовер в топовой комплектации Calligraphy с полной историей по Encar.",
    highlights: ["Три ряда сидений", "Вентиляция всех кресел", "Отчёт Encar без ДТП"],
    images: [
      { src: "/media/generated/palisade-side.webp", alt: "Чёрный Hyundai Palisade в профиль", kind: "model" },
      { src: "/media/generated/palisade-rear.webp", alt: "Задние фонари Hyundai Palisade", kind: "model" },
    ],
    eta: [25, 40],
  },
  {
    slug: "geely-monjaro",
    brand: "Geely",
    model: "Monjaro",
    trim: "2.0T 4WD Flagship",
    year: 2025,
    country: "china",
    condition: "new",
    body: "crossover",
    engine: "petrol",
    engineLabel: "2.0T, 8 АКПП",
    power: 238,
    drive: "AWD",
    steering: "left",
    mileage: 0,
    price: 3_590_000,
    status: "order",
    summary: "Флагманский кроссовер Geely на платформе CMA с полным приводом и роскошным салоном.",
    highlights: ["Платформа CMA (Volvo)", "Три экрана в салоне", "Полный привод BorgWarner"],
    images: [
      { src: "/media/generated/geely-monjaro-red.webp", alt: "Красный Geely Monjaro", kind: "model" },
      { src: "/media/generated/monjaro-front.webp", alt: "Передняя часть Geely Monjaro", kind: "model" },
    ],
    eta: [30, 45],
  },
  {
    slug: "toyota-alphard",
    brand: "Toyota",
    model: "Alphard",
    generation: "AH40",
    trim: "2.5 HEV Executive Lounge",
    year: 2024,
    country: "japan",
    condition: "used",
    body: "minivan",
    engine: "hybrid",
    engineLabel: "2.5 гибрид, E-Four",
    power: 250,
    drive: "AWD",
    steering: "right",
    mileage: 11_000,
    price: 9_400_000,
    status: "auction",
    badge: "Премиум",
    summary: "Бизнес-минивэн нового поколения с креслами Executive Lounge — частный самолёт на колёсах.",
    highlights: ["Кресла с массажем и оттоманками", "Гибрид E-Four", "Оценка аукциона 5"],
    images: [
      { src: "/media/generated/alphard-exterior.webp", alt: "Toyota Alphard снаружи", kind: "model" },
      { src: "/media/generated/alphard-seat.webp", alt: "Кресло Executive Lounge в Toyota Alphard", kind: "model" },
    ],
    eta: [30, 45],
  },
];

/**
 * Витрина из админки (vanauto-api). Файл content/showcase.json пишет scripts/fetch-showcase.mjs перед сборкой.
 * Пусто → демо-каталог.
 */
const liveCars = (showcase as unknown as { cars?: Car[] }).cars ?? [];
export const isLiveCatalog = liveCars.length > 0;
export const cars: Car[] = isLiveCatalog ? liveCars : demoCars;

export function priceText(c: Pick<Car, "price" | "priceFrom">) {
  return `${c.priceFrom ? "от " : ""}${formatPrice(c.price)}`;
}

export function carName(c: Pick<Car, "brand" | "model">) {
  return `${c.brand} ${c.model}`;
}

export function formatPrice(n: number) {
  return new Intl.NumberFormat("ru-RU").format(n).replace(/ /g, " ") + " ₽";
}

export function formatKm(n: number) {
  return n === 0 ? "Новый" : new Intl.NumberFormat("ru-RU").format(n).replace(/ /g, " ") + " км";
}

/** Разбивка ориентира по статьям (ТЗ §6). Доли условные для демо. */
export function estimate(c: Car) {
  const share = c.country === "japan" ? [0.58, 0.07, 0.24, 0.05, 0.03, 0.03] : c.country === "korea" ? [0.6, 0.06, 0.24, 0.04, 0.03, 0.03] : [0.62, 0.06, 0.22, 0.04, 0.03, 0.03];
  const names = ["Автомобиль у продавца", "Экспорт, осмотр, фрахт", "Таможенные платежи и утильсбор", "Логистика до " + PRICE_CITY, "Брокер, СБКТС, ЭПТС", "Комиссия VAN2KAUTO"];
  const rows = names.map((name, i) => ({ name, value: Math.round((c.price * share[i]) / 1000) * 1000 }));
  const diff = c.price - rows.reduce((a, r) => a + r.value, 0);
  rows[0].value += diff;
  return rows;
}

const ALT_ANGLE = ["вид спереди три четверти", "вид сбоку"];
/** Фото карточки: свои для лота либо иллюстрации модели из /media/cars. */
export function photos(c: Car): CarImage[] {
  if (c.images.length) return c.images;
  return (carPhotoCount[c.slug] ? Array.from({ length: carPhotoCount[c.slug] }, (_, i) => i + 1) : [1]).map((n) =>
    img(c.slug, n, `${carName(c)} ${c.year}, ${ALT_ANGLE[n - 1] ?? "фото"} — иллюстрация модели`),
  );
}

/** Сколько фото модели лежит в /media/cars (заполняется по результатам подбора фото). */
export const carPhotoCount: Record<string, number> = {
  "audi-a3l": 2,
  "audi-q3": 2,
  "bmw-3-series": 2,
  "byd-dolphin": 2,
  "byd-seagull": 2,
  "changan-qiyuan-a06": 2,
  "changan-qiyuan-q05": 2,
  "honda-freed": 2,
  "honda-n-box": 2,
  "honda-stepwgn": 2,
  "kia-sportage": 2,
  "mazda-cx-3": 2,
  "suzuki-jimny": 2,
  "vw-golf-8": 2,
  "vw-t-roc": 2,
  "vw-tayron": 2,
  "vw-tharu": 2,
};
