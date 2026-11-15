import type { Metadata } from "next";
import { CountryPage } from "@/components/site/CountryPage";
import { korea } from "@/content/countries";

export const metadata: Metadata = { title: korea.title, description: korea.text };

export default function Page() {
  return <CountryPage c={korea} />;
}
