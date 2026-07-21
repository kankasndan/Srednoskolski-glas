"use client";

import Link from "next/link";
import { useState } from "react";

const MAX_COMMENT_LENGTH = 1000;

export default function CommentComposer({ forumSlug }) {
  const [comment, setComment] = useState("");
  const isEmpty = comment.trim() === "";

  function handleSubmit(event) {
    event.preventDefault();
    if (isEmpty) return;
        // TODO comment endpoint
    setComment("");
  }

  return (
    <form
      onSubmit={handleSubmit}
      className="flex flex-col gap-4 rounded-3xl border border-[#CFE9ED] bg-white p-6"
    >
      <h2 className="text-[16px] font-bold leading-none text-black">
        Остави коментар
      </h2>

      <textarea
        value={comment}
        onChange={(event) => setComment(event.target.value)}
        maxLength={MAX_COMMENT_LENGTH}
        placeholder="Сподели го своето мислење... Употреби @ за да означиш некого..."
        aria-label="Коментар"
        className="h-32 resize-none rounded-xl border border-[#D9D9D9] p-4 text-[14px] leading-6 text-black outline-none transition-colors placeholder:text-[#595959] focus:border-[#582FF5]"
      />

      <div className="flex items-center justify-between gap-4">
        {/* TODO da se napravi da odi na pravilata koga kje bidat napraveni*/}
        <Link
          href={`/p/${forumSlug}`}
          className="text-[12px] leading-[18px] text-[#595959] underline underline-offset-[3px]"
        >
          Внимавај на правилата на заедницата.
        </Link>

        <button
          type="submit"
          disabled={isEmpty}
          className="h-10 w-36 shrink-0 cursor-pointer rounded-xl bg-[#582FF5] text-[14px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0] disabled:cursor-not-allowed disabled:bg-[#CCCCCC]"
        >
          Објави
        </button>
      </div>
    </form>
  );
}
