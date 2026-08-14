"use client";

import Link from "next/link";
import { useState } from "react";
import { createComment } from "@/api/comments";
import { useProfile } from "@/hooks/useProfile";
import { canCreateComments } from "@/lib/capabilities";

const MAX_COMMENT_LENGTH = 1000;

export default function CommentComposer({
  threadId,
  parentId = null,
  compact = false,
  onClose,
  onCreated,
}) {
  const { user, loading: profileLoading } = useProfile();
  const [comment, setComment] = useState("");
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const isEmpty = comment.trim() === "";
  const allowed = canCreateComments(user);

  async function handleSubmit(event) {
    event.preventDefault();
    if (isEmpty || busy || !threadId || !allowed) return;

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
      if (err.status === 403) {
        setError(err.message || "Немаш дозвола да коментираш.");
      } else if (err.status === 401) {
        setError("Мора да си најавен за да коментираш.");
      } else {
        setError(err.message || "Неуспешно објавување. Обиди се повторно.");
      }
    } finally {
      setBusy(false);
    }
  }

  if (profileLoading) {
    return null;
  }

  if (!allowed) {
    return (
      <div
        className={
          compact
            ? "rounded-xl border border-[#CCCCCC] bg-[#F7F7F7] px-4 py-3 text-[13px] text-[#595959]"
            : "rounded-3xl border border-[#CCCCCC] bg-[#F7F7F7] p-6 text-[14px] text-[#595959]"
        }
      >
        {user == null ? (
          <>
            <Link href="/login" className="font-bold text-[#582FF5] hover:underline">
              Најави се
            </Link>{" "}
            за да коментираш.
          </>
        ) : (
          "Заврши го onboarding процесот за да можеш да коментираш."
        )}
      </div>
    );
  }

  return (
    <form
      onSubmit={handleSubmit}
      className={
        compact
          ? "flex flex-col gap-2"
          : "flex w-full max-w-[342px] flex-col gap-2 rounded-3xl border border-[#CFE9ED] bg-white p-6 sm:max-w-full md:gap-4"
      }
    >
      {compact ? null : (
        <h2 className="h-5 text-[16px] font-bold leading-[19.5px] text-black">
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
            : "Употреби @ за да означиш некого..."
        }
        aria-label={compact ? "Одговор" : "Коментар"}
        autoFocus={compact}
        disabled={busy}
        className={`resize-none rounded-[14px] border border-[#CCCCCC] p-3 text-[14px] leading-6 text-black outline-none transition-colors placeholder:text-[#595959] focus:border-[#582FF5] disabled:opacity-60 ${
          compact ? "h-20" : "h-20 md:h-32"
        }`}
      />

      {error ? (
        <p className="text-[13px] text-[#DC2626]">{error}</p>
      ) : null}

      <div
        className={`flex gap-4 ${
          compact
            ? "items-center justify-end"
            : "flex-col items-start justify-between sm:flex-row sm:items-center"
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
            href="/rules"
            className="h-[18px] cursor-pointer text-[12px] leading-[18px] text-[#595959] underline underline-offset-[4px] transition-colors hover:text-black"
          >
            Внимавај на правилата на заедницата.
          </Link>
        )}

        <button
          type="submit"
          disabled={isEmpty || busy}
          className={`shrink-0 cursor-pointer rounded-xl bg-[#582FF5] font-bold leading-none text-white transition-colors hover:bg-[#3300F5] disabled:cursor-not-allowed disabled:bg-[#CCCCCC] ${
            compact ? "h-9 px-5 text-[12px]" : "h-10 w-full px-4 text-[14px] sm:w-36"
          } flex items-center justify-center gap-4`}
        >
          {busy ? "…" : compact ? "Објави" : "Објави коментар"}
        </button>
      </div>
    </form>
  );
}
