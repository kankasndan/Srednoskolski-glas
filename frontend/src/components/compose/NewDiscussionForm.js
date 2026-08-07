"use client";

import { useCallback, useState } from "react";
import { useRouter } from "next/navigation";
import AnonymousToggle from "@/components/compose/AnonymousToggle";
import ForumSelect from "@/components/forum/ForumSelect";
import PostTypeButtons from "@/components/compose/PostTypeButtons";
import RichTextEditor from "@/components/compose/RichTextEditor";
import TitleInput from "@/components/compose/TitleInput";
import { createThread } from "@/api/threads";

function getPlainTextFromHtml(html) {
  const container = document.createElement("div");
  container.innerHTML = html;
  return container.textContent?.trim() ?? "";
}

const REQUIRED_FIELD_MESSAGES = {
  forum: "Избери форум за дискусијата.",
  title: "Внеси наслов на дискусијата.",
};

export default function NewDiscussionForm() {
  const router = useRouter();
  const [title, setTitle] = useState("");
  const [selectedForum, setSelectedForum] = useState(null);
  const [errors, setErrors] = useState({});
  const [isAnonymous, setIsAnonymous] = useState(false);
  const [attachments, setAttachments] = useState({ files: [], link: "", poll: null });
  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState("");
  const handleAttachmentsChange = useCallback((next) => {
    setAttachments(next);
  }, []);

  async function handleSubmit(event) {
    event.preventDefault();

    const nextErrors = {};
    const formData = new FormData(event.currentTarget);
    const content = formData.get("content")?.toString() ?? "";

    if (!selectedForum) nextErrors.forum = REQUIRED_FIELD_MESSAGES.forum;
    if (!title.trim()) nextErrors.title = REQUIRED_FIELD_MESSAGES.title;

    setErrors(nextErrors);
    if (Object.keys(nextErrors).length > 0) return;

    setSubmitting(true);
    setSubmitError("");

    try {
      const thread = await createThread({
        forumId: selectedForum.id,
        title: title.trim(),
        description: getPlainTextFromHtml(content) ? content : "",
        isAnonymous,
        files: attachments.files,
        link: attachments.link || undefined,
        poll: attachments.poll,
      });

      router.push(`/p/${thread.forum.slug}/${thread.id}`);
    } catch (error) {
      const validation = error.body?.errors;
      if (validation) {
        setSubmitError(Object.values(validation).flat().join(" "));
      } else if (error.status === 401) {
        setSubmitError("Мора да си најавен за да објавиш дискусија.");
      } else {
        setSubmitError(error.message || "Неуспешно објавување. Обиди се повторно.");
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="flex flex-col items-start gap-6">
      <ForumSelect
        selected={selectedForum}
        onChange={(forum) => {
          setSelectedForum(forum);
          setErrors((current) => ({ ...current, forum: undefined }));
        }}
        errorMessage={errors.forum}
      />
      <TitleInput
        value={title}
        onChange={(nextTitle) => {
          setTitle(nextTitle);
          setErrors((current) => ({ ...current, title: undefined }));
        }}
        errorMessage={errors.title}
      />
      <RichTextEditor
        errorMessage={errors.content}
        onContentChange={(nextContent) => {
          if (!getPlainTextFromHtml(nextContent)) return;
          setErrors((current) => ({ ...current, content: undefined }));
        }}
      />
      <PostTypeButtons onAttachmentsChange={handleAttachmentsChange} />
      <AnonymousToggle
        className="w-[632px] max-w-full"
        checked={isAnonymous}
        onChange={setIsAnonymous}
        action={
          <button
            type="submit"
            disabled={submitting}
            className="h-10 w-36 cursor-pointer rounded-xl bg-[#582FF5] font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:bg-[#4B25E0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5] disabled:cursor-not-allowed disabled:opacity-60"
          >
            {submitting ? "Се објавува…" : "Објави"}
          </button>
        }
      />
      {submitError ? (
        <p className="w-[632px] max-w-full font-[family-name:var(--font-manrope)] text-[13px] text-[var(--color-error)]">
          {submitError}
        </p>
      ) : null}
    </form>
  );
}
