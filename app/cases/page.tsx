import type { Metadata } from "next";
import { PortfolioHorizontal } from "@/components/sections/PortfolioHorizontal";
import { CtaPhotoBand } from "@/components/sections/CtaPhotoBand";
import { BlogNewsFeed } from "@/components/sections/BlogNewsFeed";
import * as content from "@/content/pages/cases";

export const metadata: Metadata = {
  "title": "Выдачи",
  "description": "Посмотрите, какие автомобили мы уже доставили нашим клиентам. Реальные истории и фото."
};

export default function Page() {
  return (
    <>
      <div id="portfolio" className="scroll-mt-20">
        <PortfolioHorizontal {...content.portfolio} />
      </div>
      <div id="cta" className="scroll-mt-20">
        <CtaPhotoBand {...content.cta} />
      </div>
      <div id="blog" className="scroll-mt-20">
        <BlogNewsFeed {...content.blog} />
      </div>
    </>
  );
}
