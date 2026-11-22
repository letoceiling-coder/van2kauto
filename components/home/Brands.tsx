const BRANDS = ["BYD", "Changan", "Volkswagen", "Audi", "BMW", "Honda", "Kia", "Mazda", "Suzuki", "Zeekr", "Geely", "Toyota", "Hyundai", "Li Auto"];

export function Brands() {
  const row = [...BRANDS, ...BRANDS];
  return (
    <section id="brands" aria-label="Марки в работе" className="relative border-y border-white/[0.06] bg-white/[0.015] py-6">
      <div className="fade-x overflow-hidden">
        <div className="animate-marquee flex w-max items-center gap-12 pr-12 [--marquee-duration:40s]">
          {row.map((b, i) => (
            <span key={i} className="flex items-center gap-8 font-display text-[17px] font-semibold tracking-tight whitespace-nowrap text-foreground/40 sm:gap-12 sm:text-3xl">
              {b}
              <span className="size-1.5 rounded-full bg-accent/60" />
            </span>
          ))}
        </div>
      </div>
    </section>
  );
}
