"use client";

import Image from "next/image";
import { useState } from "react";
import { toggleThreadVote } from "@/api/threads";
import FollowThreadButton from "@/components/thread/FollowThreadButton";
import { formatCount } from "@/lib/formatCount";
import { nextVoteState } from "@/lib/votes";

function Stat({ icon, label, count }) {
  return (
    <div className="flex h-10 w-24 items-center justify-center gap-4 rounded-xl border border-[#CCCCCC] opacity-80">
      <Image src={icon} alt="" width={24} height={24} className="size-6" />
      <span className="text-[14px] leading-none text-black">
        <span className="sr-only">{label}: </span>
        {formatCount(count)}
      </span>
    </div>
  );
}

function VoteStat({ threadId, votes: initialVotes = 0, hasVoted: initialHasVoted = false, onVoted }) {
  const [votes, setVotes] = useState(initialVotes);
  const [hasVoted, setHasVoted] = useState(Boolean(initialHasVoted));
  const [busy, setBusy] = useState(false);

  async function handleVote() {
    if (!threadId || busy) return;

    const { previousVotes, previousHasVoted, nextVotes, nextHasVoted } =
      nextVoteState(votes, hasVoted);

    setBusy(true);
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
    } catch {
      setVotes(previousVotes);
      setHasVoted(previousHasVoted);
      onVoted?.({ upvotes: previousVotes, has_voted: previousHasVoted });
    } finally {
      setBusy(false);
    }
  }

  return (
    <button
      type="button"
      disabled={busy}
      aria-pressed={hasVoted}
      aria-label="Гласај нагоре"
      onClick={handleVote}
      className={`flex h-10 w-24 cursor-pointer items-center justify-center gap-4 rounded-xl border transition-colors disabled:opacity-70 ${
        hasVoted
          ? "border-[var(--color-primary-100)] bg-[var(--color-primary-100)] text-white"
          : "border-[#CCCCCC] text-black opacity-80 hover:border-[var(--color-primary-100)] hover:bg-[var(--color-primary-100)] hover:text-white hover:opacity-100"
      }`}
    >
      <Image
        src="/Chevrons up.svg"
        alt=""
        width={24}
        height={24}
        className={`size-6 ${hasVoted ? "-scale-y-100 brightness-0 invert" : ""}`}
      />
      <span className="text-[14px] leading-none">
        <span className="sr-only">Гласови: </span>
        {formatCount(votes)}
      </span>
    </button>
  );
}

export default function ThreadStats({
  threadId,
  views,
  comments,
  votes,
  hasVoted = false,
  isFollowing = false,
  onVoted,
  onFollowingChange,
}) {
  return (
    <div className="flex flex-wrap items-start gap-2">
      <Stat icon="/eye-line.svg" label="Прегледи" count={views} />
      <Stat icon="/chat-1-line.svg" label="Коментари" count={comments} />
      <VoteStat
        threadId={threadId}
        votes={votes}
        hasVoted={hasVoted}
        onVoted={onVoted}
      />
      <FollowThreadButton
        threadId={threadId}
        initialFollowing={isFollowing}
        onFollowingChange={onFollowingChange}
      />
    </div>
  );
}
