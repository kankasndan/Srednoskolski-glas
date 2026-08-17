"use client";

import Link from "next/link";
import { mentionParts } from "@/lib/mentions";
import { authorProfileHref } from "@/lib/profileLinks";

// Komentarite od prvo nivo se sivi, odgovorite se crni.
export default function CommentBody({
  text,
  mentions = [],
  muted,
  className = "text-[14px] leading-[22px]",
}) {
  return (
    <p
      className={`break-words ${className} ${
        muted ? "text-[#595959]" : "text-black"
      }`}
    >
      {mentionParts(text, mentions).map((part, index) =>
        part.type === "mention" ? (
          <Link
            key={`${part.username}-${index}`}
            href={authorProfileHref({ username: part.username })}
            className="relative z-10 font-bold text-[#582FF5] hover:underline"
            onClick={(event) => event.stopPropagation()}
          >
            {part.value}
          </Link>
        ) : (
          <span key={index}>{part.value}</span>
        ),
      )}
    </p>
  );
}
