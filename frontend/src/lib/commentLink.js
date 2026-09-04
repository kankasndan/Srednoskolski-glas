/**
 * Comment deep-links: ?comment=23&expand=12.15#comment-23
 * Browsers/Next can produce a doubled hash (#comment-23#comment-23);
 * parsing takes the first comment id from either query or hash.
 */
export function parseCommentTarget(hash, search) {
  const params = new URLSearchParams(search || "");
  const fromQuery = params.get("comment");

  if (fromQuery && /^\d+$/.test(fromQuery)) {
    return Number(fromQuery);
  }

  const match = String(hash || "").match(/comment-(\d+)/);

  return match ? Number(match[1]) : null;
}

export function parseExpandPath(value) {
  if (!value) return null;

  const ids = String(value)
    .split(".")
    .map((part) => Number(part))
    .filter((id) => Number.isInteger(id) && id > 0);

  return ids.length > 0 ? ids : null;
}

/** Relative href for in-app navigation (no hash — Next.js duplicates it). */
export function commentPageHref(pathname, commentId, ancestorIds = []) {
  const params = new URLSearchParams();
  params.set("comment", String(commentId));

  if (ancestorIds.length > 0) {
    params.set("expand", ancestorIds.map(Number).join("."));
  }

  return `${pathname}?${params.toString()}`;
}

/** Absolute URL for copy/share, with a single hash. */
export function commentShareUrl(commentId, ancestorIds = []) {
  const url = new URL(window.location.pathname, window.location.origin);
  url.searchParams.set("comment", String(commentId));

  if (ancestorIds.length > 0) {
    url.searchParams.set("expand", ancestorIds.map(Number).join("."));
  }

  url.hash = `comment-${commentId}`;

  return url.toString();
}

export function normalizeNotificationHref(url) {
  if (typeof url !== "string" || url === "") return "/feed";
  if (!url.startsWith("/") || url.startsWith("//")) return "/feed";
  if (!url.startsWith("/p/") && !url.startsWith("/u/")) return "/feed";

  if (url.startsWith("/u/")) {
    return url.split("#")[0];
  }

  try {
    const parsed = new URL(url, "http://local.invalid");
    const commentId = parseCommentTarget(parsed.hash, parsed.search);
    const expand = parseExpandPath(parsed.searchParams.get("expand"));

    if (!commentId) {
      return parsed.pathname + parsed.search;
    }

    return commentPageHref(parsed.pathname, commentId, expand ?? []);
  } catch {
    return "/feed";
  }
}
