/**
 * Parse YouTube / TikTok embed pastes (iframe HTML or embed URLs).
 * Returns { type, id } or null.
 */
export function parseEmbed(input) {
  const text = (input || "").trim();
  if (!text) return null;

  const youtube = text.match(/youtube(?:-nocookie)?\.com\/embed\/([\w-]{11})/);
  if (youtube) return { type: "youtube", id: youtube[1] };

  const isTikTokEmbed =
    /tiktok-embed|tiktok\.com\/(?:embed|player)/.test(text) ||
    (/data-video-id=/.test(text) && /tiktok\.com/.test(text));
  if (isTikTokEmbed) {
    const id =
      text.match(/data-video-id="(\d+)"/)?.[1] ??
      text.match(/tiktok\.com\/(?:embed\/v2\/|player\/v1\/)(\d+)/)?.[1] ??
      text.match(/tiktok\.com\/@[\w.-]+\/video\/(\d+)/)?.[1] ??
      null;
    if (id) return { type: "tiktok", id };
  }

  return null;
}

/** Canonical embed URL stored on the thread attachment. */
export function toEmbedUrl(embed) {
  if (!embed) return null;
  if (embed.type === "youtube") {
    return `https://www.youtube.com/embed/${embed.id}`;
  }
  if (embed.type === "tiktok") {
    return `https://www.tiktok.com/player/v1/${embed.id}`;
  }
  return null;
}

/** Turn a paste (HTML or URL) into a canonical embed URL, or null. */
export function normalizeEmbedLink(input) {
  return toEmbedUrl(parseEmbed(input));
}
