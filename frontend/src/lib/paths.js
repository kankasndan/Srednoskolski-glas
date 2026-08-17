/** Same-origin relative paths only — never protocol-relative or absolute URLs. */
export function safeInternalPath(value, fallback = "/feed") {
  if (typeof value !== "string") return fallback;

  const path = value.trim();
  if (
    !path.startsWith("/") ||
    path.startsWith("//") ||
    path.startsWith("/\\") ||
    path.includes("://") ||
    path.includes("\\")
  ) {
    return fallback;
  }

  return path;
}

/**
 * http(s) URLs (or same-origin paths) only. Stored attachment URLs come from the
 * API, so this is the client-side half of the check: a `javascript:` or `data:`
 * value must never reach an href or a media src.
 */
export function safeExternalUrl(value) {
  if (typeof value !== "string") return null;

  const url = value.trim();
  if (url === "") return null;

  if (url.startsWith("/") && !url.startsWith("//")) return url;

  try {
    const parsed = new URL(url);

    return parsed.protocol === "https:" || parsed.protocol === "http:" ? url : null;
  } catch {
    return null;
  }
}
