"use client";

import Image from "next/image";
import FollowThreadButton from "@/components/thread/FollowThreadButton";
import ThreadActionsMenu from "@/components/thread/ThreadActionsMenu";
import ThreadAttachments from "@/components/thread/ThreadAttachments";
import ThreadMetaTags from "@/components/thread/ThreadMetaTags";
import ThreadPoll from "@/components/thread/ThreadPoll";
import ThreadStats from "@/components/thread/ThreadStats";
import { renderHtmlProps } from "@/lib/html";
import { formatPostedAgo } from "@/lib/time";

const SCHOOL_ICON = "/icons/uchilishte.svg";
const DEFAULT_AVATAR = "/Generic avatar.svg";

function buildTags(forum, thread) {
  const tags = [
    { label: forum.name, icon: forum.imageUrl, zoom: true },
    {
      label: thread.is_anonymous ? "Анонимен" : thread.author?.username ?? "Корисник",
      icon: thread.is_anonymous ? DEFAULT_AVATAR : thread.author?.imageUrl ?? DEFAULT_AVATAR,
    },
  ];

  if (!thread.is_anonymous && thread.author?.school?.name) {
    tags.push({ label: thread.author.school.name, icon: SCHOOL_ICON });
  }

  return tags;
}

function IconButton({ icon, label }) {
  return (
    <button
      type="button"
      aria-label={label}
      className="grid size-9 cursor-pointer place-items-center rounded-lg transition-colors hover:bg-[#E5E5E5]"
    >
      <Image src={icon} alt="" width={20} height={20} className="size-5" />
    </button>
  );
}

export default function ThreadPost({ forum, thread }) {
  return (
    <article className="flex flex-col gap-6 rounded-3xl border border-[#CCCCCC] bg-white p-6">
      <div className="flex items-start justify-between gap-4">
        <ThreadMetaTags
          tags={buildTags(forum, thread)}
          postedAgo={formatPostedAgo(thread.created_at)}
        />

        <div className="flex shrink-0 items-center gap-1">
          <IconButton icon="/share-line.svg" label="Сподели ја дискусијата" />
          <ThreadActionsMenu thread={thread} />
        </div>
      </div>

      <div className="flex flex-col gap-4">
        <h1 className="text-[20px] font-bold leading-[27px] text-black">
          {thread.title}
        </h1>
        {thread.description ? (
          <div
            className="whitespace-pre-line text-[16px] leading-[22px] text-[#595959] [&_p]:mb-2 [&_p:last-child]:mb-0"
            {...renderHtmlProps(thread.description)}
          />
        ) : null}
        <ThreadAttachments attachments={thread.attachments} />
        <ThreadPoll poll={thread.poll} />
      </div>

      <hr className="border-[#CCCCCC]" />

      <div className="flex items-center justify-between gap-4">
        <ThreadStats
          views={thread.views}
          comments={thread.comments_count}
          votes={thread.upvotes}
        />
        <FollowThreadButton />
      </div>
    </article>
  );
}
