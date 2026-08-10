"use client";

import dynamic from "next/dynamic";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { deleteThread, updateThread } from "@/api/threads";
import ConfirmDialog from "@/components/ui/ConfirmDialog";
import InfoDialog from "@/components/ui/InfoDialog";
import ReportDialog from "@/components/ui/ReportDialog";
import ThreeDotsMenu from "@/components/ui/ThreeDotsMenu";
import { useProfile } from "@/hooks/useProfile";

const EditThreadDialog = dynamic(
  () => import("@/components/thread/EditThreadDialog"),
  { ssr: false },
);

export default function ThreadActionsMenu({ thread, isOwner, onUpdated }) {
  const router = useRouter();
  const { user } = useProfile();
  const showOwnerActions =
    isOwner ??
    (user != null && thread?.author?.id != null && user.id === thread.author.id);

  const [editing, setEditing] = useState(false);
  const [confirmingDelete, setConfirmingDelete] = useState(false);
  const [deleted, setDeleted] = useState(false);
  const [busy, setBusy] = useState(false);
  const [reporting, setReporting] = useState(false);
  const [reported, setReported] = useState(false);

  const items = showOwnerActions
    ? [
        { label: "Измени", onSelect: () => setEditing(true) },
        { label: "Избриши", onSelect: () => setConfirmingDelete(true) },
      ]
    : [{ label: "Пријави", onSelect: () => setReporting(true) }];

  async function handleSave({ title, content, files, link, removeAttachmentIds }) {
    const updated = await updateThread(thread.id, {
      title,
      description: content,
      files,
      link,
      removeAttachmentIds,
    });

    onUpdated?.(updated);
    setEditing(false);
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

  return (
    <>
      <ThreeDotsMenu items={items} />

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
          onSubmit={() => {
            setReporting(false);
            setReported(true);
          }}
        />
      )}

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
