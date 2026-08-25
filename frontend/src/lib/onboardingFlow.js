import { loadSessionUser } from "@/lib/sessionUser";

const GENERATED_AVATAR_KEY = "onboarding_generated_avatar";

export function rememberGeneratedAvatar(url) {
  if (typeof window === "undefined") return;

  if (url) {
    sessionStorage.setItem(GENERATED_AVATAR_KEY, url);
  }
}

export function readGeneratedAvatar() {
  if (typeof window === "undefined") return null;

  return sessionStorage.getItem(GENERATED_AVATAR_KEY);
}

export function finishOnboarding(router) {
  localStorage.removeItem("onboarding_pending");
  sessionStorage.removeItem(GENERATED_AVATAR_KEY);

  // Ensure /api/me capabilities (create threads, etc.) are fresh on the feed.
  loadSessionUser({ force: true }).finally(() => router.push("/feed"));
}
