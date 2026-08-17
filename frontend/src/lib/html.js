import DOMPurify from "isomorphic-dompurify";

const ALLOWED_TAGS = [
  "p",
  "br",
  "strong",
  "b",
  "em",
  "i",
  "u",
  "s",
  "a",
  "ul",
  "ol",
  "li",
  "h1",
  "h2",
  "h3",
  "blockquote",
  "code",
  "pre",
];

const ALLOWED_ATTR = ["href", "target", "rel"];

DOMPurify.addHook("afterSanitizeAttributes", (node) => {
  if (node.tagName !== "A") return;

  const href = node.getAttribute("href") || "";
  if (href.startsWith("//") || /^\s*(javascript|data|vbscript):/i.test(href)) {
    node.removeAttribute("href");
  }

  // Never trust stored markup to carry rel itself — without noopener the opened
  // page can rewrite this tab through window.opener.
  if (node.getAttribute("target") === "_blank") {
    node.setAttribute("rel", "noopener noreferrer ugc");
  } else {
    node.removeAttribute("target");
  }
});

/**
 * TipTap stores description as HTML. Use stripHtml for list/card previews
 * and renderHtmlProps for the full thread body.
 */
export function stripHtml(html) {
  if (!html) return "";
  if (typeof document === "undefined") {
    return html.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
  }
  const container = document.createElement("div");
  container.innerHTML = sanitizeHtml(html);
  return (container.textContent || "").replace(/\s+/g, " ").trim();
}

export function sanitizeHtml(html) {
  return DOMPurify.sanitize(html || "", {
    ALLOWED_TAGS,
    ALLOWED_ATTR,
    ALLOW_DATA_ATTR: false,
  });
}

export function renderHtmlProps(html) {
  return { dangerouslySetInnerHTML: { __html: sanitizeHtml(html) } };
}
