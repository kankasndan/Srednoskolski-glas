import { loadSessionUser } from "@/lib/sessionUser";

export function finishOnboarding(router) {
  localStorage.removeItem("onboarding_pending");

  // Ensure /api/me capabilities (create threads, etc.) are fresh on the feed.
  loadSessionUser({ force: true }).finally(() => router.push("/feed"));
}
