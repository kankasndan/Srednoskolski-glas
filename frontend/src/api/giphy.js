const GIPHY_URL = "https://api.giphy.com/v1/gifs";

/**
 * Trending GIFs when `q` is empty, search results otherwise.
 * `rating=g` keeps the results safe for a school audience.
 */
export async function searchGifs(q = "") {
  const params = new URLSearchParams({
    api_key: process.env.NEXT_PUBLIC_GIPHY_API_KEY ?? "",
    limit: "24",
    rating: "g",
  });
  if (q) params.set("q", q);

  const res = await fetch(`${GIPHY_URL}/${q ? "search" : "trending"}?${params}`);
  if (!res.ok) {
    throw new Error(`Failed to load GIFs (${res.status})`);
  }

  const body = await res.json();

  return (body.data ?? []).map((gif) => ({
    id: gif.id,
    url: gif.images.fixed_width.url,
    title: gif.title,
  }));
}
