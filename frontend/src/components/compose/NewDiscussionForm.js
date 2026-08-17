"use client";

import { useCallback, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import AnonymousToggle from "@/components/compose/AnonymousToggle";
import ForumSelect from "@/components/forum/ForumSelect";
import PostTypeButtons from "@/components/compose/PostTypeButtons";
import RichTextEditor from "@/components/compose/RichTextEditor";
import TitleInput from "@/components/compose/TitleInput";
import InfoDialog from "@/components/ui/InfoDialog";
import PrimaryButton from "@/components/ui/PrimaryButton";
import { createThread } from "@/api/threads";
import { useProfile } from "@/hooks/useProfile";
import { userFacingError } from "@/lib/api";
import { stripHtml } from "@/lib/html";
import {
  canCreateThreadInForum,
  canCreateThreads,
  needsOnboarding,
} from "@/lib/capabilities";

const POSTED_TITLE = "Дискусијата беше успешно објавена.";
const POSTED_MESSAGE = "Можеш да ја следиш на форумот или на твојот профил.";

const REQUIRED_FIELD_MESSAGES = {
  forum: "Избери форум за дискусијата.",
  title: "Внеси наслов на дискусијата.",
};

export default function NewDiscussionForm() {
  const router = useRouter();
  const { user, loading: profileLoading } = useProfile();
  const [title, setTitle] = useState("");
  const [selectedForum, setSelectedForum] = useState(null);
  const [errors, setErrors] = useState({});
  const [isAnonymous, setIsAnonymous] = useState(false);
  const [attachments, setAttachments] = useState({ files: [], link: "", poll: null });
  const [submitting, setSubmitting] = useState(false);
  const [submitError, setSubmitError] = useState("");
  const [postedThread, setPostedThread] = useState(null);
  const handleAttachmentsChange = useCallback((next) => {
    setAttachments(next);
  }, []);

  async function handleSubmit(event) {
    event.preventDefault();

    if (!canCreateThreads(user)) {
      setSubmitError("Немаш дозвола да започнеш дискусија. Потребно е да си дел од училиште.");
      return;
    }

    const nextErrors = {};
    const formData = new FormData(event.currentTarget);
    const content = formData.get("content")?.toString() ?? "";

    if (!selectedForum) nextErrors.forum = REQUIRED_FIELD_MESSAGES.forum;
    else if (!canCreateThreadInForum(user, selectedForum)) {
      nextErrors.forum = "Не можеш да започнеш дискусија во овој форум.";
    }
    if (!title.trim()) nextErrors.title = REQUIRED_FIELD_MESSAGES.title;

    setErrors(nextErrors);
    if (Object.keys(nextErrors).length > 0) return;

    setSubmitting(true);
    setSubmitError("");

    try {
      const thread = await createThread({
        forumId: selectedForum.id,
        title: title.trim(),
        description: stripHtml(content) ? content : "",
        isAnonymous,
        files: attachments.files,
        link: attachments.link || undefined,
        poll: attachments.poll,
      });

      // Odenjeto kon novata diskusija cheka da se zatvori potvrdata.
      setPostedThread(thread);
    } catch (error) {
      const validation = error.body?.errors;
      if (validation) {
        setSubmitError(Object.values(validation).flat().join(" "));
      } else if (error.status === 401) {
        setSubmitError("Мора да си најавен за да објавиш дискусија.");
      } else if (error.status === 403) {
        setSubmitError(
          userFacingError(error, "Немаш дозвола да започнеш дискусија во овој форум."),
        );
      } else {
        setSubmitError(userFacingError(error, "Неуспешно објавување. Обиди се повторно."));
      }
    } finally {
      setSubmitting(false);
    }
  }

  if (profileLoading) {
    return (
      <p className="font-[family-name:var(--font-manrope)] text-[14px] text-[#595959]">
        Се вчитува…
      </p>
    );
  }

  if (!canCreateThreads(user)) {
    return (
      <div className="max-w-xl rounded-3xl border border-[#CCCCCC] bg-[#F7F7F7] p-6 font-[family-name:var(--font-manrope)] text-[14px] leading-6 text-[#595959]">
        {user == null ? (
          <>
            <Link href="/login" className="font-bold text-[#582FF5] hover:underline">
              Најави се
            </Link>{" "}
            и заврши го onboarding процесот со училиште за да започнеш дискусија.
          </>
        ) : needsOnboarding(user) ? (
          <>
            <Link href="/register/onboarding" className="font-bold text-[#582FF5] hover:underline">
              Заврши ја регистрацијата
            </Link>{" "}
            за да можеш да започнеш дискусија.
          </>
        ) : (
          "Само корисници кои се дел од училиште можат да започнат дискусија. Сè уште можеш да коментираш на постоечки дискусии."
        )}
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="flex w-full flex-col items-start gap-6">
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
        widthClassName="w-full"
      />
      <RichTextEditor
        errorMessage={errors.content}
        widthClassName="w-full"
        onContentChange={(nextContent) => {
          if (!stripHtml(nextContent)) return;
          setErrors((current) => ({ ...current, content: undefined }));
        }}
      />
      <PostTypeButtons widthClassName="w-full" onAttachmentsChange={handleAttachmentsChange} />
      <AnonymousToggle
        className="w-full"
        checked={isAnonymous}
        onChange={setIsAnonymous}
        action={
          <PrimaryButton
            type="submit"
            disabled={submitting}
            className="h-10 w-36 font-[family-name:var(--font-manrope)] text-[14px] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-200)] disabled:opacity-60"
          >
            {submitting ? "Се објавува…" : "Објави"}
          </PrimaryButton>
        }
      />
      {submitError ? (
        <p className="w-full font-[family-name:var(--font-manrope)] text-[13px] text-[var(--color-error)]">
          {submitError}
        </p>
      ) : null}

      <InfoDialog
        open={Boolean(postedThread)}
        title={POSTED_TITLE}
        message={POSTED_MESSAGE}
        messageWidthClassName="max-w-[246px]"
        onClose={() =>
          router.push(`/p/${postedThread.forum.slug}/${postedThread.id}`)
        }
      />
    </form>
  );
}
