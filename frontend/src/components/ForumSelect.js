"use client";

import { useEffect, useRef, useState } from "react";
import Image from "next/image";
import FieldLabel from "@/components/FieldLabel";
import ForumIcon from "@/components/ForumIcon";
import ForumOption from "@/components/ForumOption";
import { useForums } from "@/hooks/useForums";

export default function ForumSelect({ selected, onChange, onBlur, errorMessage }) {
  const { general, loading, error } = useForums();
  const [open, setOpen] = useState(false);
  const dropdownRef = useRef(null);

  useEffect(() => {
    if (!open) return;

    function handlePointerDown(event) {
      if (!dropdownRef.current?.contains(event.target)) {
        setOpen(false);
      }
    }

    document.addEventListener("pointerdown", handlePointerDown);
    return () => document.removeEventListener("pointerdown", handlePointerDown);
  }, [open]);

  function handleSelect(forum) {
    onChange(forum);
    setOpen(false);
  }

  return (
    <div className="flex w-[310px] max-w-full flex-col gap-2">
      <FieldLabel required>Каде сакаш да започнеш дискусија?</FieldLabel>
      <input type="hidden" name="forum" value={selected?.slug ?? ""} />

      <div
        ref={dropdownRef}
        className="relative w-full"
        onBlur={(event) => {
          if (!event.currentTarget.contains(event.relatedTarget)) {
            setOpen(false);
            onBlur?.();
          }
        }}
      >
        <button
          type="button"
          disabled={loading || !!error}
          aria-expanded={open}
          aria-describedby={errorMessage ? "forum-error" : undefined}
          onClick={() => setOpen((prev) => !prev)}
          className={`flex h-10 w-full cursor-pointer items-center justify-between gap-4 rounded-xl border px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none transition-colors ${
            open || selected ? "bg-[#CFE9ED]" : "bg-white hover:bg-[#DCEBED]"
          } ${errorMessage ? "border-[var(--color-error)]" : "border-[#CCCCCC]"} ${
            selected ? "text-black" : "text-[#595959]"
          }`}
        >
          <span className="flex min-w-0 items-center gap-3">
            {selected && <ForumIcon src={selected.imageUrl} />}
            <span className="truncate leading-5">
              {selected?.name ??
                (error ? "Не успеа вчитувањето" : loading ? "Се вчитува…" : "Избери форум")}
            </span>
          </span>
          <Image
            src="/chevron-down.svg"
            alt=""
            width={16}
            height={16}
            className={`size-4 shrink-0 transition-transform ${open ? "rotate-180" : ""}`}
          />
        </button>

        {open && (
          <div className="absolute left-0 top-11 z-10 flex w-full flex-col overflow-hidden rounded-xl border border-[#CCCCCC] bg-white py-1">
            {general.map((forum) => (
              <ForumOption key={forum.slug} forum={forum} onSelect={handleSelect} />
            ))}
          </div>
        )}
        <p
          id="forum-error"
          className={`absolute left-0 top-full mt-1 w-full truncate font-[family-name:var(--font-manrope)] text-[11px] leading-4 text-[var(--color-error)] ${
            errorMessage ? "" : "invisible"
          }`}
        >
          {errorMessage || "Нема грешка"}
        </p>
      </div>
    </div>
  );
}
