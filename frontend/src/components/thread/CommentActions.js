"use client";

import Image from "next/image";
import { useState } from "react";
import { toggleCommentVote } from "@/api/comments";

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
  hasReplies,
  collapsed,
  onToggle,
  onReply,
  onReport,
}) {
  const [votes, setVotes] = useState(initialVotes);
  const [hasVoted, setHasVoted] = useState(Boolean(initialHasVoted));
  const [busy, setBusy] = useState(false);

  async function handleVote() {
    if (!commentId || busy) return;

    setBusy(true);

    try {
      const data = await toggleCommentVote(commentId);
      setVotes(data.upvotes ?? votes);
      setHasVoted(Boolean(data.has_voted));
    } catch {
      // Keep previous vote state on failure.
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="flex items-center gap-4">
      <button
        type="button"
        disabled={busy}
        onClick={handleVote}
        className={`flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2 text-[12px] leading-none transition-colors disabled:opacity-60 ${
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

      {hasReplies ? (
        <ActionButton
          icon="/comments icon/show less arroew.svg"
          iconClassName={collapsed ? "rotate-180" : ""}
          label={collapsed ? "Прикажи повеќе" : "Прикажи помалку"}
          onClick={onToggle}
        />
      ) : null}
    </div>
  );
}
