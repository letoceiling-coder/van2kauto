import type { NextConfig } from "next";

// Set by the platform: preview -> "/{projectId}", publish -> "/projects/{slug}".
// Must be known at build/dev start time (inlined into client bundles).
const basePath = process.env.PROJECT_BASE_PATH ?? "";
const staticExport = process.env.STATIC_EXPORT === "1";

const nextConfig: NextConfig = {
  basePath,
  ...(staticExport ? { output: "export" as const, trailingSlash: true } : {}),
  images: { unoptimized: true },
  env: { NEXT_PUBLIC_BASE_PATH: basePath },
  // Preview is served through preview.siteaccess.ru (gateway), allow its origin for dev resources/HMR.
  allowedDevOrigins: ["preview.siteaccess.ru"],
  // The preview is the client's site: no Next.js dev badge over it (errors are reported to the agent/QA instead).
  devIndicators: false,
  turbopack: {
    rules: {
      "*.css": {
        loaders: ["@tailwindcss/turbopack"],
        as: "*.css",
      },
    },
  },
};

export default nextConfig;
