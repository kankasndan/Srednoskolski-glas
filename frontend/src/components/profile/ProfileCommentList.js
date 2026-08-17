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

function ProfileCommentItem({ comment: initialComment, onDeleted, canManage = true }) {
  const [comment, setComment] = useState(initialComment);
  const [editing, setEditing] = useState(false);
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
    <article className="relative flex cursor-pointer items-center justify-between gap-8 rounded-3xl border-b border-b-[#CFE9ED] px-4 py-5 transition-colors hover:bg-[#DCEBED]">
      <Link
        href={threadHref}
        aria-label={thread.title}
        className="absolute inset-0 rounded-3xl"
      />

      <div className="flex min-w-0 flex-col gap-4">
        <div className="flex items-center gap-2">
          <ProfileForumTag forum={thread.forum} />
          <span className="font-(family-name:--font-roboto) text-[12px] leading-4 text-[#595959]">
            {formatDistanceToNow(new Date(comment.created_at), {
              addSuffix: true,
              locale: mk,
            })}
          </span>
        </div>

          <CommentBody
            text={comment.content}
            mentions={comment.mentions}
            className="font-(family-name:--font-manrope) text-[16px] leading-none"
          />

        <div className="flex items-center gap-1.5 font-(family-name:--font-manrope) text-[12px] leading-none text-(--color-grays-700)">
          <span aria-hidden>↳</span>
          <span className="truncate">{thread.title}</span>
        </div>

        {canManage ? (
          <div className="relative z-10 flex flex-col gap-2">
            <div className="flex flex-wrap items-center gap-3">
              <button
                type="button"
                disabled={busy}
                onClick={() => setEditing(true)}
                className="cursor-pointer rounded-xl border border-(--color-primary-200) bg-white px-4 py-2.5 font-(family-name:--font-manrope) text-[14px] font-bold text-(--color-primary-200) transition-colors hover:bg-[#EDE9FE] disabled:cursor-not-allowed disabled:opacity-50"
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
                className="cursor-pointer rounded-xl border border-[#DC2626] bg-white px-4 py-2.5 font-(family-name:--font-manrope) text-[14px] font-bold text-[#DC2626] transition-colors hover:bg-[#FEF2F2] disabled:cursor-not-allowed disabled:opacity-50"
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
      </div>

      <div className="relative z-10 flex shrink-0">
        <ThreadActionButton
          icon="/Chevrons up.svg"
          label="Гласови"
          count={comment.upvotes ?? 0}
          href={threadHref}
        />
      </div>

      {canManage && editing && (
        <EditCommentDialog
          open
          comment={comment}
          onClose={() => setEditing(false)}
          onSave={handleSave}
        />
      )}

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
    <div className="flex flex-col">
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
