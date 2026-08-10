"use client";

import ThreadActionsMenu from "@/components/thread/ThreadActionsMenu";
import ThreadAttachments from "@/components/thread/ThreadAttachments";
import ThreadMetaTags, { buildThreadMetaTags } from "@/components/thread/ThreadMetaTags";
import ThreadPoll from "@/components/thread/ThreadPoll";
import ThreadStats from "@/components/thread/ThreadStats";
import { renderHtmlProps } from "@/lib/html";
import { formatPostedAgo } from "@/lib/time";

export default function ThreadPost({ forum, thread, onThreadUpdated }) {
  return (
    <article className="flex flex-col gap-6 rounded-3xl border border-[#CCCCCC] bg-white p-6">
      <div className="flex items-start justify-between gap-4">
        <ThreadMetaTags
          tags={buildThreadMetaTags(thread.forum ?? forum, thread)}
          postedAgo={formatPostedAgo(thread.created_at)}
        />

        <ThreadActionsMenu thread={thread} onUpdated={onThreadUpdated} />
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

      <ThreadStats
        threadId={thread.id}
        views={thread.views}
        comments={thread.comments_count}
        votes={thread.upvotes}
        hasVoted={thread.has_voted}
        onVoted={(vote) => onThreadUpdated?.(vote)}
      />
    </article>
  );
}
