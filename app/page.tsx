import { Hero } from "@/components/home/Hero";
import { Brands } from "@/components/home/Brands";
import { WhyUs } from "@/components/home/WhyUs";
import { TopTen } from "@/components/home/TopTen";
import { HowWeWork } from "@/components/home/HowWeWork";
import { Countries } from "@/components/home/Countries";
import { QuickActions } from "@/components/home/QuickActions";
import { Faq } from "@/components/home/Faq";
import { AskManager } from "@/components/home/AskManager";
import { FinalCta } from "@/components/home/FinalCta";
import { cars } from "@/content/cars";

/** Структура по референсу: первый экран → быстрые действия (телефон) → почему мы → топ-10 → как работаем → страны → вопросы → чат с менеджером → финальный экран.
 *  «Выдачи» убраны с главной: там демо-истории, а выдавать выдумку за реальные сделки нельзя. */
export default function Page() {
  return (
    <>
      <Hero count={cars.length} />
      <QuickActions />
      <Brands />
      <WhyUs />
      <TopTen />
      <HowWeWork />
      <Countries />
      <Faq />
      <AskManager />
      <FinalCta />
    </>
  );
}
