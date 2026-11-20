import type { Metadata, Viewport } from "next";
import "../styles/fonts.css";
import "./globals.css";
import { site } from "@/content/site";
import { Header } from "@/components/site/Header";
import { ScrollProgress } from "@/components/motion/ScrollProgress";
import { Footer } from "@/components/site/Footer";
import { MobileDock } from "@/components/site/MobileDock";
import { Backdrop } from "@/components/site/Backdrop";
import { LeadProvider } from "@/components/site/Lead";
import { PreviewBridge } from "@/components/platform/PreviewBridge";
import { RevealScript } from "@/components/motion/RevealScript";

export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL ?? "https://vanauto.neeklo.ru"),
  title: { default: "ВАН23АВТО — авто из Китая, Кореи и Японии под заказ в Анапе", template: `%s — ${site.name}` },
  description: site.description,
  openGraph: { siteName: site.name, locale: "ru_RU", type: "website" },
  applicationName: site.name,
};

export const viewport: Viewport = {
  themeColor: "#09090a",
  colorScheme: "dark",
  viewportFit: "cover",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang={site.language} className="h-full antialiased" suppressHydrationWarning>
      <head>
        <link rel="preload" href="/fonts/commissioner-cyrillic-wght-normal.woff2" as="font" type="font/woff2" crossOrigin="anonymous" />
        <link rel="preload" href="/fonts/commissioner-latin-wght-normal.woff2" as="font" type="font/woff2" crossOrigin="anonymous" />
        <RevealScript />
      </head>
      <body className="flex min-h-full flex-col">
        <LeadProvider>
          <Backdrop />
          <ScrollProgress />
          <Header />
          <main className="flex-1">{children}</main>
          <Footer />
          <MobileDock />
        </LeadProvider>
        <PreviewBridge />
      </body>
    </html>
  );
}
