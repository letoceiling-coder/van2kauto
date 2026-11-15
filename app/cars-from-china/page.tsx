import type { Metadata } from "next";
import { CountryPage } from "@/components/site/CountryPage";
import { china } from "@/content/countries";

export const metadata: Metadata = { title: china.title, description: china.text };

export default function Page() {
  return <CountryPage c={china} />;
}
