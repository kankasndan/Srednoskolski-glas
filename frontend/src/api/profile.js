import { apiFetch } from "@/lib/api";

export async function getProfileUser() {
  const res = await apiFetch("/api/me");

  if (!res.ok) {
    throw new Error(`Failed to load profile user: ${res.status}`);
  }

  const payload = await res.json();

  return payload.user ?? payload.data ?? payload;
}

export async function getMyCounts() {
  const res = await apiFetch("/api/me/counts");

  if (!res.ok) {
    throw new Error(`Failed to load profile counts: ${res.status}`);
  }

  const payload = await res.json();

  return payload.data ?? payload;
}

async function getProfileList(path) {
  const res = await apiFetch(path);

  if (!res.ok) {
    throw new Error(`Failed to load ${path}: ${res.status}`);
  }

  const payload = await res.json();
  const data = payload.data ?? payload;

  return Array.isArray(data) ? data : [];
}

export function getMyThreads() {
  return getProfileList("/api/me/threads");
}

export function getMyComments() {
  return getProfileList("/api/me/comments");
}

export function getMyFollowedForums() {
  return getProfileList("/api/me/followed-forums");
}

export function getMyFollowingUsers() {
  return getProfileList("/api/me/following-users");
}

/** @param {string} username */
export async function getPublicProfile(username) {
  const res = await apiFetch(`/api/u/${encodeURIComponent(username)}`);

  if (res.status === 404) return null;
  if (!res.ok) throw new Error(`Failed to load user profile: ${res.status}`);

  const payload = await res.json();

  return payload.data ?? payload;
}

/** @param {string} username */
export function getUserThreads(username) {
  return getProfileList(`/api/u/${encodeURIComponent(username)}/threads`);
}

/** @param {string} username */
export function getUserComments(username) {
  return getProfileList(`/api/u/${encodeURIComponent(username)}/comments`);
}

/** @param {string} username */
export function getUserFollowedForums(username) {
  return getProfileList(`/api/u/${encodeURIComponent(username)}/followed-forums`);
}

/** @param {string} username */
export async function followUser(username) {
  const res = await apiFetch(`/api/u/${encodeURIComponent(username)}/follow`, {
    method: "POST",
  });

  const body = await res.json().catch(() => ({}));

  if (!res.ok) {
    const error = new Error(body.message || `Failed to follow user (${res.status})`);
    error.status = res.status;
    throw error;
  }

  return body.data;
}

/** @param {string} username */
export async function unfollowUser(username) {
  const res = await apiFetch(`/api/u/${encodeURIComponent(username)}/follow`, {
    method: "DELETE",
  });

  const body = await res.json().catch(() => ({}));

  if (!res.ok) {
    const error = new Error(body.message || `Failed to unfollow user (${res.status})`);
    error.status = res.status;
    throw error;
  }

  return body.data;
}
