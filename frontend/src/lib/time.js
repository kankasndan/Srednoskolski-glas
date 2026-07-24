export function formatPostedAgo(value) {
  const createdAt = new Date(value);

  if (Number.isNaN(createdAt.getTime())) {
    return "";
  }

  const diffMs = Date.now() - createdAt.getTime();
  const diffMinutes = Math.max(0, Math.floor(diffMs / 60000));

  if (diffMinutes < 60) {
    return `пред ${Math.max(1, diffMinutes)}м.`;
  }

  const diffHours = Math.floor(diffMinutes / 60);

  if (diffHours < 24) {
    return `пред ${diffHours}ч.`;
  }

  return `пред ${Math.floor(diffHours / 24)}д.`;
}
