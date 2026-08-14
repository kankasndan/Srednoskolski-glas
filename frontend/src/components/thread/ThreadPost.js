"use client";

import ThreadActionsMenu from "@/components/thread/ThreadActionsMenu";
import ThreadAttachments from "@/components/thread/ThreadAttachments";
import ThreadMetaTags, { buildThreadMetaTags } from "@/components/thread/ThreadMetaTags";
import ThreadPoll from "@/components/thread/ThreadPoll";
import ThreadShareButton from "@/components/thread/ThreadShareButton";
import ThreadStats from "@/components/thread/ThreadStats";
import ThreadViewCount from "@/components/thread/ThreadViewCount";
import { renderHtmlProps } from "@/lib/html";
import { formatPostedAgo } from "@/lib/time";

export default function ThreadPost({ forum, thread, onThreadUpdated }) {
  const isOwner = Boolean(thread?.is_owner);

  return (
    <article className="flex flex-col gap-4 rounded-3xl border border-[#CFE9ED] bg-white p-6 md:gap-6">
      <div className="flex items-start justify-between gap-3 md:gap-4">
        <ThreadMetaTags
          tags={buildThreadMetaTags(thread.forum ?? forum, thread)}
          postedAgo={formatPostedAgo(thread.created_at)}
          hideForumOnPhone
        />

        {isOwner ? (
          <ThreadActionsMenu
            thread={thread}
            onUpdated={onThreadUpdated}
          />
        ) : (
          <div className="hidden items-center gap-1 md:flex">
            <ThreadShareButton />
            <ThreadActionsMenu thread={thread} onUpdated={onThreadUpdated} />
          </div>
        )}
      </div>

      <div className="flex flex-col gap-4">
        <h1 className="text-[18px] font-bold leading-[25px] text-black md:text-[20px] md:leading-[27px]">
          {thread.title}
        </h1>
        {thread.description ? (
          <div
            className="whitespace-pre-line break-words text-[15px] leading-[22px] text-[#595959] md:text-[16px] [&_p]:mb-2 [&_p:last-child]:mb-0"
            {...renderHtmlProps(thread.description)}
          />
        ) : null}
        <ThreadViewCount views={thread.views} className="hidden w-24 md:flex" />
        <ThreadAttachments attachments={thread.attachments} />
        <ThreadPoll poll={thread.poll} />
      </div>

      <hr className="border-[#CCCCCC]" />

      <ThreadStats
        threadId={thread.id}
        comments={thread.comments_count}
        views={thread.views}
        votes={thread.upvotes}
        hasVoted={thread.has_voted}
        isFollowing={thread.is_following}
        onVoted={(vote) => onThreadUpdated?.(vote)}
        onFollowingChange={(is_following) => onThreadUpdated?.({ is_following })}
      >
        {isOwner ? (
          <>
            <ThreadShareButton className="size-[18px] rounded-none hover:bg-transparent md:hidden" />
            <ThreadActionsMenu
              thread={thread}
              onUpdated={onThreadUpdated}
              className="md:hidden [&_.thread-actions-trigger]:size-5 [&_.thread-actions-trigger]:rounded-none [&_.thread-actions-trigger]:hover:bg-transparent"
            />
          </>
        ) : (
          <>
            <ThreadShareButton className="size-[18px] rounded-none hover:bg-transparent md:hidden" />
            <ThreadActionsMenu
              thread={thread}
              onUpdated={onThreadUpdated}
              className="md:hidden [&_.thread-actions-trigger]:size-[18px] [&_.thread-actions-trigger]:rounded-none [&_.thread-actions-trigger]:hover:bg-transparent"
            />
          </>
        )}
      </ThreadStats>
    </article>
  );
}
