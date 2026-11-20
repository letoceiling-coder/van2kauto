import { Reveal } from "@/components/motion/Reveal";
import { Counter } from "@/components/motion/Counter";
import { pains } from "@/content/home";

export function Trust() {
  return (
    <section data-component-id="trust" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <div className="grid gap-10 lg:grid-cols-12">
        <Reveal className="lg:col-span-5">
          <p className="inline-flex items-center gap-2.5 text-[11px] font-semibold tracking-[0.22em] text-accent uppercase"><span className="h-px w-6 bg-accent" />Почему ВАН23АВТО</p>
          <h2 className="text-headline mt-4">Мы закрываем главные страхи покупки авто под заказ</h2>
          <p className="mt-4 max-w-md text-[17px] leading-relaxed text-muted-foreground">Неполная цена, сорванные сроки и тишина менеджера — частые жалобы рынка. Мы построили процесс так, чтобы их не было.</p>
          <div className="mt-10 grid grid-cols-2 gap-6 border-t border-white/[0.08] pt-8">
            <div>
              <p className="text-4xl font-semibold tracking-tight sm:text-5xl"><Counter to={15} /> <span className="text-xl text-muted-foreground">мин</span></p>
              <p className="mt-1 text-sm text-muted-foreground">среднее время первого ответа</p>
            </div>
            <div>
              <p className="text-4xl font-semibold tracking-tight sm:text-5xl"><Counter to={6} /> <span className="text-xl text-muted-foreground">статей</span></p>
              <p className="mt-1 text-sm text-muted-foreground">в каждой смете — без «прочего»</p>
            </div>
          </div>
        </Reveal>
        <div className="grid gap-4 sm:grid-cols-2 lg:col-span-7">
          {pains.map((p, i) => (
            <Reveal key={p.k} delay={i * 0.07}>
              <div className="group card relative h-full overflow-hidden p-6 transition hover:border-accent/30">
                <div aria-hidden="true" className="absolute -top-16 -right-16 size-40 rounded-full bg-accent/0 blur-3xl transition duration-500 group-hover:bg-accent/15" />
                <span className="font-display text-sm font-semibold text-accent">{p.k}</span>
                <h3 className="mt-6 text-xl font-semibold tracking-tight">{p.title}</h3>
                <p className="mt-2 text-[15px] leading-relaxed text-muted-foreground">{p.text}</p>
              </div>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
