import type { Metadata } from "next";
import { CartCheckout } from "@/components/sections/CartCheckout";
import { catalog } from "@/content/catalog";

export const metadata: Metadata = { title: "Корзина" };

export default function Page() {
  return (
    <CartCheckout title="Корзина" text="Проверьте заказ и оставьте контакты — менеджер подтвердит наличие, сроки и стоимость доставки." products={catalog.products} basePath="/catalog" currency={catalog.currency} note="Оплата после подтверждения заказа менеджером." />
  );
}
