"use client";

import Link from "next/link";
import { formatDistanceToNow } from "date-fns";
import { mk } from "date-fns/locale";
import ThreadStatButtons from "@/components/ThreadStatButtons";

const DEFAULT_AVATAR = "/Generic avatar.svg";

function Tag({ children, icon, highlight }) {
  return (
    <span
      className={`flex h-6 shrink-0 items-center gap-2 rounded-md border-[0.5px] border-(--color-grays-300) px-2 py-1 font-(family-name:--font-roboto) text-[12px] leading-4 text-black ${
        highlight ? "bg-(--color-accent-200)" : "bg-gray-100"
      }`}
    >
      {icon ? (
        <img src={icon} alt="" className="size-4 rounded-full object-cover" />
      ) : null}
      {children}
    </span>
  );
}

export default function ProfileFollowedThreadItem({ thread }) {
  const href = `/p/${thread.forum.slug}/${thread.id}`;
  const author = thread.is_anonymous ? null : thread.author;

  return (
    <article className="relative flex cursor-pointer items-center justify-between gap-8 rounded-3xl border-b border-b-[#CFE9ED] p-6 transition-colors hover:bg-[#DCEBED]">
      <Link
        href={href}
        aria-label={thread.title}
        className="absolute inset-0 rounded-3xl"
      />

      <div className="flex min-w-0 flex-col gap-4">
        <div className="flex flex-wrap items-center gap-2">
          {thread.is_featured ? <Tag highlight>Истакнато</Tag> : null}
          <Tag icon={author?.imageUrl || DEFAULT_AVATAR}>
            {author ? author.username : "Анонимен"}
          </Tag>
          {author?.school?.name ? <Tag>{author.school.name}</Tag> : null}
          <span className="font-(family-name:--font-roboto) text-[12px] leading-4 text-[#595959]">
            {formatDistanceToNow(new Date(thread.created_at), {
              addSuffix: true,
              locale: mk,
            })}
          </span>
        </div>

        <div className="flex flex-col gap-2">
          <h3 className="w-fit max-w-full overflow-hidden text-ellipsis whitespace-nowrap font-(family-name:--font-manrope) text-[20px] font-bold leading-6.75 text-black">
            {thread.title}
          </h3>
          <p className="font-(family-name:--font-manrope) text-[16px] leading-5.5 text-[#595959]">
            {thread.description}
          </p>
        </div>
      </div>

      <ThreadStatButtons thread={thread} href={href} />
    </article>
  );
}
