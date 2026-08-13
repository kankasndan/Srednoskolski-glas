"use client";

import Image from "next/image";
import { useState } from "react";
import { useRouter } from "next/navigation";
import { toggleThreadVote } from "@/api/threads";
import ThreadAttachments from "@/components/thread/ThreadAttachments";
import ThreadMetaTags, { buildThreadMetaTags } from "@/components/thread/ThreadMetaTags";
import ThreadPoll from "@/components/thread/ThreadPoll";
import ThreadViewCount from "@/components/thread/ThreadViewCount";
import { ONBOARDING_REQUIRED_MESSAGE } from "@/lib/capabilities";
import { formatCount } from "@/lib/formatCount";
import { stripHtml } from "@/lib/html";
import { formatPostedAgo } from "@/lib/time";
import { nextVoteState } from "@/lib/votes";

function ActionButton({ icon, label, count, onClick, active = false }) {
  const baseClassName =
    "group flex h-10 w-24 cursor-pointer items-center justify-center gap-4 rounded-2xl border font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none transition-colors";

  return (
    <button
      onClick={onClick}
      type="button"
      aria-label={label}
      className={
        active
          ? `${baseClassName} border-[var(--color-primary-100)] bg-[var(--color-primary-100)] text-white hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-200)]`
          : `${baseClassName} border-[#CCCCCC] text-black opacity-80 hover:border-[var(--color-primary-100)] hover:bg-[var(--color-primary-100)] hover:text-white hover:opacity-100`
      }
    >
      <Image
        src={icon}
        alt=""
        width={24}
        height={24}
        className={
          active
            ? "size-6 -scale-y-100 white-icon"
            : "size-6 transition group-hover:brightness-0 group-hover:invert"
        }
      />
      {formatCount(count ?? 0)}
    </button>
  );
}

export default function ThreadCard({ thread }) {
  const [upvotes, setUpvotes] = useState(thread.upvotes ?? 0);
  const [hasVoted, setHasVoted] = useState(Boolean(thread.has_voted));
  const [voting, setVoting] = useState(false);
  const [voteError, setVoteError] = useState("");
  const router = useRouter();
  const threadHref = `/p/${thread.forum.slug}/${thread.id}`;
  const hasAttachments = (thread.attachments?.length ?? 0) > 0;
  const hasPoll = Boolean(thread.poll);

  async function upvote() {
    if (voting) return;

    const { previousVotes, previousHasVoted, nextVotes, nextHasVoted } =
      nextVoteState(upvotes, hasVoted);

    setVoting(true);
    setVoteError("");
    setUpvotes(nextVotes);
    setHasVoted(nextHasVoted);

    try {
      const data = await toggleThreadVote(thread.id);
      setUpvotes(data.upvotes ?? nextVotes);
      setHasVoted(Boolean(data.has_voted));
    } catch (err) {
      setUpvotes(previousVotes);
      setHasVoted(previousHasVoted);
      if (err?.status === 403) {
        setVoteError(err.message || ONBOARDING_REQUIRED_MESSAGE);
      }
    } finally {
      setVoting(false);
    }
  }

  function openThread() {
    router.push(threadHref);
  }

  return (
    <article className="relative flex flex-col gap-4 items-start justify-center bg-transparent border-b border-b-[#CFE9ED] p-4 pt-6 rounded-3xl transition-colors hover:bg-[#DCEBED]">
      <div className="flex w-full items-start justify-between gap-8">
        <div
          className="flex min-w-0 flex-1 cursor-pointer flex-col gap-4"
          onClick={openThread}
        >
          <ThreadMetaTags
            tags={buildThreadMetaTags(thread.forum, thread)}
            postedAgo={formatPostedAgo(thread.created_at)}
          />

          <div className="flex min-h-[57px] w-[681px] max-w-full flex-col gap-2">
            <h3 className="w-fit max-w-full overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-[27px] text-black">
              {thread.title}
            </h3>
            {thread.description ? (
              <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
                {stripHtml(thread.description)}
              </p>
            ) : null}
            <ThreadViewCount views={thread.views} />
          </div>
        </div>

        <div className="relative z-10 flex shrink-0 self-center flex-col gap-2">
          <ActionButton
            icon="/Chevrons up.svg"
            label="Гласај нагоре"
            count={upvotes}
            onClick={upvote}
            active={hasVoted}
          />
          <ActionButton
            icon="/chat-1-line.svg"
            label="Коментари"
            count={thread.comments_count}
            onClick={openThread}
          />
          {voteError ? (
            <p className="max-w-[120px] font-[family-name:var(--font-manrope)] text-[11px] leading-4 text-[#DC2626]">
              {voteError}
            </p>
          ) : null}
        </div>
      </div>

      {hasAttachments || hasPoll ? (
        <div
          className="relative z-10 flex w-full flex-col gap-4"
          onClick={(event) => event.stopPropagation()}
        >
          {hasAttachments ? (
            <ThreadAttachments attachments={thread.attachments} />
          ) : null}
          {hasPoll ? <ThreadPoll poll={thread.poll} /> : null}
        </div>
      ) : null}
    </article>
  );
}
