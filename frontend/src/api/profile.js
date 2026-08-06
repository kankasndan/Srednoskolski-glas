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
