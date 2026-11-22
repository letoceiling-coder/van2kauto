import { Hero } from "@/components/home/Hero";
import { Brands } from "@/components/home/Brands";
import { WhyUs } from "@/components/home/WhyUs";
import { TopTen } from "@/components/home/TopTen";
import { HowWeWork } from "@/components/home/HowWeWork";
import { Countries } from "@/components/home/Countries";
import { Cases } from "@/components/home/Cases";
import { Faq } from "@/components/home/Faq";
import { AskManager } from "@/components/home/AskManager";
import { FinalCta } from "@/components/home/FinalCta";
import { cars } from "@/content/cars";

/** Структура по референсу: первый экран → почему мы → топ-10 → как работаем → страны → выдачи → вопросы → чат с менеджером → финальный экран. */
export default function Page() {
  return (
    <>
      <Hero count={cars.length} />
      <Brands />
      <WhyUs />
      <TopTen />
      <HowWeWork />
      <Countries />
      <Cases />
      <Faq />
      <AskManager />
      <FinalCta />
    </>
  );
}
