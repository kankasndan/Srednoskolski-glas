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
  container.innerHTML = html;
  return (container.textContent || "").replace(/\s+/g, " ").trim();
}

export function renderHtmlProps(html) {
  return { dangerouslySetInnerHTML: { __html: html || "" } };
}
