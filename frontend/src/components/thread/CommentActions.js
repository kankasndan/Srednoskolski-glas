"use client";

import Image from "next/image";
import { useState } from "react";
import { toggleCommentVote } from "@/api/comments";
import ThreadShareButton from "@/components/thread/ThreadShareButton";
import { nextVoteState } from "@/lib/votes";

function IconButton({ icon, iconClassName = "", label, onClick }) {
  return (
    <button
      type="button"
      aria-label={label}
      onClick={onClick}
      className="grid size-5 shrink-0 cursor-pointer place-items-center text-[#595959] transition-colors hover:text-black"
    >
      <Image
        src={icon}
        alt=""
        width={20}
        height={20}
        className={iconClassName}
      />
    </button>
  );
}

function ToggleRepliesButton({ collapsed, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      className="flex cursor-pointer items-center gap-1.5 text-[12px] leading-none text-[#595959] transition-colors hover:text-black"
    >
      <Image
        src="/comments icon/show less arroew.svg"
        alt=""
        width={16}
        height={16}
        className={`size-4 ${collapsed ? "rotate-180" : ""}`}
      />
      {collapsed ? "Прикажи повеќе" : "Прикажи помалку"}
    </button>
  );
}

export default function CommentActions({
  commentId,
  votes: initialVotes = 0,
  hasVoted: initialHasVoted = false,
  hasReplies,
  collapsed,
  onToggle,
  onReply,
  onReport,
  createdAtLabel,
}) {
  const [votes, setVotes] = useState(initialVotes);
  const [hasVoted, setHasVoted] = useState(Boolean(initialHasVoted));
  const [busy, setBusy] = useState(false);

  async function handleVote() {
    if (!commentId || busy) return;

    const { previousVotes, previousHasVoted, nextVotes, nextHasVoted } =
      nextVoteState(votes, hasVoted);

    setBusy(true);
    setVotes(nextVotes);
    setHasVoted(nextHasVoted);

    try {
      const data = await toggleCommentVote(commentId);
      setVotes(data.upvotes ?? nextVotes);
      setHasVoted(Boolean(data.has_voted));
    } catch {
      setVotes(previousVotes);
      setHasVoted(previousHasVoted);
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
    <div className="flex flex-col items-start gap-3">
      <div className="flex flex-wrap items-center gap-4">
        <button
          type="button"
          disabled={busy}
          onClick={handleVote}
          className={`flex h-6 w-[58px] cursor-pointer items-center justify-center gap-2 rounded-lg border px-4 py-2 text-[12px] leading-none transition-colors disabled:opacity-70 ${
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

        <div className="flex h-5 w-[72px] shrink-0 items-center gap-1.5">
          <IconButton
            icon="/comments icon/comment.svg"
            iconClassName="size-5"
            label="Одговори"
            onClick={onReply}
          />
          <IconButton
            icon="/comments icon/report.svg"
            iconClassName="size-5"
            label="Пријави"
            onClick={onReport}
          />
          <ThreadShareButton
            className="-ml-[5px] size-5 rounded-none hover:bg-transparent [&_img]:size-5"
            getUrl={commentUrl}
            successMessage="Линкот до коментарот е успешно копиран."
            errorMessage="Линкот до коментарот не успеа да се копира."
          />
        </div>

        {createdAtLabel ? (
          <span className="font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-[18px] text-[#595959]">
            {createdAtLabel}
          </span>
        ) : null}
      </div>

      {hasReplies ? (
        <ToggleRepliesButton collapsed={collapsed} onClick={onToggle} />
      ) : null}
    </div>
  );
}
