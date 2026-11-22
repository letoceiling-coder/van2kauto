import type { MetadataRoute } from "next";

export const dynamic = "force-static";

/** Установка на экран как приложение (app-feel-web, правило 8). Цвета — из токенов. */
export default function manifest(): MetadataRoute.Manifest {
  return {
    name: "ВАН23АВТО — авто из Китая, Кореи и Японии",
    short_name: "ВАН23АВТО",
    description: "Подбор и привоз автомобилей под ключ в Анапу",
    start_url: "/",
    scope: "/",
    display: "standalone",
    orientation: "portrait",
    background_color: "#09090a",
    theme_color: "#09090a",
    lang: "ru",
    icons: [
      { src: "/brand/icon-192.png", sizes: "192x192", type: "image/png", purpose: "any" },
      { src: "/brand/icon-512.png", sizes: "512x512", type: "image/png", purpose: "any" },
      { src: "/brand/icon-maskable-512.png", sizes: "512x512", type: "image/png", purpose: "maskable" },
    ],
  };
}
