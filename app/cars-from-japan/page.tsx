import type { Metadata } from "next";
import { CountryPage } from "@/components/site/CountryPage";
import { japan } from "@/content/countries";

export const metadata: Metadata = { title: japan.title, description: japan.text };

export default function Page() {
  return <CountryPage c={japan} />;
}
