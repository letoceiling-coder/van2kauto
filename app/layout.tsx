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

export const metadata: Metadata = {
  title: { default: "VAN2KAUTO — авто из Китая, Кореи и Японии под заказ в Анапе", template: `%s — ${site.name}` },
  description: site.description,
  openGraph: { siteName: site.name, locale: "ru_RU", type: "website" },
};

export const viewport: Viewport = {
  themeColor: "#0a0c0e",
  colorScheme: "dark",
  viewportFit: "cover",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang={site.language} className="h-full antialiased">
      <head>
        <link rel="preload" href="/fonts/commissioner-cyrillic-wght-normal.woff2" as="font" type="font/woff2" crossOrigin="anonymous" />
        <link rel="preload" href="/fonts/commissioner-latin-wght-normal.woff2" as="font" type="font/woff2" crossOrigin="anonymous" />
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
