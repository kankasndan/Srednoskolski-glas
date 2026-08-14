import { splitMentionText } from "@/lib/mentions";

export default function MentionText({ text, className = "" }) {
  if (!text) {
    return null;
  }

  return (
    <span className={className}>
      {splitMentionText(text).map((part, index) => {
        if (part.type === "mention") {
          return (
            <span
              key={`${part.value}-${index}`}
              className="cursor-pointer font-bold text-[#582FF5]"
            >
              {part.value}
            </span>
          );
        }

        return <span key={`text-${index}`}>{part.value}</span>;
      })}
    </span>
  );
}
