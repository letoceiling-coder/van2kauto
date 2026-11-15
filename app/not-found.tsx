import Link from "next/link";

export default function NotFound() {
  return (
    <section className="mx-auto grid min-h-[70svh] max-w-site place-items-center px-4 pt-28 text-center">
      <div>
        <p className="font-display text-[clamp(6rem,24vw,14rem)] leading-none font-bold text-outline">404</p>
        <h1 className="-mt-6 text-3xl font-semibold tracking-tight sm:text-4xl">Такой страницы нет</h1>
        <p className="mx-auto mt-3 max-w-md text-muted-foreground">Возможно, лот уже продан или ссылка устарела. Посмотрите похожие варианты в каталоге.</p>
        <Link href="/catalog" className="btn-accent mt-8 inline-flex h-13 items-center rounded-full px-7 font-semibold">Перейти в каталог</Link>
      </div>
    </section>
  );
}
