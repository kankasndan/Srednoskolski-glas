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
