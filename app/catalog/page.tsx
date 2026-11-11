import type { Metadata } from "next";
import { CatalogBrowser } from "@/components/sections/CatalogBrowser";
import { FaqSearch } from "@/components/sections/FaqSearch";
import { CtaSplitNote } from "@/components/sections/CtaSplitNote";
import { catalog } from "@/content/catalog";
import * as content from "@/content/pages/catalog";

export const metadata: Metadata = {
  "title": "Каталог",
  "description": "Полный каталог автомобилей из Китая, Кореи и Японии. Удобные фильтры по марке, году, цене и состоянию."
};

export default function Page() {
  return (
    <>
      <div id="products" className="scroll-mt-20">
        <CatalogBrowser {...content.products} products={catalog.products} currency={catalog.currency} />
      </div>
      <div id="faq" className="scroll-mt-20">
        <FaqSearch {...content.faq} />
      </div>
      <div id="cta" className="scroll-mt-20">
        <CtaSplitNote {...content.cta} />
      </div>
    </>
  );
}
