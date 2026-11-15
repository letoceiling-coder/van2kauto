import { CatalogView } from "@/components/car/CatalogView";
import { PageHero } from "@/components/site/PageHero";
import { Process } from "@/components/home/Process";
import { Faq } from "@/components/home/Faq";
import { Reveal } from "@/components/motion/Reveal";
import { LeadButton } from "@/components/site/Lead";
import { countries, type Country } from "@/content/cars";

export interface CountryContent {
  country: Country;
  title: string;
  text: string;
  image: string;
  watermark: string;
  facts: { v: string; l: string }[];
  points: { title: string; text: string }[];
}

export function CountryPage({ c }: { c: CountryContent }) {
  return (
    <>
      <PageHero eyebrow={`Направление · ${countries[c.country].label}`} title={c.title} text={c.text} image={c.image} watermark={c.watermark}>
        <div className="flex flex-wrap gap-3">
          <LeadButton title={`Подбор авто ${countries[c.country].from}`} className="btn-accent shine h-13 rounded-full px-7 font-semibold">Подобрать {countries[c.country].from}</LeadButton>
          <a href="#catalog" className="inline-flex h-13 items-center rounded-full border border-white/15 px-7 font-semibold transition hover:border-white/40">Смотреть модели</a>
        </div>
      </PageHero>
      <section className="mx-auto max-w-site px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
          {c.facts.map((f, i) => (
            <Reveal key={f.l} delay={i * 0.06} className="card p-5 sm:p-6">
              <p className="tnum text-3xl font-semibold tracking-tight sm:text-4xl">{f.v}</p>
              <p className="mt-1 text-sm text-muted-foreground">{f.l}</p>
            </Reveal>
          ))}
        </div>
        <div className="mt-4 grid gap-3 md:grid-cols-3">
          {c.points.map((p, i) => (
            <Reveal key={p.title} delay={i * 0.06} className="card h-full p-6">
              <span className="font-display text-sm font-semibold text-accent">0{i + 1}</span>
              <h2 className="mt-4 text-xl font-semibold tracking-tight">{p.title}</h2>
              <p className="mt-2 text-[15px] leading-relaxed text-muted-foreground">{p.text}</p>
            </Reveal>
          ))}
        </div>
      </section>
      <section id="catalog" className="mx-auto max-w-site scroll-mt-24 px-4 pt-[var(--section-y)] sm:px-6 lg:px-8">
        <h2 className="text-headline mb-8">Модели {countries[c.country].from}</h2>
        <CatalogView initialCountry={c.country} lockCountry />
      </section>
      <Process />
      <Faq />
    </>
  );
}
