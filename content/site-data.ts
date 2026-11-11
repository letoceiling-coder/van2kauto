// Site content. The agent fills this file from site-plan.json; components only read from here.
export const site = {
  name: "Новый проект",
  description: "Сайт, созданный в Website AI Studio.",
  language: "ru",
};

export const navigation = [
  { label: "Главная", href: "/" },
  { label: "О нас", href: "/#about" },
  { label: "Контакты", href: "/#contact" },
];

export const home = {
  hero: {
    eyebrow: "Website AI Studio",
    title: "Здесь появится ваш сайт",
    text: "Опишите задачу в чате — агент соберёт страницы из проверенных блоков, подберёт стиль и наполнит контентом.",
    primaryCta: { label: "Связаться", href: "/#contact" },
  },
};
