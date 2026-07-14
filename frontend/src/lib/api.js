// Backend address, shared across the app.
// Uses NEXT_PUBLIC_API_URL in production; falls back to the local backend.
export const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
