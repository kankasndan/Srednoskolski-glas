import { apiFetch } from "@/lib/api";

/**
 * Trending GIFs when `q` is empty, search results otherwise.
 * The backend keeps the Giphy key server-side and forces `rating=g`.
 */
export async function searchGifs(q = "") {
  const params = new URLSearchParams();
  if (q) params.set("q", q);

  const query = params.toString();
  const response = await apiFetch(`/api/gifs${query ? `?${query}` : ""}`);
  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to load GIFs (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return Array.isArray(body.data) ? body.data : [];
}
