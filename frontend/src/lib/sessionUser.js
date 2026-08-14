import { apiFetch } from "@/lib/api";
import { needsOnboarding } from "@/lib/capabilities";

/** @type {object | null | undefined} undefined = not loaded yet */
let cachedUser = undefined;
/** @type {Promise<object | null> | null} */
let inflight = null;
const listeners = new Set();

function notify() {
  for (const listener of listeners) {
    listener(cachedUser);
  }
}

/** Last known session user (`undefined` until the first load finishes). */
export function getCachedSessionUser() {
  return cachedUser;
}

export function subscribeSessionUser(listener) {
  listeners.add(listener);
  return () => listeners.delete(listener);
}

export function setSessionUser(user) {
  cachedUser = user ?? null;
  notify();
}

/** Confirmed signed-out state (e.g. after logout). */
export function clearSessionUser() {
  cachedUser = null;
  notify();
}

/** Drop the cache so the next loadSessionUser() hits the network. */
export function invalidateSessionUser() {
  cachedUser = undefined;
  inflight = null;
}

/**
 * Load `/api/me` once and reuse across remounts (page navigations).
 * @param {{ force?: boolean }} [options]
 */
export async function loadSessionUser({ force = false } = {}) {
  if (!force && cachedUser !== undefined) {
    // Stale cache from before capabilities were attached — refetch.
    const caps = cachedUser?.capabilities;
    const missingCaps =
      cachedUser != null &&
      (caps == null ||
        typeof caps.can_create_threads !== "boolean" ||
        typeof caps.can_change_school !== "boolean");
    if (!missingCaps) {
      return cachedUser;
    }
  }

  if (inflight) {
    if (!force) {
      return inflight;
    }

    try {
      await inflight;
    } catch {
      // Previous request failed; continue with a fresh load.
    }
  }

  inflight = (async () => {
    try {
      const response = await apiFetch("/api/me");

      if (!response.ok) {
        cachedUser = null;
        return null;
      }

      const data = await response.json();
      const nextUser = data.user ?? null;

      if (nextUser && typeof nextUser === "object") {
        nextUser.capabilities = data.capabilities ?? nextUser.capabilities ?? null;
        nextUser.permissions = data.permissions ?? nextUser.permissions ?? [];
      }

      cachedUser = nextUser;

      if (needsOnboarding(nextUser)) {
        localStorage.setItem("onboarding_pending", "1");
      }

      return nextUser;
    } catch {
      cachedUser = null;
      return null;
    } finally {
      inflight = null;
      notify();
    }
  })();

  return inflight;
}
