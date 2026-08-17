const USERNAME_CHARS = String.raw`\p{L}0-9_.-`;
const MENTION_DRAFT = new RegExp(`(^|[^${USERNAME_CHARS}])@([${USERNAME_CHARS}]{0,20})$`, "u");

function mentionTokenRegex() {
  return /(@[\p{L}0-9_](?:[\p{L}0-9_.-]*[\p{L}0-9_])?)/gu;
}

/**
 * If the caret is in an @username draft, return the query and range.
 *
 * @param {string} text
 * @param {number} cursor
 * @returns {{ query: string, start: number, cursor: number } | null}
 */
export function getMentionDraft(text, cursor) {
  const before = text.slice(0, cursor);
  const match = before.match(MENTION_DRAFT);
  if (!match) return null;

  const query = match[2];
  return {
    query,
    start: before.length - query.length - 1,
    cursor,
  };
}

/**
 * Split comment text into plain runs and resolved @mentions.
 *
 * @param {string} text
 * @param {Array<{ id?: number, username: string, imageUrl?: string | null }>} [mentions]
 * @returns {Array<{ type: "text" | "mention", value: string, username?: string }>}
 */
export function mentionParts(text, mentions = []) {
  const lookup = new Map(
    (mentions ?? [])
      .filter((user) => user?.username)
      .map((user) => [user.username.toLowerCase(), user.username]),
  );

  return String(text ?? "")
    .split(mentionTokenRegex())
    .filter((part) => part !== "")
    .map((part) => {
      if (!part.startsWith("@")) {
        return { type: "text", value: part };
      }

      const resolved = lookup.get(part.slice(1).toLowerCase());
      if (!resolved || resolved.length < 3) {
        return { type: "text", value: part };
      }

      return { type: "mention", value: `@${resolved}`, username: resolved };
    });
}
