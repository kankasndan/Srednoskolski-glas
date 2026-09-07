// Public Laravel origin. NEXT_PUBLIC_API_URL is inlined at `next build` time.
export function apiBaseUrl() {
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

export const API_BASE_URL = apiBaseUrl();
