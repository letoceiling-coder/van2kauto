import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { ProductDetail } from "@/components/sections/ProductDetail";
import { catalog } from "@/content/catalog";

export const dynamicParams = false;

export function generateStaticParams() {
  return catalog.products.map((p) => ({ slug: p.slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params;
  const p = catalog.products.find((x) => x.slug === slug);
  return p ? { title: p.name, description: p.summary } : {};
}

export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const product = catalog.products.find((p) => p.slug === slug);
  if (!product) notFound();
  const others = catalog.products.filter((p) => p.slug !== slug);
  const related = [...others.filter((p) => p.category === product.category), ...others.filter((p) => p.category !== product.category)].slice(0, 4);
  return (
    <ProductDetail product={product} related={related} basePath="/catalog" cartHref="/cart" currency={catalog.currency} note={catalog.note} contact={{ label: "Задать вопрос менеджеру", href: "/#contact" }} />
  );
}
