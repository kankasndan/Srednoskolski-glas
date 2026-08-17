"use client";

import { useState } from "react";
import DialogShell from "@/components/ui/DialogShell";
import MentionTextarea from "@/components/thread/MentionTextarea";
import { userFacingError } from "@/lib/api";

const MAX_COMMENT_LENGTH = 1000;

export default function EditCommentDialog({
  open,
  comment,
  onClose,
  onSave,
}) {
  const [content, setContent] = useState(comment?.content ?? "");
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState("");
  const isEmpty = content.trim() === "";

  async function handleSubmit(event) {
    event.preventDefault();
    if (isEmpty || saving) return;

    setSaving(true);
    setError("");

    try {
      await onSave?.({ content: content.trim() });
    } catch (err) {
      const validation = err.body?.errors;
      if (validation) {
        setError(Object.values(validation).flat().join(" "));
      } else {
        setError(userFacingError(err, "Неуспешно зачувување. Обиди се повторно."));
      }
    } finally {
      setSaving(false);
    }
  }

  return (
    <DialogShell
      open={open}
      label="Уреди го коментарот"
      onClose={saving ? undefined : onClose}
      widthClassName="max-w-xl"
    >
      <form onSubmit={handleSubmit} className="flex w-full flex-col gap-4">
        <h2 className="font-(family-name:--font-manrope) text-[18px] font-bold text-black">
          Уреди го коментарот
        </h2>

        <MentionTextarea
          value={content}
          onChange={setContent}
          maxLength={MAX_COMMENT_LENGTH}
          disabled={saving}
          aria-label="Коментар"
          autoFocus
          className="h-32 w-full resize-none rounded-xl border border-[#CCCCCC] p-4 text-[14px] leading-6 text-black outline-none transition-colors placeholder:text-[#595959] focus:border-[#582FF5] disabled:opacity-60"
        />

        {error ? (
          <p className="font-(family-name:--font-manrope) text-[13px] text-[#DC2626]">
            {error}
          </p>
        ) : null}

        <div className="flex justify-end gap-3">
          <button
            type="button"
            onClick={onClose}
            disabled={saving}
            className="cursor-pointer rounded-xl border-[0.5px] border-(--color-primary-200) bg-white px-5 py-3 font-(family-name:--font-manrope) text-[14px] font-bold text-black transition-colors hover:bg-[#DCEBED] disabled:cursor-not-allowed disabled:opacity-60"
          >
            Откажи
          </button>
          <button
            type="submit"
            disabled={isEmpty || saving}
            className="cursor-pointer rounded-xl bg-(--color-primary-200) px-6 py-3 font-(family-name:--font-manrope) text-[14px] font-bold text-white transition-colors hover:bg-[#3300F5] disabled:cursor-not-allowed disabled:bg-[#CCCCCC]"
          >
            {saving ? "Се зачувува…" : "Зачувај"}
          </button>
        </div>
      </form>
    </DialogShell>
  );
}
