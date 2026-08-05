import { API_BASE_URL } from "@/lib/api";

const USE_MOCK = true;

const MOCK_URLS = {
  threads: "/MOCK_JSON/profile-threads-mock.json",
  comments: "/MOCK_JSON/profile-comments-mock.json",
  followedForums: "/MOCK_JSON/profile-followed-forums-mock.json",
  followedThreads: "/MOCK_JSON/profile-followed-threads-mock.json",
};

const REAL_PATHS = {
  threads: "/api/me/threads",
  comments: "/api/me/comments",
  followedForums: "/api/me/followed-forums",
  followedThreads: "/api/me/followed-threads",
};

export async function getProfileUser() {
  const res = await fetch(API_BASE_URL + "/api/me", { credentials: "include" });

  if (!res.ok) throw new Error(`Failed to load profile user: ${res.status}`);

  const payload = await res.json();

  return payload.data ?? payload.user ?? payload;
}

async function getProfileList(key) {
  const url = USE_MOCK ? MOCK_URLS[key] : API_BASE_URL + REAL_PATHS[key];
  const res = await fetch(url, { credentials: "include" });

  if (!res.ok) throw new Error(`Failed to load ${key}: ${res.status}`);

  const payload = await res.json();

  return payload.data ?? payload;
}

export function getMyThreads() {
  return getProfileList("threads");
}

export function getMyComments() {
  return getProfileList("comments");
}

export function getMyFollowedForums() {
  return getProfileList("followedForums");
}

export function getMyFollowedThreads() {
  return getProfileList("followedThreads");
}
