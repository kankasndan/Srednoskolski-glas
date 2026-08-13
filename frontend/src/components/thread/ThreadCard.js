"use client";

import Image from "next/image";
import { useState } from "react";
import { useRouter } from "next/navigation";
import { toggleThreadVote } from "@/api/threads";
import ThreadAttachments from "@/components/thread/ThreadAttachments";
import ThreadMetaTags, { buildThreadMetaTags } from "@/components/thread/ThreadMetaTags";
import ThreadPoll from "@/components/thread/ThreadPoll";
import ThreadViewCount from "@/components/thread/ThreadViewCount";
import { formatCount } from "@/lib/formatCount";
import { stripHtml } from "@/lib/html";
import { formatPostedAgo } from "@/lib/time";
import { nextVoteState } from "@/lib/votes";

function ActionButton({ icon, label, count, onClick, active = false, compact = false }) {
  const sizeClassName = compact
    ? "h-8 w-[72px] gap-2 rounded-xl text-[12px]"
    : "h-10 w-24 gap-4 rounded-2xl text-[14px]";
  const baseClassName = `group flex cursor-pointer items-center justify-center border font-[family-name:var(--font-manrope)] font-normal leading-none transition-colors ${sizeClassName}`;
  const iconSize = compact ? "size-4" : "size-6";

  // Celata kartica e klikabilna, pa klikot na kopce zapira ovde za da ne ja otvori diskusijata.
  function handleClick(event) {
    event.stopPropagation();
    onClick?.(event);
  }

  return (
    <button
      onClick={handleClick}
      type="button"
      aria-label={label}
      className={
        active
          ? `${baseClassName} border-[var(--color-primary-100)] bg-[var(--color-primary-100)] text-white hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-200)]`
          : `${baseClassName} border-[#CCCCCC] text-black opacity-80 hover:border-[var(--color-primary-100)] hover:bg-[var(--color-primary-100)] hover:text-white hover:opacity-100 active:border-[var(--color-primary-100)] active:bg-[var(--color-primary-100)] active:text-white active:opacity-100`
      }
    >
      <Image
        src={icon}
        alt=""
        width={24}
        height={24}
        className={
          active
            ? `${iconSize} -scale-y-100 white-icon`
            : `${iconSize} transition group-hover:brightness-0 group-hover:invert group-active:brightness-0 group-active:invert`
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
  const [opening, setOpening] = useState(false);
  const router = useRouter();
  const threadHref = `/p/${thread.forum.slug}/${thread.id}`;
  const hasAttachments = (thread.attachments?.length ?? 0) > 0;
  const hasPoll = Boolean(thread.poll);

  async function upvote() {
    if (voting) return;

    const { previousVotes, previousHasVoted, nextVotes, nextHasVoted } =
      nextVoteState(upvotes, hasVoted);

    setVoting(true);
    setUpvotes(nextVotes);
    setHasVoted(nextHasVoted);

    try {
      const data = await toggleThreadVote(thread.id);
      setUpvotes(data.upvotes ?? nextVotes);
      setHasVoted(Boolean(data.has_voted));
    } catch {
      setUpvotes(previousVotes);
      setHasVoted(previousHasVoted);
    } finally {
      setVoting(false);
    }
  }

  // Ostanuva sino se dodeka ne se otvori novata strana.
  function openThread() {
    setOpening(true);
    router.push(threadHref);
  }

  return (
    <article
      onClick={openThread}
      className={`relative flex cursor-pointer flex-col items-start justify-center gap-4 rounded-3xl border-b border-b-[#CFE9ED] px-2 pb-6 pt-4 transition-colors active:bg-[#DCEBED] md:px-4 lg:p-4 lg:pt-6 lg:hover:bg-[#DCEBED] ${
        opening ? "bg-[#DCEBED]" : "bg-transparent"
      }`}
    >
      <div className="flex w-full items-start justify-between gap-8">
        <div className="flex min-w-0 flex-1 flex-col gap-4">
          <ThreadMetaTags
            tags={buildThreadMetaTags(thread.forum, thread)}
            postedAgo={formatPostedAgo(thread.created_at)}
            forumOnlyOnMobile
          />

          <div className="flex w-full min-w-0 flex-col gap-2">
            <h3 className="font-[family-name:var(--font-manrope)] text-[16px] font-bold text-black md:text-[18px] lg:w-fit lg:max-w-full lg:overflow-hidden lg:text-ellipsis lg:whitespace-nowrap lg:text-[20px] lg:leading-[27px]">
              {thread.title}
            </h3>
            {thread.description ? (
              <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal text-[#595959] lg:leading-[22px]">
                {stripHtml(thread.description)}
              </p>
            ) : null}
            <div className="hidden lg:block">
              <ThreadViewCount views={thread.views} />
            </div>
          </div>
        </div>

        <div className="relative z-10 hidden shrink-0 self-center flex-col gap-2 lg:flex">
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

      <div className="relative z-10 flex flex-col gap-4 lg:hidden">
        <ThreadViewCount views={thread.views} />

        <div className="flex items-center gap-2">
          <ActionButton
            compact
            icon="/Chevrons up.svg"
            label="Гласај нагоре"
            count={upvotes}
            onClick={upvote}
            active={hasVoted}
          />
          <ActionButton
            compact
            icon="/chat-1-line.svg"
            label="Коментари"
            count={thread.comments_count}
            onClick={openThread}
          />
        </div>
      </div>
    </article>
  );
}
