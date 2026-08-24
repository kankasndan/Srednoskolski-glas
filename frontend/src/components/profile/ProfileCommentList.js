"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { formatDistanceToNow } from "date-fns";
import { mk } from "date-fns/locale";
import { deleteComment, updateComment } from "@/api/comments";
import ConfirmDialog from "@/components/ui/ConfirmDialog";
import EditCommentDialog from "@/components/thread/EditCommentDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import CommentBody from "@/components/thread/CommentBody";
import ProfileForumTag from "@/components/profile/ProfileForumTag";
import ThreadActionButton from "@/components/thread/ThreadActionButton";
import { getMyComments, getUserComments } from "@/api/profile";
import { userFacingError } from "@/lib/api";

function postedAgoLabel(comment) {
  const ago = formatDistanceToNow(new Date(comment.edited_at ?? comment.created_at), {
    addSuffix: true,
    locale: mk,
  });

  return comment.edited_at ? `Изменето ${ago}` : ago;
}

function ProfileCommentItem({ comment: initialComment, onDeleted, canManage = true }) {
  const [comment, setComment] = useState(initialComment);
  const [editing, setEditing] = useState(false);
  const [saved, setSaved] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);
  const [deleted, setDeleted] = useState(false);
  const [busy, setBusy] = useState(false);
  const [actionError, setActionError] = useState("");

  const thread = comment.thread;
  const threadHref = `/p/${thread.forum.slug}/${thread.id}`;

  async function handleSave({ content }) {
    const updated = await updateComment(comment.id, { content });

    setComment((prev) => ({
      ...prev,
      content: updated.content,
      edited_at: updated.edited_at,
      mentions: updated.mentions ?? prev.mentions,
    }));
    setEditing(false);
    setSaved(true);
  }

  async function handleConfirmDelete() {
    if (busy) return;

    setBusy(true);
    setActionError("");

    try {
      await deleteComment(comment.id);
      setConfirmingDelete(false);
      setDeleted(true);
    } catch (err) {
      setActionError(userFacingError(err, "Неуспешно бришење. Обиди се повторно."));
      setConfirmingDelete(false);
    } finally {
      setBusy(false);
    }
  }

  return (
    <article className="relative flex cursor-pointer flex-col gap-4 rounded-3xl border-b border-b-[#CFE9ED] px-2 pb-6 transition-colors active:bg-[#DCEBED] hover:bg-[#DCEBED] md:px-4 md:py-5">
      <Link
        href={threadHref}
        aria-label={thread.title}
        className="absolute inset-0 rounded-3xl"
      />

      <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between md:gap-8">
        <div className="flex min-w-0 flex-col gap-4">
          <div className="flex items-center gap-2">
            <ProfileForumTag forum={thread.forum} />
            <span className="font-(family-name:--font-roboto) text-[12px] leading-4 text-black">
              {postedAgoLabel(comment)}
            </span>
          </div>

          <div className="flex min-w-0 flex-col gap-2 md:gap-4">
            <h3 className="break-words font-(family-name:--font-manrope) text-[16px] leading-snug text-black">
              {thread.title}
            </h3>

            <div className="flex min-w-0 items-start gap-1.5">
              <span
                aria-hidden
                className="font-(family-name:--font-manrope) text-[12px] leading-snug text-(--color-grays-700)"
              >
                ↳
              </span>
              <div className="flex min-w-0 flex-1 flex-col gap-2">
                {comment.content ? (
                  <CommentBody
                    text={comment.content}
                    mentions={comment.mentions}
                    muted
                    className="min-w-0 font-(family-name:--font-manrope) text-[12px] leading-snug"
                  />
                ) : null}
                {comment.gif_url ? (
                  <img
                    src={comment.gif_url}
                    alt="GIF"
                    className="max-w-40 rounded-xl"
                  />
                ) : null}
              </div>
            </div>
          </div>
        </div>

        <div className="relative z-10 flex shrink-0">
          <ThreadActionButton
            compact
            icon="/Chevrons up.svg"
            label="Гласови"
            count={comment.upvotes ?? 0}
            href={threadHref}
          />
        </div>
      </div>

      {canManage ? (
        <div className="relative z-10 flex flex-col gap-2">
          {/* Na telefon se obichen tekst, na pogolem ekran se kopcinja so ramka. */}
          <div className="flex flex-wrap items-center gap-4 md:gap-3">
            <button
              type="button"
              disabled={busy}
              onClick={() => setEditing(true)}
              className="cursor-pointer font-(family-name:--font-manrope) text-[12px] font-medium text-(--color-grays-400) transition-colors active:text-(--color-primary-200) disabled:cursor-not-allowed disabled:opacity-50 md:rounded-xl md:border md:border-(--color-primary-200) md:bg-white md:px-4 md:py-2.5 md:text-[14px] md:font-bold md:text-(--color-primary-200) md:hover:bg-[#EDE9FE] md:active:bg-[#EDE9FE]"
            >
              Измени
            </button>
            <button
              type="button"
              disabled={busy}
              onClick={() => {
                setActionError("");
                setConfirmingDelete(true);
              }}
              className="cursor-pointer font-(family-name:--font-manrope) text-[12px] font-medium text-(--color-grays-400) transition-colors active:text-[#DC2626] disabled:cursor-not-allowed disabled:opacity-50 md:rounded-xl md:border md:border-[#DC2626] md:bg-white md:px-4 md:py-2.5 md:text-[14px] md:font-bold md:text-[#DC2626] md:hover:bg-[#FEF2F2] md:active:bg-[#FEF2F2]"
            >
              Избриши
            </button>
          </div>
          {actionError ? (
            <p className="font-(family-name:--font-manrope) text-[13px] text-[#DC2626]">
              {actionError}
            </p>
          ) : null}
        </div>
      ) : null}

      {canManage && editing && (
        <EditCommentDialog
          open
          comment={comment}
          onClose={() => setEditing(false)}
          onSave={handleSave}
        />
      )}

      <InfoDialog
        open={saved}
        title="Промените беа успешно зачувани."
        onClose={() => setSaved(false)}
      />

      <ConfirmDialog
        open={canManage && confirmingDelete}
        title="Дали си сигурен дека сакаш да го избришеш овој коментар?"
        confirmLabel={busy ? "Се брише…" : "Избриши"}
        onCancel={() => {
          if (!busy) setConfirmingDelete(false);
        }}
        onConfirm={handleConfirmDelete}
      />

      <InfoDialog
        open={deleted}
        title="Коментарот беше успешно избришан."
        onClose={() => {
          setDeleted(false);
          onDeleted?.(comment.id);
        }}
      />
    </article>
  );
}

export default function ProfileCommentList({
  username = null,
  isOwnProfile = true,
}) {
  const [comments, setComments] = useState(null);

  useEffect(() => {
    let active = true;

    const loader = username ? getUserComments(username) : getMyComments();

    loader
      .then((data) => {
        if (active) setComments(data);
      })
      .catch(() => {
        if (active) setComments([]);
      });

    return () => {
      active = false;
    };
  }, [username]);

  function handleDeleted(commentId) {
    setComments((prev) => (prev ?? []).filter((comment) => comment.id !== commentId));
  }

  if (comments === null) {
    return <p className="text-[16px] text-[#595959]">Се вчитува…</p>;
  }

  if (comments.length === 0) {
    return (
      <p className="text-[16px] text-[#595959]">
        {isOwnProfile
          ? "Сè уште немаш напишано коментари."
          : "Овој корисник сè уште нема напишано коментари."}
      </p>
    );
  }

  return (
    <div className="flex flex-col gap-10 md:gap-0">
      {comments.map((comment) => (
        <ProfileCommentItem
          key={comment.id}
          comment={comment}
          onDeleted={handleDeleted}
          canManage={isOwnProfile}
        />
      ))}
    </div>
  );
}
