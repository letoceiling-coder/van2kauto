import type { Metadata } from "next";
import "../styles/fonts.css";
import "./globals.css";
import { NavbarScrollSolid } from "@/components/layout/NavbarScrollSolid";
import { FooterClosingPhoto } from "@/components/layout/FooterClosingPhoto";
import { footer, navbar } from "@/content/layout";
import { site } from "@/content/site";
import { PreviewBridge } from "@/components/platform/PreviewBridge";
import { CartButton } from "@/components/ui/Shop";

export const metadata: Metadata = {
  title: { default: site.name, template: `%s — ${site.name}` },
  description: site.description,
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang={site.language} className="h-full antialiased">
      <body className="flex min-h-full flex-col">
        <NavbarScrollSolid {...navbar} />
        <main className="flex-1">{children}</main>
        <FooterClosingPhoto {...footer} />
        <CartButton href="/cart" />
        <PreviewBridge />
      </body>
    </html>
  );
}
