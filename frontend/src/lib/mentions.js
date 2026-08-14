export const MENTION_USERNAME_PATTERN = "[\\w.\\u0400-\\u04FF]+";
export const MENTION_QUERY_PATTERN = "[\\w.\\u0400-\\u04FF]*";
export const MENTION_REGEX = new RegExp(`@(${MENTION_USERNAME_PATTERN})`, "g");
export const ACTIVE_MENTION_REGEX = new RegExp(`(^|\\s)@(${MENTION_QUERY_PATTERN})$`);

export const MENTION_USERS = [
  { username: "марко_2026", school: "Гим. Орце Николов" },
  { username: "ана_2027", school: "СУГС Михајло Пупин" },
  { username: "елена.к", school: "СУГС Михајло Пупин" },
  { username: "анонимен_111", school: "Гим. Никола Карев" },
  { username: "стефан_22", school: "СУГС Михајло Пупин" },
  { username: "гоце_2027", school: "Гим. Никола Карев" },
  { username: "ана_матуранка", school: "СУГС Михајло Пупин" },
  { username: "корисник", school: "Средношколски глас" },
];

export function filterMentionUsers(query) {
  const normalizedQuery = query.trim().toLowerCase();

  if (!normalizedQuery) {
    return MENTION_USERS;
  }

  return MENTION_USERS.filter(({ username }) =>
    username.toLowerCase().includes(normalizedQuery),
  );
}

export function getActiveMention(text, cursor) {
  const beforeCursor = text.slice(0, cursor);
  const match = beforeCursor.match(ACTIVE_MENTION_REGEX);

  if (!match) {
    return null;
  }

  const query = match[2] || "";

  return {
    query,
    start: cursor - query.length - 1,
  };
}

export function splitMentionText(text) {
  if (!text) {
    return [];
  }

  const parts = [];
  let lastIndex = 0;
  const regex = new RegExp(MENTION_REGEX);

  for (const match of text.matchAll(regex)) {
    if (match.index > lastIndex) {
      parts.push({ type: "text", value: text.slice(lastIndex, match.index) });
    }

    parts.push({ type: "mention", value: match[0], username: match[1] });
    lastIndex = match.index + match[0].length;
  }

  if (lastIndex < text.length) {
    parts.push({ type: "text", value: text.slice(lastIndex) });
  }

  return parts;
}
