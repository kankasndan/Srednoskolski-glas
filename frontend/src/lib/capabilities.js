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

function schoolIdOf(user) {
  return (
    getCapabilities(user)?.school_forum_id ??
    user?.student_data?.school_id ??
    user?.studentData?.school_id ??
    null
  );
}

export function canCreateComments(user) {
  if (!user || !hasCompletedOnboarding(user)) return false;

  const fromCapabilities = getCapabilities(user)?.can_create_comments;
  if (typeof fromCapabilities === "boolean") {
    return fromCapabilities;
  }

  return hasPermission(user, "create comments") || hasPermission(user, "manage comments");
}

export function canCreateThreads(user) {
  if (!user || !hasCompletedOnboarding(user)) return false;

  if (getCapabilities(user)?.can_create_threads === true) {
    return true;
  }

  // School members can start discussions even if a stale capability flag said otherwise.
  if (schoolIdOf(user) != null) {
    return true;
  }

  return hasPermission(user, "create threads") || hasPermission(user, "manage threads");
}

function hasPermission(user, name) {
  return Array.isArray(user?.permissions) && user.permissions.includes(name);
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
