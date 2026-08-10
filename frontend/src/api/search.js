import { apiFetch } from "@/lib/api";

/**
 * Search threads (and matching forums when `q` is set and `forum` is not).
 * GET /api/search → { data, forums, meta, links }
 */
export async function searchContent({
  q = "",
  forum = null,
  page = 1,
  perPage = 5,
  sort,
  time,
} = {}) {
  const params = new URLSearchParams({
    page: String(page),
    per_page: String(perPage),
  });
  if (q) params.set("q", q);
  if (forum) params.set("forum", forum);
  if (sort) params.set("sort", sort);
  if (time) params.set("time", time);

  const res = await apiFetch(`/api/search?${params}`);
  if (!res.ok) {
    const err = new Error(`Search failed: ${res.status}`);
    err.status = res.status;
    throw err;
  }
  return res.json();
}
