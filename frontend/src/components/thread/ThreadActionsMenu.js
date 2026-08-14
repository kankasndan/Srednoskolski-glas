"use client";

import dynamic from "next/dynamic";
import Image from "next/image";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { reportErrorMessage, reportThread } from "@/api/moderation";
import { deleteThread, updateThread } from "@/api/threads";
import ConfirmDialog from "@/components/ui/ConfirmDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import ReportDialog from "@/components/ui/ReportDialog";
import ThreeDotsMenu from "@/components/ui/ThreeDotsMenu";

const EditThreadDialog = dynamic(
  () => import("@/components/thread/EditThreadDialog"),
  { ssr: false },
);

export default function ThreadActionsMenu({
  thread,
  isOwner,
  onUpdated,
  className = "",
  ownerAction = "menu",
}) {
  const router = useRouter();
  // Prefer API `is_owner` (covers anonymous threads). Optional prop overrides.
  const showOwnerActions = isOwner ?? Boolean(thread?.is_owner);

  const [editing, setEditing] = useState(false);
  const [saved, setSaved] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);
  const [deleted, setDeleted] = useState(false);
  const [busy, setBusy] = useState(false);
  const [reporting, setReporting] = useState(false);
  const [reported, setReported] = useState(false);

  const ownerItems = [
    { label: "Измени", onSelect: () => setEditing(true) },
    { label: "Избриши", onSelect: () => setConfirmingDelete(true) },
  ];

  async function handleSave({
    title,
    content,
    files,
    link,
    removeAttachmentIds,
    poll,
    removePoll,
  }) {
    const updated = await updateThread(thread.id, {
      title,
      content,
      files,
      link,
      removeAttachmentIds,
      poll,
      removePoll,
    });

    onUpdated?.({
      ...updated,
      is_edited: true,
      edited_at: updated.edited_at ?? updated.updated_at,
    });
    setEditing(false);
    setSaved(true);
  }

  async function handleConfirmDelete() {
    if (busy) return;

    setBusy(true);

    try {
      await deleteThread(thread.id);
      setConfirmingDelete(false);
      setDeleted(true);
    } catch {
      setConfirmingDelete(false);
    } finally {
      setBusy(false);
    }
  }

  async function handleReport({ reason, details }) {
    try {
      await reportThread(thread.id, { reason, details });
      setReporting(false);
      setReported(true);
    } catch (error) {
      const next = new Error(reportErrorMessage(error));
      next.status = error?.status;
      next.body = error?.body;
      throw next;
    }
  }

  return (
    <>
      {showOwnerActions ? (
        <div className={className}>
          {ownerAction === "delete" ? (
            <button
              type="button"
              aria-label="Избриши дискусија"
              onClick={() => setConfirmingDelete(true)}
              className="thread-actions-trigger grid size-9 cursor-pointer place-items-center rounded-lg transition-colors hover:bg-[#E5E5E5]"
            >
              <Image
                src="/more-2-fill.svg"
                alt=""
                width={20}
                height={20}
                className="size-5"
              />
            </button>
          ) : (
            <ThreeDotsMenu items={ownerItems} />
          )}
        </div>
      ) : (
        <div className={className}>
          <button
            type="button"
            aria-label="Пријави"
            onClick={() => setReporting(true)}
            className="thread-actions-trigger grid size-9 cursor-pointer place-items-center rounded-lg text-[#333333] transition-colors hover:bg-[#E5E5E5]"
          >
            <Image
              src="/comments icon/report.svg"
              alt=""
              width={18}
              height={18}
              className="size-[18px]"
            />
          </button>
        </div>
      )}

      {editing && (
        <EditThreadDialog
          open
          thread={thread}
          onClose={() => setEditing(false)}
          onSave={handleSave}
        />
      )}

      {reporting && (
        <ReportDialog
          open
          onClose={() => setReporting(false)}
          onSubmit={handleReport}
        />
      )}

      <InfoDialog
        open={saved}
        title="Промените беа успешно зачувани."
        onClose={() => setSaved(false)}
      />

      <ConfirmDialog
        open={confirmingDelete}
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
          const slug = thread.forum?.slug;
          if (slug) router.push(`/p/${slug}`);
        }}
      />

      <InfoDialog
        open={reported}
        title="Пријавата беше успешно поднесена и испратена до админот."
        onClose={() => setReported(false)}
      />
    </>
  );
}
