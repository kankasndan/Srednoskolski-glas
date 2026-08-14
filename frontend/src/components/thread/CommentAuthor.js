"use client";

import Link from "next/link";
import { authorProfileHref, schoolForumHref } from "@/lib/profileLinks";

function MetaLink({ href, className, children }) {
  if (!href) {
    return <span className={className}>{children}</span>;
  }

  return (
    <Link
      href={href}
      className={`${className} transition-colors hover:text-[#582FF5] hover:underline`}
      onClick={(event) => event.stopPropagation()}
    >
      {children}
    </Link>
  );
}

export default function CommentAuthor({ author }) {
  if (!author) {
    return (
      <div className="flex min-h-8 flex-wrap items-center gap-2 md:gap-3">
        <span className="text-[14px] font-bold leading-none text-black">Анонимен</span>
      </div>
    );
  }

  return (
    <div className="flex min-h-8 flex-wrap items-center gap-2 md:gap-3">
      <MetaLink
        href={authorProfileHref(author)}
        className="text-[14px] font-bold leading-none text-black"
      >
        {author.username}
      </MetaLink>
      {author.school?.name ? (
        <MetaLink
          href={schoolForumHref(author.school)}
          className="text-[12px] leading-none text-[#999999]"
        >
          {author.school.name}
        </MetaLink>
      ) : null}
    </div>
  );
}
