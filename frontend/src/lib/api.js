// Backend address, shared across the app.
// Uses NEXT_PUBLIC_API_URL in production; falls back to the local backend.
export const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

const SAFE_METHODS = new Set(["GET", "HEAD", "OPTIONS"]);

/**
 * Text from the server is only shown for 4xx responses, which carry messages
 * written for the user. A 5xx body can contain internal detail (SQL, file
 * paths) whenever debug mode is left on, so those get the local fallback.
 */
export function userFacingError(error, fallback) {
  const status = error?.status ?? 0;
  const message = typeof error?.message === "string" ? error.message.trim() : "";

  if (message !== "" && status >= 400 && status < 500) return message;

  return fallback;
}

function readCookie(name) {
  if (typeof document === "undefined") return null;
  const match = document.cookie.match(
    new RegExp(`(?:^|; )${name}=([^;]*)`),
  );
  return match ? decodeURIComponent(match[1]) : null;
}

// Ask Laravel to set the XSRF-TOKEN cookie. Sanctum then validates it against
// the X-XSRF-TOKEN header we send on every state-changing request.
export async function ensureCsrfCookie() {
  if (readCookie("XSRF-TOKEN")) return;
  await fetch(`${API_BASE_URL}/sanctum/csrf-cookie`, {
    credentials: "include",
  });
}

// Authenticated requests rely on the httpOnly session cookie set by the
// backend during login, so we always send credentials. There is no token in
// JavaScript to read or attach.
export async function apiFetch(path, options = {}) {
  const method = (options.method || "GET").toUpperCase();
  const headers = {
    Accept: "application/json",
    ...(options.body ? { "Content-Type": "application/json" } : {}),
    ...(options.headers || {}),
  };

  if (!SAFE_METHODS.has(method)) {
    await ensureCsrfCookie();
    const csrfToken = readCookie("XSRF-TOKEN");
    if (csrfToken) {
      headers["X-XSRF-TOKEN"] = csrfToken;
    }
  }

  return fetch(`${API_BASE_URL}${path}`, {
    ...options,
    method,
    credentials: "include",
    headers,
  });
}
