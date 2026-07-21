// Resolves a forum's banner image from its slug, mirroring how the forum icons
// live at /icons/{slug}.svg. Every general (topic) forum has its own artwork,
// while all school forums share a single banner. When a slug has no file yet,
// the ForumBanner falls back to a solid background colour, so a missing image
// degrades gracefully instead of rendering broken.

const BANNER_BASE = "/banners";

export function bannerFor(slug, type) {
  if (type === "school") {
    return `${BANNER_BASE}/school.svg`;
  }

  return `${BANNER_BASE}/${slug}.svg`;
}
