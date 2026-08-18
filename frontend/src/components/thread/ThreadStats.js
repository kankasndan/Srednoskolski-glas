"use client";

import Image from "next/image";
import { useState } from "react";
import { toggleThreadVote } from "@/api/threads";
import FollowThreadButton from "@/components/thread/FollowThreadButton";
import ThreadViewCount from "@/components/thread/ThreadViewCount";
import { ONBOARDING_REQUIRED_MESSAGE } from "@/lib/capabilities";
import { formatCount } from "@/lib/formatCount";
import { nextVoteState } from "@/lib/votes";

function Stat({ icon, label, count, onClick }) {
  const interactive = typeof onClick === "function";
  const className =
    "group flex h-8 w-[72px] items-center justify-center gap-2 rounded-xl border border-[#CCCCCC] font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-none text-black opacity-80 transition-colors md:h-10 md:w-24 md:gap-4 md:rounded-2xl md:text-[14px]";
  const hoverClassName = interactive
    ? "cursor-pointer hover:border-[var(--color-primary-100)] hover:bg-[var(--color-primary-100)] hover:text-white hover:opacity-100"
    : "";
  const inner = (
    <>
      <Image
        src={icon}
        alt=""
        width={24}
        height={24}
        className={`size-4 md:size-6 ${interactive ? "transition group-hover:brightness-0 group-hover:invert" : ""}`}
      />
      <span>
        <span className="sr-only">{label}: </span>
        {formatCount(count)}
      </span>
    </>
  );

  if (interactive) {
    return (
      <button
        type="button"
        aria-label={label}
        onClick={onClick}
        className={`${className} ${hoverClassName}`}
      >
        {inner}
      </button>
    );
  }

  return (
    <div className={className}>
      {inner}
    </div>
  );
}

function VoteStat({ threadId, votes: initialVotes = 0, hasVoted: initialHasVoted = false, onVoted }) {
  const [votes, setVotes] = useState(initialVotes);
  const [hasVoted, setHasVoted] = useState(Boolean(initialHasVoted));
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  async function handleVote() {
    if (!threadId || busy) return;

    const { previousVotes, previousHasVoted, nextVotes, nextHasVoted } =
      nextVoteState(votes, hasVoted);

    setBusy(true);
    setError("");
    setVotes(nextVotes);
    setHasVoted(nextHasVoted);
    onVoted?.({ upvotes: nextVotes, has_voted: nextHasVoted });

    try {
      const data = await toggleThreadVote(threadId);
      const serverVotes = data.upvotes ?? nextVotes;
      const serverHasVoted = Boolean(data.has_voted);

      setVotes(serverVotes);
      setHasVoted(serverHasVoted);
      onVoted?.({ upvotes: serverVotes, has_voted: serverHasVoted });
    } catch (err) {
      setVotes(previousVotes);
      setHasVoted(previousHasVoted);
      onVoted?.({ upvotes: previousVotes, has_voted: previousHasVoted });
      if (err?.status === 403) {
        setError(err.message || ONBOARDING_REQUIRED_MESSAGE);
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="flex flex-col gap-1">
      <button
        type="button"
        disabled={busy}
        aria-pressed={hasVoted}
        aria-label="Гласај нагоре"
        onClick={handleVote}
        className={`group flex h-8 w-[72px] cursor-pointer items-center justify-center gap-2 rounded-xl border font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-none transition-colors disabled:opacity-70 md:h-10 md:w-24 md:gap-4 md:rounded-2xl md:text-[14px] ${
          hasVoted
            ? "border-[var(--color-primary-100)] bg-[var(--color-primary-100)] text-white hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-200)]"
            : "border-[#CCCCCC] text-black opacity-80 hover:border-[var(--color-primary-100)] hover:bg-[var(--color-primary-100)] hover:text-white hover:opacity-100"
        }`}
      >
        <Image
          src="/Chevrons up.svg"
          alt=""
          width={24}
          height={24}
          className={`size-4 transition md:size-6 ${
            hasVoted
              ? "-scale-y-100 brightness-0 invert"
              : "group-hover:brightness-0 group-hover:invert"
          }`}
        />
        <span>
          <span className="sr-only">Гласови: </span>
          {formatCount(votes)}
        </span>
      </button>
      {error ? (
        <p className="max-w-[220px] font-[family-name:var(--font-manrope)] text-[12px] leading-4 text-[#DC2626]">
          {error}
        </p>
      ) : null}
    </div>
  );
}

export default function ThreadStats({
  threadId,
  comments,
  views,
  votes,
  hasVoted = false,
  isFollowing = false,
  onVoted,
  onFollowingChange,
  children,
}) {
  function scrollToComments() {
    document.getElementById("comments")?.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  }

  return (
    <div className="flex w-full flex-col gap-4 md:flex-row md:items-start md:justify-between">
      <div className="flex w-full items-center md:flex-wrap md:gap-2">
        <div className="flex items-center gap-2">
          <VoteStat
            threadId={threadId}
            votes={votes}
            hasVoted={hasVoted}
            onVoted={onVoted}
          />
          <Stat
            icon="/chat-1-line.svg"
            label="Коментари"
            count={comments}
            onClick={scrollToComments}
          />
          <ThreadViewCount views={views} className="w-auto md:hidden" />
        </div>
        {children ? (
          <div className="ml-auto flex items-center gap-2 md:ml-0">
            {children}
          </div>
        ) : null}
      </div>

      <FollowThreadButton
        threadId={threadId}
        initialFollowing={isFollowing}
        onFollowingChange={onFollowingChange}
        wrapperClassName="w-full self-center md:w-[268px] md:self-start"
        className="w-full"
      />
    </div>
  );
}
