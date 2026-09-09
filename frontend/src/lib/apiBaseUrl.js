// Laravel origin. NEXT_PUBLIC_API_URL is inlined at `next build` time and used
// for Next rewrites / server-side fetches. The browser always calls same-origin
// `/api` and `/sanctum`; next.config.mjs proxies those to this host.
export function laravelOrigin() {
  const configured = process.env.NEXT_PUBLIC_API_URL?.trim().replace(/\/$/, "");

  if (configured) {
    return configured;
  }

  if (process.env.NODE_ENV === "production") {
    throw new Error(
      "NEXT_PUBLIC_API_URL must be set for production builds. Copy frontend/.env.production.example to .env.production.",
    );
  }

  return "http://localhost:8000";
}

export const API_BASE_URL = laravelOrigin();

/** Empty in the browser so cookies stay first-party on the SPA host. */
export function apiRequestBase() {
  if (typeof window !== "undefined") {
    return "";
  }

  return API_BASE_URL;
}
