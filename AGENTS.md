<!-- BEGIN:nextjs-agent-rules -->

## This is NOT the Next.js you know

This version has breaking changes — APIs, conventions, and file structure may all differ from your training data. Read the relevant guide in `node_modules/next/dist/docs/` (resolved from this file's directory; in monorepos the `next` package may not be visible from the repo root) before writing any code. Heed deprecation notices.

This block is written and re-added by `next dev` — verify at `node_modules/next/dist/server/lib/generate-agent-files.js`. Removing it from a diff only re-creates the uncommitted change; committing it with your work keeps the tree clean.

<!-- END:nextjs-agent-rules -->

## Website AI Studio — starter rules

- Content lives in `content/site-data.ts`; sections read props from there. Do not hard-code copy in components.
- Colors, fonts, radius, spacing come only from `styles/tokens.css` (Tailwind: `bg-background`, `text-primary`, `font-heading`, `rounded-token`, `max-w-site`). No raw hex colors in components.
- Every section root has `data-component-id`, editable text has `data-editable-id`.
- Public assets: use `asset("/media/x.webp")` from `lib/utils` (basePath is set by the platform).
- Do not edit `package.json`, `pnpm-lock.yaml`, `next.config.ts`, `tsconfig.json` unless a tool explicitly allows it. Never disable lint/typecheck.
- Mobile first: check 390px, 768px, 1440px. No horizontal overflow. Respect `prefers-reduced-motion`.
