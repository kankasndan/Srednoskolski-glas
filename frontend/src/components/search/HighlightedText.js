function escapeForRegExp(value) {
  return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

// Vo rezultatite pobaraniot tekst e vo boja, kako vo dizajnot.
export default function HighlightedText({ text, query }) {
  const needle = query?.trim();
  if (!text || !needle) return text;

  const parts = text.split(new RegExp(`(${escapeForRegExp(needle)})`, "gi"));

  return parts.map((part, index) =>
    part.toLowerCase() === needle.toLowerCase() ? (
      <span key={index} className="text-[var(--color-primary-200)]">
        {part}
      </span>
    ) : (
      part
    ),
  );
}
