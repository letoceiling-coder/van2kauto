import type { Metadata } from "next";
import { FeaturesTabsMedia } from "@/components/sections/FeaturesTabsMedia";
import { CtaAurora } from "@/components/sections/CtaAurora";
import { ContactSplit } from "@/components/sections/ContactSplit";
import * as content from "@/content/pages/selection";

export const metadata: Metadata = {
  "title": "Подбор авто",
  "description": "Заполните квиз, чтобы получить персональную подборку автомобилей под ваш бюджет и требования."
};

export default function Page() {
  return (
    <>
      <div id="features" className="scroll-mt-20">
        <FeaturesTabsMedia {...content.features} />
      </div>
      <div id="cta" className="scroll-mt-20">
        <CtaAurora {...content.cta} />
      </div>
      <div className="scroll-mt-20">
        <ContactSplit {...content.contact} />
      </div>
    </>
  );
}
