import { apiFetch } from "@/lib/api";

/**
 * Single forum banner/metadata.
 * GET /api/p/{slug} → { data: { forum } }
 * Pass trackView on the forum page so visits count toward explore ranking.
 */
export async function getForum(slug, { trackView = false } = {}) {
  const query = trackView ? "?track_view=1" : "";
  const res = await apiFetch(`/api/p/${slug}${query}`);
  if (!res.ok) throw new Error(`Failed to load forum: ${res.status}`);
  const payload = await res.json();
  return payload.data?.forum ?? payload.data ?? payload;
}

/**
 * Explore page: top visited general forums + weekly popular threads.
 * GET /api/explore → { data: { forums, threads } }
 */
export async function getExplore() {
  const res = await apiFetch("/api/explore");
  if (!res.ok) throw new Error(`Failed to load explore: ${res.status}`);
  const payload = await res.json();
  return payload.data ?? payload;
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
 * Follow a forum (general or school).
 * POST /api/p/{slug}/follow → { data: { is_following, members_count } }
 */
export async function followForum(slug) {
  const res = await apiFetch(`/api/p/${slug}/follow`, { method: "POST" });
  const payload = await res.json().catch(() => ({}));
  if (!res.ok) {
    const error = new Error(payload.message || `Failed to follow forum: ${res.status}`);
    error.status = res.status;
    error.body = payload;
    throw error;
  }
  return payload.data;
}

/**
 * Unfollow a forum (not allowed for the caller's own school forum).
 * DELETE /api/p/{slug}/follow → { data: { is_following, members_count } }
 */
export async function unfollowForum(slug) {
  const res = await apiFetch(`/api/p/${slug}/follow`, { method: "DELETE" });
  const payload = await res.json().catch(() => ({}));
  if (!res.ok) {
    const error = new Error(payload.message || `Failed to unfollow forum: ${res.status}`);
    error.status = res.status;
    error.body = payload;
    throw error;
  }
  return payload.data;
}
