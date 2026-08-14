"use client";

import dynamic from "next/dynamic";
import Link from "next/link";
import { useState } from "react";
import { formatDistanceToNow } from "date-fns";
import { mk } from "date-fns/locale";
import { deleteThread, updateThread } from "@/api/threads";
import ConfirmDialog from "@/components/ui/ConfirmDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import ProfileForumTag from "@/components/profile/ProfileForumTag";
import ThreadAttachments from "@/components/thread/ThreadAttachments";
import ThreadPoll from "@/components/thread/ThreadPoll";
import ThreadStatButtons from "@/components/thread/ThreadStatButtons";
import ThreadViewCount from "@/components/thread/ThreadViewCount";
import { stripHtml } from "@/lib/html";

const EditThreadDialog = dynamic(
  () => import("@/components/thread/EditThreadDialog"),
  { ssr: false },
);

export default function ProfileThreadItem({
  thread: initialThread,
  onDeleted,
  canManage = true,
}) {
  const [thread, setThread] = useState(initialThread);
  const [editing, setEditing] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);
  const [deleted, setDeleted] = useState(false);
  const [busy, setBusy] = useState(false);
  const [actionError, setActionError] = useState("");

  const href = `/p/${thread.forum.slug}/${thread.id}`;
  const hasAttachments = (thread.attachments?.length ?? 0) > 0;
  const hasPoll = Boolean(thread.poll);

  async function handleSave({ title, content, files, link, removeAttachmentIds }) {
    const updated = await updateThread(thread.id, {
      title,
      description: content,
      files,
      link,
      removeAttachmentIds,
    });

    setThread((prev) => ({
      ...prev,
      ...updated,
      is_edited: true,
      edited_at: updated.edited_at ?? updated.updated_at,
      forum: updated.forum ?? prev.forum,
      attachments: updated.attachments ?? [],
      poll: updated.poll ?? prev.poll,
    }));
    setEditing(false);
  }

  async function handleConfirmDelete() {
    if (busy) return;

    setBusy(true);
    setActionError("");

    try {
      await deleteThread(thread.id);
      setConfirmingDelete(false);
      setDeleted(true);
    } catch (err) {
      setActionError(err.message || "Неуспешно бришење. Обиди се повторно.");
      setConfirmingDelete(false);
    } finally {
      setBusy(false);
    }
  }

  return (
    <article className="relative flex cursor-pointer flex-col gap-4 rounded-3xl border-b border-b-[#CFE9ED] px-4 py-5 transition-colors hover:bg-[#DCEBED]">
      <Link href={href} aria-label={thread.title} className="absolute inset-0 rounded-3xl" />

      <div className="flex items-center justify-between gap-8">
        <div className="flex min-w-0 flex-col gap-4">
          <div className="flex items-center gap-2">
            <ProfileForumTag forum={thread.forum} />
            <span className="font-(family-name:--font-roboto) text-[12px] leading-4 text-[#595959]">
              {formatDistanceToNow(new Date(thread.created_at), {
                addSuffix: true,
                locale: mk,
              })}
            </span>
          </div>

          <div className="flex flex-col gap-2">
            <h3 className="w-fit max-w-full overflow-hidden text-ellipsis whitespace-nowrap font-(family-name:--font-manrope) text-[20px] font-bold leading-6.75 text-black">
              {thread.title}
            </h3>
            <p className="font-(family-name:--font-manrope) text-[16px] leading-5.5 text-[#595959]">
              {stripHtml(thread.description)}
            </p>
            <ThreadViewCount views={thread.views} />
          </div>
        </div>

        <ThreadStatButtons thread={thread} href={href} />
      </div>

      {hasAttachments || hasPoll ? (
        <div
          className="relative z-10 flex w-full flex-col gap-4"
          onClick={(event) => event.stopPropagation()}
        >
          {hasAttachments ? (
            <ThreadAttachments attachments={thread.attachments} />
          ) : null}
          {hasPoll ? <ThreadPoll poll={thread.poll} /> : null}
        </div>
      ) : null}

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

      {canManage && editing && (
        <EditThreadDialog
          open
          thread={thread}
          onClose={() => setEditing(false)}
          onSave={handleSave}
        />
      )}

      <ConfirmDialog
        open={canManage && confirmingDelete}
        title="Дали си сигурен дека сакаш да ја избришеш оваа дискусија?"
        confirmLabel={busy ? "Се брише…" : "Избриши"}
        onCancel={() => {
          if (!busy) setConfirmingDelete(false);
        }}
        onConfirm={handleConfirmDelete}
      />

      <InfoDialog
        open={deleted}
        title="Дискусијата беше успешно избришана."
        message="Корисниците сè уште може да ги гледаат коментарите."
        onClose={() => {
          setDeleted(false);
          onDeleted?.(thread.id);
        }}
      />
    </article>
  );
}
