export function getCapabilities(user) {
  return user?.capabilities ?? null;
}

export function canCreateComments(user) {
  return Boolean(getCapabilities(user)?.can_create_comments);
}

export function canCreateThreads(user) {
  return Boolean(getCapabilities(user)?.can_create_threads);
}

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
