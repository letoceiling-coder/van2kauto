import type { Metadata } from "next";
import { HeroCinematic } from "@/components/sections/HeroCinematic";
import { FeaturesListPreview } from "@/components/sections/FeaturesListPreview";
import { ProductsShowcase } from "@/components/sections/ProductsShowcase";
import { ServicesCompareCards } from "@/components/sections/ServicesCompareCards";
import { ScrollStory } from "@/components/sections/ScrollStory";
import { PriceList } from "@/components/sections/PriceList";
import { PortfolioCursorList } from "@/components/sections/PortfolioCursorList";
import { BlogMagazine } from "@/components/sections/BlogMagazine";
import { FaqWithCta } from "@/components/sections/FaqWithCta";
import { ContactHoursMap } from "@/components/sections/ContactHoursMap";
import * as content from "@/content/pages/home";

export const metadata: Metadata = {
  "description": "Заказ автомобилей из Китая, Кореи и Японии с доставкой по всей России. Подбор, расчет стоимости и проверенные кейсы от VAN2KAUTO."
};

export default function Page() {
  return (
    <>
      <div id="hero" className="scroll-mt-20">
        <HeroCinematic {...content.hero} />
      </div>
      <div id="search" className="scroll-mt-20">
        <FeaturesListPreview {...content.search} />
      </div>
      <div id="products" className="scroll-mt-20">
        <ProductsShowcase {...content.products} />
      </div>
      <div id="countries" className="scroll-mt-20">
        <ServicesCompareCards {...content.countries} />
      </div>
      <div id="process" className="scroll-mt-20">
        <ScrollStory {...content.process} />
      </div>
      <div id="pricing" className="scroll-mt-20">
        <PriceList {...content.pricing} />
      </div>
      <div id="portfolio" className="scroll-mt-20">
        <PortfolioCursorList {...content.portfolio} />
      </div>
      <div id="blog" className="scroll-mt-20">
        <BlogMagazine {...content.blog} />
      </div>
      <div id="faq" className="scroll-mt-20">
        <FaqWithCta {...content.faq} />
      </div>
      <div id="contact" className="scroll-mt-20">
        <ContactHoursMap {...content.contact} />
      </div>
    </>
  );
}
