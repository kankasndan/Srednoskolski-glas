/** Public profile route — page can be added later at `/u/[username]`. */
export function authorProfileHref(author) {
  if (!author?.username) return null;
  return `/u/${encodeURIComponent(author.username)}`;
}

export function schoolForumHref(school) {
  const slug = school?.forum?.slug;
  return slug ? `/p/${slug}` : null;
}

export function schoolCityLabel(school) {
  const name = school?.name?.trim();
  if (!name) {
    return "";
  }

  const city =
    typeof school.city === "string"
      ? school.city.trim()
      : school.city?.name?.trim();

  return city ? `${name}-${city}` : name;
}

export function forumHref(forum) {
  return forum?.slug ? `/p/${forum.slug}` : null;
}
