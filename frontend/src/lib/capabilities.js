export function getCapabilities(user) {
  return user?.capabilities ?? null;
}

export function hasCompletedOnboarding(user) {
  if (!user) return false;

  const fromCapabilities = getCapabilities(user)?.has_completed_onboarding;
  if (typeof fromCapabilities === "boolean") {
    return fromCapabilities;
  }

  return user.onboarding_completed_at != null;
}

export function needsOnboarding(user) {
  return Boolean(user) && !hasCompletedOnboarding(user);
}

export function canCreateComments(user) {
  return Boolean(getCapabilities(user)?.can_create_comments);
}

export function canCreateThreads(user) {
  return Boolean(getCapabilities(user)?.can_create_threads);
}

/** Shared copy when an incomplete account tries a community action. */
export const ONBOARDING_REQUIRED_MESSAGE =
  "Заврши го onboarding процесот за да можеш да ја извршиш оваа акција.";

/** Whether the user may start a thread in this specific forum. */
export function canCreateThreadInForum(user, forum) {
  if (!canCreateThreads(user) || !forum) return false;

  if (forum.type === "general") return true;

  if (forum.type === "school") {
    const schoolForumId = getCapabilities(user)?.school_forum_id;
    return schoolForumId != null && Number(forum.id) === Number(schoolForumId);
  }

  return false;
}
