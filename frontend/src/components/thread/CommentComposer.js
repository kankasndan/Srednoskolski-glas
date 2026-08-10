"use client";

import Link from "next/link";
import { useState } from "react";
import { createComment } from "@/api/comments";

const MAX_COMMENT_LENGTH = 1000;

export default function CommentComposer({
  forumSlug,
  threadId,
  parentId = null,
  compact = false,
  onClose,
  onCreated,
}) {
  const [comment, setComment] = useState("");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const isEmpty = comment.trim() === "";

  async function handleSubmit(event) {
    event.preventDefault();
    if (isEmpty || busy || !threadId) return;

    setBusy(true);
    setError("");

    try {
      const created = await createComment(threadId, {
        content: comment.trim(),
        parentId,
      });

      setComment("");
      onCreated?.(created);
      onClose?.();
    } catch (err) {
      setError(err.message || "Неуспешно објавување. Обиди се повторно.");
    } finally {
      setBusy(false);
    }
  }

  return (
    <form
      onSubmit={handleSubmit}
      className={
        compact
          ? "flex flex-col gap-2"
          : "flex flex-col gap-4 rounded-3xl border border-[#CCCCCC] bg-white p-6"
      }
    >
      {compact ? null : (
        <h2 className="text-[16px] font-bold leading-none text-black">
          Остави коментар
        </h2>
      )}

      <textarea
        value={comment}
        onChange={(event) => setComment(event.target.value)}
        maxLength={MAX_COMMENT_LENGTH}
        placeholder={
          compact
            ? "Напиши одговор..."
            : "Сподели го своето мислење... Употреби @ за да означиш некого..."
        }
        aria-label={compact ? "Одговор" : "Коментар"}
        autoFocus={compact}
        disabled={busy}
        className={`resize-none rounded-xl border border-[#CCCCCC] p-4 text-[14px] leading-6 text-black outline-none transition-colors placeholder:text-[#595959] focus:border-[#582FF5] disabled:opacity-60 ${
          compact ? "h-20" : "h-32"
        }`}
      />

      {error ? (
        <p className="text-[13px] text-[#DC2626]">{error}</p>
      ) : null}

      <div
        className={`flex items-center gap-4 ${
          compact ? "justify-end" : "justify-between"
        }`}
      >
        {compact ? (
          <button
            type="button"
            onClick={onClose}
            disabled={busy}
            className="cursor-pointer text-[12px] leading-none text-[#595959] transition-colors hover:text-black disabled:opacity-50"
          >
            Откажи
          </button>
        ) : (
          <Link
            href={`/p/${forumSlug}`}
            className="cursor-pointer text-[12px] leading-[18px] text-[#595959] underline underline-offset-[3px]"
          >
            Внимавај на правилата на заедницата.
          </Link>
        )}

        <button
          type="submit"
          disabled={isEmpty || busy}
          className={`shrink-0 cursor-pointer rounded-xl bg-[#582FF5] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0] disabled:cursor-not-allowed disabled:bg-[#CCCCCC] ${
            compact ? "h-9 px-5 text-[12px]" : "h-10 w-36 text-[14px]"
          }`}
        >
          {busy ? "…" : "Објави"}
        </button>
      </div>
    </form>
  );
}
