"use client";

import { useState } from "react";
import DialogShell from "@/components/ui/DialogShell";
import PostTypeButtons from "@/components/compose/PostTypeButtons";
import RichTextEditor from "@/components/compose/RichTextEditor";
import TitleInput from "@/components/compose/TitleInput";


export default function EditThreadDialog({ open, thread, onClose, onSave }) {
  const [title, setTitle] = useState(thread?.title ?? "");

  function handleSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    onSave?.({ title, content: formData.get("content")?.toString() ?? "" });
  }

  return (
    <DialogShell
      open={open}
      label="Уреди ја дискусијата"
      onClose={onClose}
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
                className="cursor-pointer rounded-xl bg-[var(--color-primary-200)] px-6 py-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:bg-[#4B25E0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-200)]"
              >
                Објави
              </button>
            }
          />
          <PostTypeButtons widthClassName="w-full" />
        </div>
      </form>
    </DialogShell>
  );
}
