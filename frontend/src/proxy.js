import { NextResponse } from "next/server";

// GIF search goes through our API; only the CDN still needs to be in img-src.
const GIPHY_MEDIA = "https://*.giphy.com";

/**
 * Content-Security-Policy.
 *
 * The dev server needs `unsafe-eval`/`unsafe-inline` for React Refresh.
 * Production keeps scripts same-origin and allows inline Next bootstrap script.
 * (Nonce-based strict CSP can be reintroduced once the runtime reliably stamps
 * nonce attributes on every script block in this deployment target.)
 */
function contentSecurityPolicy(nonce, isDev) {
  const scriptSrc = isDev
    ? "'self' 'unsafe-inline' 'unsafe-eval'"
    : "'self' 'unsafe-inline'";

  const connectSrc = ["'self'", isDev ? "ws: http://localhost:*" : ""]
    .filter(Boolean)
    .join(" ");

  return [
    "default-src 'self'",
    `script-src ${scriptSrc}`,
    // Tailwind and next/font emit inline <style> blocks that carry no nonce.
    "style-src 'self' 'unsafe-inline'",
    `img-src 'self' data: blob: https://ik.imagekit.io ${GIPHY_MEDIA}`,
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
  const { pathname } = request.nextUrl;
  if (pathname.startsWith("/api/") || pathname.startsWith("/sanctum/")) {
    return NextResponse.next();
  }

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
