import Script from "next/script";

/**
 * Единственный ранний скрипт: каталог с ?q= / ?country= прячет сетку на кадр, пока CatalogView не применит фильтр
 * (иначе мигает полный список). Появление при прокрутке — чистый CSS (animation-timeline: view()), без JS.
 */
const code = `try{var p=new URLSearchParams(location.search);if(location.pathname.indexOf('/catalog')>-1&&(p.get('q')||p.get('country')))document.documentElement.classList.add('catalog-pending');setTimeout(function(){document.documentElement.classList.remove('catalog-pending')},1500)}catch(e){}`;

export function RevealScript() {
  // App Router: beforeInteractive в корневом layout — штатный способ (правило ESLint рассчитано на pages/).
  // eslint-disable-next-line @next/next/no-before-interactive-script-outside-document
  return <Script id="early-ui" strategy="beforeInteractive">{code}</Script>;
}
