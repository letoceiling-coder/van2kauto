import Link from "next/link";
import { Logo } from "./Logo";
import { LeadButton } from "./Lead";
import { company, nav, requisites } from "@/content/nav";
import { cars } from "@/content/cars";

export function Footer() {
  const brands = Array.from(new Set(cars.map((c) => c.brand)));
  return (
    <footer className="relative mt-24 overflow-hidden border-t border-white/[0.06] pb-28 md:pb-10">
      <div aria-hidden="true" className="pointer-events-none absolute inset-x-0 -bottom-[0.18em] hidden text-center font-display md:block text-[15vw] leading-none font-bold italic tracking-tight text-outline select-none">
        ВАН23АВТО
      </div>
      <div className="relative mx-auto grid grid-cols-1 max-w-site gap-12 px-4 pt-16 sm:px-6 lg:grid-cols-12 lg:px-8">
        <div className="lg:col-span-4">
          <Logo tagline className="min-h-11" />
          <p className="mt-6 max-w-sm text-muted-foreground">
            Автомобили из Китая, Кореи и Японии под заказ. Офис и выдача в Анапе, доставка по Краснодарскому краю и всей России.
          </p>
          <LeadButton className="btn-accent shine mt-7 inline-flex h-12 items-center rounded-full px-6 text-sm font-semibold" title="Связаться с менеджером">
            Связаться с менеджером
          </LeadButton>
        </div>
        <div className="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-8">
          <div>
            <p className="text-xs tracking-[0.18em] text-muted-foreground uppercase">Разделы</p>
            <ul className="mt-4 space-y-0 sm:space-y-2.5">
              {nav.map((l) => (
                <li key={l.href}><Link className="inline-flex min-h-11 min-w-11 items-center text-foreground/80 transition hover:text-accent sm:min-h-0 sm:min-w-0" href={l.href}>{l.label}</Link></li>
              ))}
            </ul>
          </div>
          <div>
            <p className="text-xs tracking-[0.18em] text-muted-foreground uppercase">Марки</p>
            <ul className="mt-4 space-y-0 sm:space-y-2.5">
              {brands.slice(0, 9).map((b) => (
                <li key={b}><Link className="inline-flex min-h-11 min-w-11 items-center text-foreground/80 transition hover:text-accent sm:min-h-0 sm:min-w-0" href={`/catalog?q=${encodeURIComponent(b)}`}>{b}</Link></li>
              ))}
            </ul>
          </div>
          <div className="col-span-2 sm:col-span-1">
            <p className="text-xs tracking-[0.18em] text-muted-foreground uppercase">Офис</p>
            <p className="mt-4 text-foreground/80">{company.address}</p>
            <p className="mt-2 text-foreground/80">{company.hours}</p>
            <p className="mt-2 text-muted-foreground">Доставка: {company.cities.join(", ")}</p>
            {(company.phone || company.telegram || company.whatsapp || company.email) && (
              <ul className="mt-3 space-y-0">
                {company.phone && <li><a className="inline-flex min-h-11 items-center font-semibold text-foreground transition hover:text-accent" href={company.phoneHref}>{company.phone}</a></li>}
                {company.telegram && <li><a className="inline-flex min-h-11 items-center text-foreground/80 transition hover:text-accent" href={company.telegram} target="_blank" rel="noopener">Telegram {company.telegramLabel}</a></li>}
                {company.whatsapp && <li><a className="inline-flex min-h-11 items-center text-foreground/80 transition hover:text-accent" href={company.whatsapp} target="_blank" rel="noopener">WhatsApp</a></li>}
                {company.email && <li><a className="inline-flex min-h-11 items-center text-foreground/80 transition hover:text-accent" href={`mailto:${company.email}`}>{company.email}</a></li>}
              </ul>
            )}
          </div>
        </div>
        <div className="flex flex-col gap-3 border-t border-white/[0.06] pt-6 text-sm text-muted-foreground sm:flex-row sm:justify-between lg:col-span-12">
          <p>© 2026 ВАН23АВТО · {company.legal}{requisites && ` · ${requisites}`}</p>
          <p>Цены — предварительный ориентир, не публичная оферта. <Link href="/photo-credits" className="underline-offset-4 hover:text-accent hover:underline">Авторы фото</Link></p>
        </div>
      </div>
    </footer>
  );
}
