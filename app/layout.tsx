import type { Metadata } from "next";
// fonts.css is imported by Next directly (not through Tailwind) so relative font URLs resolve correctly.
import "../styles/fonts.css";
import "./globals.css";
import { Header } from "@/components/layout/Header";
import { Footer } from "@/components/layout/Footer";
import { site } from "@/content/site-data";
import { PreviewBridge } from "@/components/platform/PreviewBridge";

export const metadata: Metadata = {
  title: site.name,
  description: site.description,
};

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang={site.language} className="h-full antialiased">
      <body className="flex min-h-full flex-col">
        <Header />
        <main className="flex-1">{children}</main>
        <Footer />
        <PreviewBridge />
      </body>
    </html>
  );
}
