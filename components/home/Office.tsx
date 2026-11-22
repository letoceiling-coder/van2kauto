import { MapPin, Clock, Truck } from "lucide-react";
import { Reveal } from "@/components/motion/Reveal";
import { LeadForm } from "@/components/site/Lead";
import { company } from "@/content/nav";

/** Блок офиса: стилизованная карта побережья + форма заявки. */
export function Office() {
  return (
    <section data-component-id="office" className="relative mx-auto max-w-site px-4 py-[var(--section-y)] sm:px-6 lg:px-8">
      <div className="card relative grid grid-cols-1 overflow-hidden lg:grid-cols-2">
        <div className="relative min-h-[360px] overflow-hidden border-b border-white/[0.06] lg:border-r lg:border-b-0">
          <div className="dots absolute inset-0 opacity-60" />
          <svg aria-hidden="true" viewBox="0 0 500 420" className="absolute inset-0 size-full" preserveAspectRatio="xMidYMid slice" fill="none">
            <path d="M-20 300 C 60 280, 90 250, 140 255 S 230 300, 270 270 S 330 180, 390 170 S 480 120, 540 100" stroke="#eb111d" strokeOpacity="0.35" strokeWidth="1.5" />
            <path d="M-20 300 C 60 280, 90 250, 140 255 S 230 300, 270 270 S 330 180, 390 170 S 480 120, 540 100 L 540 440 L -20 440 Z" fill="#3b6cff" fillOpacity="0.05" />
            <path d="M150 230 C 220 200, 300 150, 420 110" stroke="#eef0ea" strokeOpacity="0.25" strokeDasharray="3 6" className="anim-dash" />
            <path d="M150 230 C 200 170, 260 110, 330 60" stroke="#eef0ea" strokeOpacity="0.2" strokeDasharray="3 6" className="anim-dash" />
            {[
              [150, 230, "Анапа"],
              [272, 255, "Новороссийск"],
              [330, 60, "Краснодар"],
            ].map(([x, y, n], i) => (
              <g key={String(n)}>
                <circle cx={Number(x)} cy={Number(y)} r={i === 0 ? 7 : 4} fill={i === 0 ? "#eb111d" : "#eef0ea"} />
                {i === 0 && <circle cx={Number(x)} cy={Number(y)} r="18" stroke="#eb111d" strokeOpacity="0.4" />}
                <text x={Number(x) + 14} y={Number(y) + 5} fill="#eef0ea" fillOpacity={i === 0 ? 1 : 0.6} fontSize={i === 0 ? 18 : 13} fontWeight={i === 0 ? 600 : 400}>{String(n)}</text>
              </g>
            ))}
            <text x="40" y="380" fill="#eef0ea" fillOpacity="0.25" fontSize="12" letterSpacing="3">ЧЁРНОЕ МОРЕ</text>
          </svg>
          <div className="relative flex h-full flex-col justify-end p-6 sm:p-8">
            <div className="grid gap-3 rounded-2xl border border-white/10 bg-background/70 p-5 sm:max-w-sm">
              <p className="flex items-start gap-3"><MapPin className="mt-0.5 size-4.5 shrink-0 text-accent" />{company.address}</p>
              <p className="flex items-start gap-3"><Clock className="mt-0.5 size-4.5 shrink-0 text-accent" />{company.hours}</p>
              <p className="flex items-start gap-3"><Truck className="mt-0.5 size-4.5 shrink-0 text-accent" />Передача в Анапе, доставка в Новороссийск, Краснодар и по России</p>
            </div>
          </div>
        </div>
        <Reveal className="p-6 sm:p-8 lg:p-12">
          <p className="inline-flex items-center gap-2.5 text-[11px] font-semibold tracking-[0.22em] text-accent uppercase"><span className="h-px w-6 bg-accent" />Офис в Анапе</p>
          <h2 className="text-headline mt-4">Приезжайте на кофе или оставьте заявку</h2>
          <p className="mt-3 mb-8 text-muted-foreground">Покажем примеры смет и отчётов осмотра, обсудим модель и бюджет. Ответим в рабочее время.</p>
          <LeadForm />
        </Reveal>
      </div>
    </section>
  );
}
