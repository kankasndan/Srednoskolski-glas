import { apiFetch } from "@/lib/api";

/**
 * Username-prefix autocomplete for @mentions.
 * GET /api/users/search?q= → { data: [{ id, username, imageUrl }] }
 *
 * @param {string} [q]
 * @returns {Promise<Array<{ id: number, username: string, imageUrl: string | null }>>}
 */
export async function searchUsers(q = "") {
  const params = new URLSearchParams();
  if (q) params.set("q", q);

  const response = await apiFetch(`/api/users/search?${params}`);
  const body = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(body.message || `Failed to search users (${response.status})`);
    error.status = response.status;
    error.body = body;
    throw error;
  }

  return Array.isArray(body.data) ? body.data : [];
}
