/** Public profile route — page can be added later at `/u/[username]`. */
export function authorProfileHref(author) {
  if (!author?.username) return null;
  return `/u/${encodeURIComponent(author.username)}`;
}

export function schoolForumHref(school) {
  const slug = school?.forum?.slug;
  return slug ? `/p/${slug}` : null;
}

export function forumHref(forum) {
  return forum?.slug ? `/p/${forum.slug}` : null;
}
