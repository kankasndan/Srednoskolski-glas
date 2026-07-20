"use client";

import Image from "next/image";
import Link from "next/link";
import { FORUM_THREADS } from "@/lib/threads";

function ForumTag({ tag }) {
  return (
    <span
      className={`flex h-6 shrink-0 items-center rounded-[6px] px-2 font-[family-name:var(--font-roboto)] text-[12px] font-normal leading-4 text-black ${
        tag.tone === "highlight"
          ? "bg-[#F0E92F] border-[0.5px] border-[#CCCCCC]"
          : "bg-[#E6E6E6] border-[0.5px] border-[#CCCCCC]"
      }`}
      style={{ gap: tag.icon ? "8px" : undefined }}
    >
      {tag.icon ? (
        <span className="relative size-4 shrink-0 overflow-hidden">
          <Image
            src={tag.icon}
            alt=""
            width={16}
            height={16}
            className="absolute left-1/2 top-1/2 size-4 -translate-x-1/2 -translate-y-1/2"
          />
        </span>
      ) : null}
      {tag.label}
    </span>
  );
}

function ForumActionButton({ icon, label, count, noHover = false }) {
  return (
    <button
      type="button"
      aria-label={label}
      className={`flex h-10 w-24 items-center justify-center gap-4 rounded-[12px] border border-[#CCCCCC] bg-white px-4 py-2 opacity-80 transition-all ${
        noHover
          ? ""
          : "hover:border-[#582FF5] hover:bg-[#F5F2FF] hover:opacity-100 hover:shadow-sm cursor-pointer"
      }`}
    >
      <Image src={icon} alt="" width={24} height={24} className="size-6 shrink-0" />
      <span className="min-w-0 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-[19px] text-black">
        {count}
      </span>
    </button>
  );
}



function ForumThread({ thread }) {
  const metadataRow = (
    <div className="flex max-w-full flex-wrap items-center gap-2 overflow-hidden">
      {thread.tags.map((tag) => (
        <ForumTag key={`${thread.id}-${tag.label}`} tag={tag} />
      ))}
      <span className="shrink-0 font-[family-name:var(--font-roboto)] text-[12px] font-normal leading-4 text-[#595959]">
        {thread.postedAgo}
      </span>
    </div>
  );

  const textContent = (
    <div className="flex flex-col gap-2">
      <Link
        href={`/feed/thread/${thread.id}`}
        className="overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-[27px] tracking-normal text-black transition-colors hover:text-[#582FF5]"
      >
        {thread.title}
      </Link>
      <p className="overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
        {thread.excerpt}
      </p>
    </div>
  );

  if (thread.image) {
    return (
      <article className="relative flex w-full max-w-full flex-col gap-4 bg-transparent pb-6 after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:rounded-full after:bg-[#CFE9ED]">
        <div className="flex flex-col gap-4">
          {metadataRow}
          {textContent}
        </div>
        <Link href={`/feed/thread/${thread.id}`} className="block">
          <Image
            src={thread.image}
            alt=""
            width={990}
            height={421}
            className="h-[421px] w-full rounded-[24px] object-cover transition-opacity hover:opacity-90"
            priority
          />
        </Link>
        <div className="flex flex-row items-center justify-end gap-2">
          <ForumActionButton icon="/eye.svg" label="Прегледи" count={thread.views} noHover={true} />
          <ForumActionButton icon="/chat-1-line.svg" label="Коментари" count={thread.comments} />
          <ForumActionButton icon="/Chevrons up.svg" label="Гласај нагоре" count={thread.votes} />
        </div>
      </article>
    );
  }

  return (
    <article className="relative flex w-full max-w-full items-start justify-between gap-8 bg-transparent py-6 after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:rounded-full after:bg-[#CFE9ED]">
      <div className="flex min-w-0 flex-1 flex-col gap-4">
        {metadataRow}
        {textContent}
      </div>
      <div className="flex shrink-0 flex-col gap-2">
        <ForumActionButton icon="/eye.svg" label="Прегледи" count={thread.views} noHover={true} />
        <ForumActionButton icon="/chat-1-line.svg" label="Коментари" count={thread.comments} />
        <ForumActionButton icon="/Chevrons up.svg" label="Гласај нагоре" count={thread.votes} />
      </div>
    </article>
  );
}

export default function ForumThreadList() {
  return (
    <div className="flex w-[990px] max-w-full flex-col gap-6" aria-label="Дискусии за државна матура">
      {FORUM_THREADS.map((thread) => (
        <ForumThread key={thread.id} thread={thread} />
      ))}
    </div>
  );
}

