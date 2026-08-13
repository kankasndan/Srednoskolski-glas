"use client";

import Image from "next/image";
import { useState } from "react";
import { toggleCommentVote } from "@/api/comments";
import { ONBOARDING_REQUIRED_MESSAGE } from "@/lib/capabilities";
import { nextVoteState } from "@/lib/votes";

function ActionButton({ icon, iconClassName = "", label, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="flex cursor-pointer items-center gap-1.5 text-[12px] leading-none text-[#595959] transition-colors hover:text-black"
    >
      <Image
        src={icon}
        alt=""
        width={16}
        height={16}
        className={`size-4 ${iconClassName}`}
      />
      {label}
    </button>
  );
}

export default function CommentActions({
  commentId,
  votes: initialVotes = 0,
  hasVoted: initialHasVoted = false,
  repliesCount = 0,
  expanded,
  loadingReplies = false,
  onToggle,
  onReply,
  onReport,
}) {
  const [votes, setVotes] = useState(initialVotes);
  const [hasVoted, setHasVoted] = useState(Boolean(initialHasVoted));
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");

  async function handleVote() {
    if (!commentId || busy) return;

    const { previousVotes, previousHasVoted, nextVotes, nextHasVoted } =
      nextVoteState(votes, hasVoted);

    setBusy(true);
    setError("");
    setVotes(nextVotes);
    setHasVoted(nextHasVoted);

    try {
      const data = await toggleCommentVote(commentId);
      setVotes(data.upvotes ?? nextVotes);
      setHasVoted(Boolean(data.has_voted));
    } catch (err) {
      setVotes(previousVotes);
      setHasVoted(previousHasVoted);
      if (err?.status === 403) {
        setError(err.message || ONBOARDING_REQUIRED_MESSAGE);
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="flex flex-col gap-1">
      <div className="flex items-center gap-4">
        <button
          type="button"
          disabled={busy}
          onClick={handleVote}
          className={`flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2 text-[12px] leading-none transition-colors disabled:opacity-70 ${
            hasVoted
              ? "border-[var(--color-primary-100)] bg-[var(--color-primary-100)] text-white"
              : "border-[#CCCCCC] text-black opacity-80 hover:border-[var(--color-primary-100)]"
          }`}
        >
          <Image
            src="/Chevrons up.svg"
            alt=""
            width={16}
            height={16}
            className={`size-4 ${hasVoted ? "brightness-0 invert" : ""}`}
          />
          {votes}
        </button>

        <ActionButton
          icon="/comments icon/comment.svg"
          label="Одговори"
          onClick={onReply}
        />
        <ActionButton
          icon="/comments icon/report.svg"
          label="Пријави"
          onClick={onReport}
        />

        {repliesCount > 0 ? (
          <ActionButton
            icon="/comments icon/show less arroew.svg"
            iconClassName={expanded ? "" : "rotate-180"}
            label={
              loadingReplies
                ? "Се вчитува…"
                : expanded
                  ? "Сокриј одговори"
                  : `Прикажи одговори (${repliesCount})`
            }
            onClick={onToggle}
          />
        ) : null}
      </div>
      {error ? (
        <p className="font-[family-name:var(--font-manrope)] text-[12px] leading-4 text-[#DC2626]">
          {error}
        </p>
      ) : null}
    </div>
  );
}
