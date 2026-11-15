import { Hero } from "@/components/home/Hero";
import { Brands } from "@/components/home/Brands";
import { Countries } from "@/components/home/Countries";
import { Hits } from "@/components/home/Hits";
import { PriceBreakdown } from "@/components/home/PriceBreakdown";
import { Process } from "@/components/home/Process";
import { QuizBand } from "@/components/home/QuizBand";
import { Cases } from "@/components/home/Cases";
import { Trust } from "@/components/home/Trust";
import { Faq } from "@/components/home/Faq";
import { Office } from "@/components/home/Office";
import { cars } from "@/content/cars";

export default function Page() {
  return (
    <>
      <Hero count={cars.length} />
      <Brands />
      <Hits />
      <Countries />
      <PriceBreakdown />
      <Trust />
      <Process />
      <QuizBand />
      <Cases />
      <Faq />
      <Office />
    </>
  );
}
