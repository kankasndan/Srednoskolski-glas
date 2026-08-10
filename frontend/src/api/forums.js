import { apiFetch } from "@/lib/api";

/**
 * Single forum banner/metadata.
 * GET /api/p/{slug} → { data: { forum } }
 */
export async function getForum(slug) {
  const res = await apiFetch(`/api/p/${slug}`);
  if (!res.ok) throw new Error(`Failed to load forum: ${res.status}`);
  const payload = await res.json();
  return payload.data?.forum ?? payload.data ?? payload;
}

/**
 * Sidebar forums: thematic (`general`) + school forums by city.
 * GET /api/forums → { data: { general, schools_by_city } }
 */
export async function getForums() {
  const res = await apiFetch("/api/forums");
  if (!res.ok) throw new Error(`Failed to load forums: ${res.status}`);
  const payload = await res.json();
  return payload.data ?? payload;
}

/**
 * Follow a general forum.
 * POST /api/p/{slug}/follow → { data: { is_following, members_count } }
 */
export async function followForum(slug) {
  const res = await apiFetch(`/api/p/${slug}/follow`, { method: "POST" });
  if (!res.ok) throw new Error(`Failed to follow forum: ${res.status}`);
  const payload = await res.json();
  return payload.data;
}

/**
 * Unfollow a general forum.
 * DELETE /api/p/{slug}/follow → { data: { is_following, members_count } }
 */
export async function unfollowForum(slug) {
  const res = await apiFetch(`/api/p/${slug}/follow`, { method: "DELETE" });
  if (!res.ok) throw new Error(`Failed to unfollow forum: ${res.status}`);
  const payload = await res.json();
  return payload.data;
}
