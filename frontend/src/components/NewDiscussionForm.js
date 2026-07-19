"use client";

import { useState } from "react";
import AnonymousToggle from "@/components/AnonymousToggle";
import ForumSelect from "@/components/ForumSelect";
import PostTypeButtons from "@/components/PostTypeButtons";
import RichTextEditor from "@/components/RichTextEditor";
import TitleInput from "@/components/TitleInput";

function getPlainTextFromHtml(html) {
  const container = document.createElement("div");
  container.innerHTML = html;
  return container.textContent?.trim() ?? "";
}

const REQUIRED_FIELD_MESSAGES = {
  forum: "Избери форум за дискусијата.",
  title: "Внеси наслов на дискусијата.",
  content: "Напиши содржина за дискусијата.",
};

export default function NewDiscussionForm() {
  const [title, setTitle] = useState("");
  const [selectedForum, setSelectedForum] = useState(null);
  const [errors, setErrors] = useState({});

  function handleSubmit(event) {
    event.preventDefault();

    const nextErrors = {};
    const formData = new FormData(event.currentTarget);
    const content = formData.get("content")?.toString() ?? "";

    if (!selectedForum) nextErrors.forum = REQUIRED_FIELD_MESSAGES.forum;
    if (!title.trim()) nextErrors.title = REQUIRED_FIELD_MESSAGES.title;
    if (!getPlainTextFromHtml(content)) nextErrors.content = REQUIRED_FIELD_MESSAGES.content;

    setErrors(nextErrors);
    if (Object.keys(nextErrors).length > 0) return;
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="flex flex-col items-start gap-6">
      <div className="flex w-[632px] max-w-full items-start gap-3">
        <ForumSelect
          selected={selectedForum}
          onChange={(forum) => {
            setSelectedForum(forum);
            setErrors((current) => ({ ...current, forum: undefined }));
          }}
          errorMessage={errors.forum}
        />
        <AnonymousToggle className="mt-[22px] flex-1" />
      </div>
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
      <PostTypeButtons />
      <div className="flex w-[632px] max-w-full justify-end">
        <button
          type="submit"
          className="h-10 w-36 cursor-pointer rounded-xl bg-[#582FF5] font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:bg-[#4B25E0] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5]"
        >
          Објави
        </button>
      </div>
    </form>
  );
}
