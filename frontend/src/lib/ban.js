export function getActiveBan(user) {
  return user?.active_ban ?? null;
}

export function isActivelyBanned(user) {
  return getActiveBan(user) != null;
}

function unitLabel(count, one, many) {
  return `${count} ${count === 1 ? one : many}`;
}

/** Remaining duration as "6 дена и 3 часа", or null if not a timed ban. */
export function formatBanRemainingDuration(expiresAt) {
  if (!expiresAt) return null;

  const remainingMs = new Date(expiresAt).getTime() - Date.now();
  if (Number.isNaN(remainingMs) || remainingMs <= 0) {
    return null;
  }

  const totalMinutes = Math.max(1, Math.ceil(remainingMs / 60_000));
  const days = Math.floor(totalMinutes / (60 * 24));
  const hours = Math.floor((totalMinutes % (60 * 24)) / 60);
  const minutes = totalMinutes % 60;

  const parts = [];
  if (days > 0) parts.push(unitLabel(days, "ден", "дена"));
  if (hours > 0) parts.push(unitLabel(hours, "час", "часа"));
  if (days === 0 && (hours === 0 || minutes > 0)) {
    parts.push(unitLabel(minutes, "минута", "минути"));
  }

  if (parts.length === 0) return null;

  return parts.length === 1 ? parts[0] : `${parts.slice(0, -1).join(" ")} и ${parts.at(-1)}`;
}

/** Hover copy: remaining time, or that the ban is permanent. */
export function banRemainingMessage(ban) {
  if (!ban) return null;

  if (ban.type === "permanent_ban" || !ban.expires_at) {
    return "Банот е траен.";
  }

  const duration = formatBanRemainingDuration(ban.expires_at);
  if (!duration) return "Банот истече.";

  return `Банот истекува за ${duration}.`;
}

export function timedBanPopupMessage(expiresAt) {
  const duration = formatBanRemainingDuration(expiresAt) ?? "ограничено време";

  return `Поради повторно прекршување на правилата, не можеш да објавуваш и коментираш на содржини во следните ${duration}. По истекот на банот, повторно ќе можеш да учествуваш во дискусиите.`;
}

export function banDialogType(ban) {
  if (!ban?.type) return null;
  if (ban.type === "permanent_ban") return "permanent_ban";
  if (ban.type === "7-day" || ban.type === "custom") return "7-day";
  return null;
}
