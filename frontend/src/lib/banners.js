// Resolves a forum banner for display.
// Prefer real stored assets (local /banners/... or ImageKit). Fall back to the
// conventional public file for seeded forums / placeholders.

const BANNER_BASE = "/banners";

export function bannerFor(slug, type) {
  if (type === "school") {
    return `${BANNER_BASE}/school.svg`;
  }

  return `${BANNER_BASE}/${slug}.svg`;
}

export function isRemoteAssetUrl(url) {
  return typeof url === "string" && /^https?:\/\//i.test(url);
}

function isUsableBannerUrl(bannerUrl) {
  if (typeof bannerUrl !== "string") {
    return false;
  }

  const value = bannerUrl.trim();
  if (value === "") {
    return false;
  }

  // Old seeder used picsum placeholders — treat those as missing so local
  // public/banners/{slug}.svg can show instead.
  if (value.includes("picsum.photos")) {
    return false;
  }

  // Local public path or ImageKit / other CDN upload.
  return value.startsWith("/") || isRemoteAssetUrl(value);
}

export function resolveBannerUrl({ bannerUrl, slug, type }) {
  if (isUsableBannerUrl(bannerUrl)) {
    return bannerUrl.trim();
  }

  return bannerFor(slug, type);
}
