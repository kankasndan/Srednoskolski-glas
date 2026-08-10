"use client";

import { useCallback, useState } from "react";
import DialogShell from "@/components/ui/DialogShell";
import PostTypeButtons from "@/components/compose/PostTypeButtons";
import RichTextEditor from "@/components/compose/RichTextEditor";
import TitleInput from "@/components/compose/TitleInput";

export default function EditThreadDialog({ open, thread, onClose, onSave }) {
  const [title, setTitle] = useState(thread?.title ?? "");
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const [attachments, setAttachments] = useState({
    files: [],
    link: "",
    removeAttachmentIds: [],
    poll: null,
    removePoll: false,
  });

  const handleAttachmentsChange = useCallback((next) => {
    setAttachments({
      files: next.files ?? [],
      link: next.link ?? "",
      removeAttachmentIds: next.removeAttachmentIds ?? [],
      poll: next.poll ?? null,
      removePoll: Boolean(next.removePoll),
    });
  }, []);

  async function handleSubmit(event) {
    event.preventDefault();
    if (saving) return;

    const formData = new FormData(event.currentTarget);
    const content = formData.get("content")?.toString() ?? "";

    if (title.trim().length < 3) {
      setError("Насловот мора да има најмалку 3 карактери.");
      return;
    }

    setSaving(true);
    setError("");

    try {
      await onSave?.({
        title: title.trim(),
        content,
        files: attachments.files,
        link: attachments.link,
        removeAttachmentIds: attachments.removeAttachmentIds,
        poll: attachments.poll,
        removePoll: attachments.removePoll,
      });
    } catch (err) {
      const validation = err.body?.errors;
      if (validation) {
        setError(Object.values(validation).flat().join(" "));
      } else {
        setError(err.message || "Неуспешно зачувување. Обиди се повторно.");
      }
    } finally {
      setSaving(false);
    }
  }

  return (
    <DialogShell
      open={open}
      label="Уреди ја дискусијата"
      onClose={saving ? undefined : onClose}
      widthClassName="max-w-4xl"
    >
      <form onSubmit={handleSubmit} noValidate className="flex w-full flex-col gap-8">
        <TitleInput value={title} onChange={setTitle} widthClassName="w-full" />

        <div className="flex flex-col gap-4">
          <RichTextEditor
            initialContent={thread?.description ?? ""}
            widthClassName="w-full"
            action={
              <button
                type="submit"
                disabled={saving}
                className="cursor-pointer rounded-xl bg-[var(--color-primary-200)] px-6 py-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:bg-[#4B25E0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-200)] disabled:cursor-not-allowed disabled:bg-[#CCCCCC]"
              >
                {saving ? "Се зачувува…" : "Објави"}
              </button>
            }
          />
          <PostTypeButtons
            widthClassName="w-full"
            initialAttachments={thread?.attachments ?? []}
            initialPoll={thread?.poll ?? null}
            allowPoll
            onAttachmentsChange={handleAttachmentsChange}
          />

          {error ? (
            <p className="font-(family-name:--font-manrope) text-[13px] text-[#DC2626]">
              {error}
            </p>
          ) : null}
        </div>
      </form>
    </DialogShell>
  );
}
