export const nav = [
  { label: "Каталог", href: "/catalog" },
  { label: "Китай", href: "/cars-from-china" },
  { label: "Корея", href: "/cars-from-korea" },
  { label: "Япония", href: "/cars-from-japan" },
  { label: "Как заказать", href: "/how-it-works" },
  { label: "Контакты", href: "/contacts" },
];

import showcase from "./showcase.json";

/** Контакты и реквизиты из админки («Настройки → Компания и контакты»); пустые поля на сайте не выводятся. */
type CompanyApi = Partial<Record<"legal" | "inn" | "ogrn" | "address" | "hours" | "manager" | "phone" | "email" | "telegram" | "whatsapp", string>>;
const api: CompanyApi = ((showcase as unknown as { company?: CompanyApi | null }).company ?? {}) || {};
const pick = (v: string | undefined, fallback = "") => (v && v.trim() ? v.trim() : fallback);
const digits = (v: string) => v.replace(/\D/g, "").replace(/^8(?=\d{10}$)/, "7");

const phone = pick(api.phone);
const whatsapp = pick(api.whatsapp);
const telegram = pick(api.telegram).replace(/^@/, "");

export const company = {
  name: "ВАН23АВТО",
  legal: pick(api.legal, "One Auto / «Ван Авто»"),
  inn: pick(api.inn),
  ogrn: pick(api.ogrn),
  city: "Анапа",
  address: pick(api.address, "Анапа, Краснодарский край — офис и выдача"),
  hours: pick(api.hours, "Пн–Сб 9:00–19:00, Вс по записи"),
  manager: pick(api.manager, "Руслан"),
  phone,
  phoneHref: phone ? `tel:+${digits(phone)}` : "",
  email: pick(api.email),
  telegram: telegram ? `https://t.me/${telegram}` : "",
  telegramLabel: telegram ? `@${telegram}` : "",
  whatsapp: whatsapp ? `https://wa.me/${digits(whatsapp)}` : "",
  cities: ["Анапа", "Новороссийск", "Краснодар", "вся Россия"],
};

/** «ИНН … · ОГРН …» или пустая строка. */
export const requisites = [company.inn && `ИНН ${company.inn}`, company.ogrn && `${company.ogrn.length === 15 ? "ОГРНИП" : "ОГРН"} ${company.ogrn}`].filter(Boolean).join(" · ");
