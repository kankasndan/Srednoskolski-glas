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
<<<<<<< HEAD
            href="/rules"
            className="text-[12px] leading-[18px] text-[#595959] underline underline-offset-[3px]"
=======
            href={`/p/${forumSlug}`}
            className="cursor-pointer text-[12px] leading-[18px] text-[#595959] underline underline-offset-[3px] transition-colors hover:text-black"
>>>>>>> c42688fb1c9b1329ab49ece69ce477207ea48cf1
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
