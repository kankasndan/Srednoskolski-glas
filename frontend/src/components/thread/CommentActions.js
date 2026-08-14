"use client";

import Image from "next/image";
import { useState } from "react";
import { toggleCommentVote } from "@/api/comments";
import ThreadShareButton from "@/components/thread/ThreadShareButton";
import { ONBOARDING_REQUIRED_MESSAGE } from "@/lib/capabilities";
import { nextVoteState } from "@/lib/votes";

function IconButton({ icon, iconClassName, label, onClick }) {
  return (
    <button
      type="button"
      aria-label={label}
      onClick={onClick}
      className="flex shrink-0 cursor-pointer items-center opacity-80 transition-opacity hover:opacity-100"
    >
      <Image src={icon} alt="" width={20} height={20} className={iconClassName} />
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
  createdAtLabel,
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

  function commentUrl() {
    const url = new URL(window.location.href);
    url.hash = `comment-${commentId}`;
    return url.toString();
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center gap-2">
        <button
          type="button"
          disabled={busy}
          onClick={handleVote}
          className={`flex h-6 w-[58px] cursor-pointer items-center justify-center gap-2 rounded-lg border px-4 py-2 font-[family-name:var(--font-manrope)] text-[12px] leading-none transition-colors disabled:opacity-70 ${
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

        <div className="flex items-center gap-4">
          <div className="flex items-center gap-2">
            <IconButton
              icon="/comments icon/comment.svg"
              iconClassName="size-5"
              label="Одговори"
              onClick={onReply}
            />
            <IconButton
              icon="/comments icon/report.svg"
              iconClassName="size-[18px]"
              label="Пријави"
              onClick={onReport}
            />
            <ThreadShareButton
              className="size-[18px] rounded-none opacity-80 hover:bg-transparent hover:opacity-100"
              getUrl={commentUrl}
              successMessage="Линкот до коментарот е успешно копиран."
              errorMessage="Линкот до коментарот не успеа да се копира."
            />
          </div>

          {createdAtLabel ? (
            <span className="font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-[18px] text-[#999999]">
              {createdAtLabel}
            </span>
          ) : null}
        </div>
      </div>

      {repliesCount > 0 ? (
        <button
            type="button"
            onClick={onToggle}
            className="flex cursor-pointer items-center gap-1 font-[family-name:var(--font-manrope)] text-[12px] leading-[18px] text-[#595959] transition-colors hover:text-black"
          >
            <Image
              src="/comments icon/show less arroew.svg"
              alt=""
              width={13}
              height={13}
              className={`size-[13px] ${expanded ? "" : "rotate-180"}`}
            />
            {loadingReplies
              ? "Се вчитува…"
              : expanded
                ? "Сокриј одговори"
                : `Прикажи одговори (${repliesCount})`}
        </button>
      ) : null}
      {error ? (
        <p className="font-[family-name:var(--font-manrope)] text-[12px] leading-4 text-[#DC2626]">
          {error}
        </p>
      ) : null}
    </div>
  );
}
