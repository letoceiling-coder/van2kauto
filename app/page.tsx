import { HeroBasic } from "@/components/sections/HeroBasic";
import { home } from "@/content/site-data";

export default function HomePage() {
  return <HeroBasic {...home.hero} />;
}
