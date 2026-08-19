"use client";

import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faXmark } from "@fortawesome/free-solid-svg-icons";

config.autoAddCss = false;

export default function DocumentAttachment({ file, fileName, onAdd, onClose }) {
  const name = file?.name ?? fileName ?? null;
  const type = name ? name.split(".").pop()?.toUpperCase() : null;

  return (
    <div className="flex max-w-full min-w-0 items-center gap-3 rounded-xl border border-[#CCCCCC] bg-white px-4 py-3">
      <img src="/new thread icons/documents.svg" alt="" className="h-5 w-auto shrink-0" />

      {name ? (
        <span className="flex min-w-0 flex-1 items-baseline gap-2">
          <span className="truncate font-[family-name:var(--font-manrope)] text-[14px] text-black">
            {name}
          </span>
          <span className="shrink-0 font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
            {type}
          </span>
        </span>
      ) : (
        <button
          type="button"
          onClick={onAdd}
          className="min-w-0 flex-1 cursor-pointer truncate text-left font-[family-name:var(--font-manrope)] text-[14px] text-[#595959]"
        >
          Прикачи документ тука
        </button>
      )}

      <button
        type="button"
        aria-label="Отстрани го документот"
        onClick={onClose}
        className="flex size-6 shrink-0 cursor-pointer items-center justify-center text-[#595959] transition-colors hover:text-black"
      >
        <FontAwesomeIcon icon={faXmark} className="h-4 w-4" />
      </button>
    </div>
  );
}
