"use client";

import Link from "next/link";
import { useState } from "react";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faXmark } from "@fortawesome/free-solid-svg-icons";
import { createComment } from "@/api/comments";
import GifPicker from "@/components/thread/GifPicker";
import MentionTextarea from "@/components/thread/MentionTextarea";
import PrimaryButton from "@/components/ui/PrimaryButton";
import { useProfile } from "@/hooks/useProfile";
import { userFacingError } from "@/lib/api";
import { canCreateComments, needsOnboarding } from "@/lib/capabilities";

config.autoAddCss = false;

const MAX_COMMENT_LENGTH = 1000;

const COMPOSER_ID = "comment-composer";
const COMPOSER_INPUT_ID = "comment-composer-input";

export function focusCommentComposer() {
  const input = document.getElementById(COMPOSER_INPUT_ID);

  if (input) {
    input.scrollIntoView({ behavior: "smooth", block: "center" });
    input.focus({ preventScroll: true });
    return;
  }

  // Bez dozvola za komentiranje nema pole, pa odi do porakata.
  document
    .getElementById(COMPOSER_ID)
    ?.scrollIntoView({ behavior: "smooth", block: "center" });
}

export default function CommentComposer({
  threadId,
  parentId = null,
  compact = false,
  isAnonymousThread = false,
  isThreadOwner = false,
  onClose,
  onCreated,
}) {
  const { user, loading: profileLoading } = useProfile();
  const [comment, setComment] = useState("");
  const [gif, setGif] = useState(null);
  const [pickerOpen, setPickerOpen] = useState(false);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState("");
  const isEmpty = comment.trim() === "" && gif === null;
  const allowed = canCreateComments(user);

  async function handleSubmit(event) {
    event.preventDefault();
    if (isEmpty || busy || !threadId || !allowed) return;

    setBusy(true);
    setError("");

    try {
      const created = await createComment(threadId, {
        content: comment.trim(),
        parentId,
        gifUrl: gif?.url,
      });

      setComment("");
      setGif(null);
      onCreated?.(created);
      onClose?.();
    } catch (err) {
      if (err.status === 403) {
        setError(userFacingError(err, "Немаш дозвола да коментираш."));
      } else if (err.status === 401) {
        setError("Мора да си најавен за да коментираш.");
      } else {
        setError(
          userFacingError(
            err,
            err.status >= 500 || err.status === 0
              ? "Објавувањето траеше предолго. Ако прикачуваш видео, почекај и обиди се повторно."
              : "Неуспешно објавување. Обиди се повторно.",
          ),
        );
      }
    } finally {
      setBusy(false);
    }
  }

  if (profileLoading) {
    return null;
  }

  if (!allowed) {
    return (
      <div
        id={compact ? undefined : COMPOSER_ID}
        className={
          compact
            ? "rounded-xl border border-[#CCCCCC] bg-[#F7F7F7] px-4 py-3 text-[13px] text-[#595959]"
            : "rounded-3xl border border-[#CCCCCC] bg-[#F7F7F7] p-6 text-[14px] text-[#595959]"
        }
      >
        {user == null ? (
          <>
            <Link href="/login" className="font-bold text-[#582FF5] hover:underline">
              Најави се
            </Link>{" "}
            за да коментираш.
          </>
        ) : needsOnboarding(user) ? (
          <>
            <Link href="/register/onboarding" className="font-bold text-[#582FF5] hover:underline">
              Заврши ја регистрацијата
            </Link>{" "}
            за да можеш да коментираш.
          </>
        ) : (
          "Немаш дозвола да коментираш."
        )}
      </div>
    );
  }

  return (
    <form
      id={compact ? undefined : COMPOSER_ID}
      onSubmit={handleSubmit}
      className={
        compact
          ? "flex flex-col gap-2"
          : "flex w-full flex-col gap-2 rounded-3xl border border-[#CFE9ED] bg-white p-6 md:gap-4"
      }
    >
      {compact ? null : (
        <h2 className="h-5 text-[16px] font-bold leading-[19.5px] text-black">
          Остави коментар
        </h2>
      )}

      {isAnonymousThread && isThreadOwner && !compact ? (
        <p className="text-[12px] leading-5 text-[#595959]">
          Како автор на анонимната дискусија, твоите коментари овде се прикажуваат
          како анонимни.
        </p>
      ) : null}

      <MentionTextarea
        id={compact ? undefined : COMPOSER_INPUT_ID}
        value={comment}
        onChange={setComment}
        maxLength={MAX_COMMENT_LENGTH}
        placeholder={
          compact
            ? "Напиши одговор..."
            : "Употреби @ за да означиш некого..."
        }
        aria-label={compact ? "Одговор" : "Коментар"}
        autoFocus={compact}
        disabled={busy}
        className={`w-full resize-none rounded-[14px] border border-[#CCCCCC] p-3 text-[14px] leading-6 text-black outline-none transition-colors placeholder:text-[#595959] focus:border-[#582FF5] disabled:opacity-60 ${
          compact ? "h-20" : "h-20 md:h-32"
        }`}
      />

      {pickerOpen ? (
        <GifPicker
          onSelect={(picked) => {
            setGif(picked);
            setPickerOpen(false);
          }}
          onClose={() => setPickerOpen(false)}
        />
      ) : null}

      {gif ? (
        <div className="relative w-fit">
          <img src={gif.url} alt={gif.title} className="max-w-40 rounded-xl" />
          <button
            type="button"
            aria-label="Отстрани го GIF-от"
            onClick={() => setGif(null)}
            className="absolute right-2 top-2 flex size-8 cursor-pointer items-center justify-center rounded-full bg-black/50 text-white transition-colors hover:bg-black/70"
          >
            <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
          </button>
        </div>
      ) : null}

      {error ? (
        <p className="text-[13px] text-[#DC2626]">{error}</p>
      ) : null}

      <div
        className={`flex gap-4 ${
          compact
            ? "flex-wrap items-center justify-end"
            : "flex-col items-start justify-between sm:flex-row sm:items-center"
        }`}
      >
        {compact ? (
          <button
            type="button"
            onClick={onClose}
            disabled={busy}
            className="cursor-pointer text-[12px] leading-none text-[#595959] transition-colors hover:text-black disabled:opacity-50"
          >
            Откажи
          </button>
        ) : (
          <Link
            href="/rules"
            className="h-[18px] cursor-pointer text-[12px] leading-[18px] text-[#595959] underline underline-offset-[4px] transition-colors hover:text-black"
          >
            Внимавај на правилата на заедницата.
          </Link>
        )}

        <div className={`flex items-center gap-3 ${compact ? "" : "w-full sm:w-auto"}`}>
          <button
            type="button"
            aria-pressed={pickerOpen}
            onClick={() => setPickerOpen((open) => !open)}
            disabled={busy}
            className={`flex shrink-0 cursor-pointer items-center rounded-xl border font-[family-name:var(--font-manrope)] leading-5 transition-colors disabled:opacity-50 ${
              compact ? "h-9 px-3 text-[12px]" : "h-10 px-4 text-[14px]"
            } ${
              pickerOpen
                ? "border-[#582FF5] bg-[#CFE9ED] text-black"
                : "border-[#CCCCCC] bg-white text-[#595959] hover:bg-[#DCEBED] hover:text-black"
            }`}
          >
            GIF
          </button>

          <PrimaryButton
            type="submit"
            disabled={isEmpty || busy}
            className={`flex shrink-0 items-center justify-center gap-4 whitespace-nowrap leading-none disabled:bg-[#CCCCCC] ${
              compact ? "h-9 px-5 text-[12px]" : "h-10 flex-1 px-4 text-[14px] sm:w-36 sm:flex-none"
            }`}
          >
            {busy ? "…" : "Објави"}
          </PrimaryButton>
        </div>
      </div>
    </form>
  );
}
