import { NextResponse } from "next/server";

const API_ORIGIN = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

/**
 * Content-Security-Policy.
 *
 * In production every script must carry the per-request nonce (Next.js stamps
 * its own bundles automatically) and `strict-dynamic` covers the chunks they
 * load, so injected markup cannot execute even if it slips past sanitizing.
 * The dev server needs `unsafe-eval`/`unsafe-inline` for React Refresh, so the
 * strict policy only applies to real builds.
 */
function contentSecurityPolicy(nonce, isDev) {
  const scriptSrc = isDev
    ? "'self' 'unsafe-inline' 'unsafe-eval'"
    : `'self' 'nonce-${nonce}' 'strict-dynamic'`;

  const connectSrc = ["'self'", API_ORIGIN, isDev ? "ws: http://localhost:*" : ""]
    .filter(Boolean)
    .join(" ");

  return [
    "default-src 'self'",
    `script-src ${scriptSrc}`,
    // Tailwind and next/font emit inline <style> blocks that carry no nonce.
    "style-src 'self' 'unsafe-inline'",
    "img-src 'self' data: blob: https://ik.imagekit.io",
    "media-src 'self' blob: https://ik.imagekit.io",
    "font-src 'self' data:",
    `connect-src ${connectSrc}`,
    "frame-src https://www.youtube.com https://www.youtube-nocookie.com https://www.tiktok.com",
    "object-src 'none'",
    "base-uri 'self'",
    "form-action 'self'",
    "frame-ancestors 'none'",
    isDev ? "" : "upgrade-insecure-requests",
  ]
    .filter(Boolean)
    .join("; ");
}

export function proxy(request) {
  const isDev = process.env.NODE_ENV !== "production";
  const nonce = crypto.randomUUID();
  const csp = contentSecurityPolicy(nonce, isDev);

  // Next.js reads the nonce back out of the request header to stamp its scripts.
  const requestHeaders = new Headers(request.headers);
  requestHeaders.set("x-nonce", nonce);
  requestHeaders.set("Content-Security-Policy", csp);

  const response = NextResponse.next({ request: { headers: requestHeaders } });
  response.headers.set("Content-Security-Policy", csp);

  return response;
}

export const config = {
  matcher: [
    {
      source: "/((?!_next/static|_next/image|favicon.ico).*)",
      missing: [
        { type: "header", key: "next-router-prefetch" },
        { type: "header", key: "purpose", value: "prefetch" },
      ],
    },
  ],
};
