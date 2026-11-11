"use client";

// Bridge between the Studio (lovable.siteaccess.ru) and this site running inside the preview iframe.
// Dev only (not part of production/static builds). Talks via postMessage to the Studio origin only.
//   Studio -> site: { type: "lv:mode", mode: "none" | "select" | "text" }
//   site -> Studio: { type: "lv:ready" | "lv:location" | "lv:selected" | "lv:text-edit", ... }
import { useEffect } from "react";
import { usePathname } from "next/navigation";

const STUDIO_ORIGINS = (process.env.NEXT_PUBLIC_STUDIO_ORIGIN ?? "https://lovable.siteaccess.ru").split(",");
const TEXT_TAGS = new Set(["H1", "H2", "H3", "H4", "P", "SPAN", "A", "BUTTON", "LI", "DD", "DT", "BLOCKQUOTE", "FIGCAPTION", "LABEL", "STRONG", "EM"]);
const BASE = process.env.NEXT_PUBLIC_BASE_PATH ?? "";

function post(msg: Record<string, unknown>) {
  for (const o of STUDIO_ORIGINS) window.parent.postMessage(msg, o);
}

function describe(el: Element) {
  const section = el.closest("[data-component-id]");
  const editable = el.closest("[data-editable-id]");
  return {
    componentId: section?.getAttribute("data-component-id") ?? null,
    editableId: editable?.getAttribute("data-editable-id") ?? null,
    tag: el.tagName.toLowerCase(),
    text: (el.textContent ?? "").replace(/\s+/g, " ").trim().slice(0, 200),
    path: location.pathname.slice(BASE.length) || "/",
  };
}

export function PreviewBridge() {
  const pathname = usePathname();
  useEffect(() => {
    if (process.env.NODE_ENV !== "production" && window.parent !== window) post({ type: "lv:location", path: pathname || "/" });
  }, [pathname]);
  useEffect(() => {
    if (process.env.NODE_ENV === "production" || window.parent === window) return;
    let mode: "none" | "select" | "text" = "none";
    let hovered: HTMLElement | null = null;
    let editing: { el: HTMLElement; before: string } | null = null;
    const style = document.createElement("style");
    style.textContent = `.lv-hover{outline:2px solid #7c6cf6!important;outline-offset:2px;cursor:pointer}.lv-editing{outline:2px dashed #7c6cf6!important;outline-offset:3px;cursor:text}`;
    document.head.appendChild(style);

    const target = (e: Event) => {
      const el = e.target as HTMLElement | null;
      if (!el) return null;
      if (mode === "select") return (el.closest("[data-editable-id]") ?? el.closest("h1,h2,h3,h4,p,a,button,img,li") ?? el.closest("[data-component-id]")) as HTMLElement | null;
      if (mode === "text") { let t: HTMLElement | null = el; while (t && !TEXT_TAGS.has(t.tagName)) t = t.parentElement; return t; }
      return null;
    };
    const clearHover = () => { hovered?.classList.remove("lv-hover"); hovered = null; };
    const onOver = (e: MouseEvent) => {
      if (mode === "none" || editing) return;
      const t = target(e);
      if (t === hovered) return;
      clearHover();
      if (t) { hovered = t; t.classList.add("lv-hover"); }
    };
    const finishEdit = (commit: boolean) => {
      if (!editing) return;
      const { el, before } = editing;
      el.contentEditable = "false";
      el.classList.remove("lv-editing");
      const after = (el.textContent ?? "").replace(/\s+/g, " ").trim();
      editing = null;
      if (!commit) { el.textContent = before; return; }
      if (after && after !== before) post({ type: "lv:text-edit", oldText: before, newText: after, ...describe(el) });
    };
    const onClick = (e: MouseEvent) => {
      if (mode === "none") return;
      const t = target(e);
      if (!t) return;
      e.preventDefault();
      e.stopPropagation();
      if (mode === "select") {
        post({ type: "lv:selected", ...describe(t) });
      } else if (mode === "text" && !editing) {
        clearHover();
        editing = { el: t, before: (t.textContent ?? "").replace(/\s+/g, " ").trim() };
        t.contentEditable = "true";
        t.classList.add("lv-editing");
        t.focus();
      }
    };
    const onKey = (e: KeyboardEvent) => {
      if (!editing) return;
      if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); finishEdit(true); }
      if (e.key === "Escape") { e.preventDefault(); finishEdit(false); }
    };
    const onBlur = () => finishEdit(true);
    const onMessage = (e: MessageEvent) => {
      if (!STUDIO_ORIGINS.includes(e.origin) || e.data?.type !== "lv:mode") return;
      mode = e.data.mode;
      if (mode !== "text") finishEdit(true);
      clearHover();
    };

    document.addEventListener("mouseover", onOver, true);
    document.addEventListener("click", onClick, true);
    document.addEventListener("keydown", onKey, true);
    document.addEventListener("focusout", onBlur, true);
    window.addEventListener("message", onMessage);
    post({ type: "lv:ready", path: location.pathname.slice(BASE.length) || "/" });
    return () => {
      document.removeEventListener("mouseover", onOver, true);
      document.removeEventListener("click", onClick, true);
      document.removeEventListener("keydown", onKey, true);
      document.removeEventListener("focusout", onBlur, true);
      window.removeEventListener("message", onMessage);
      style.remove();
    };
  }, []);
  return null;
}
