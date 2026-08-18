"use client";

import Image from "next/image";
import { useCallback, useId, useRef, useState } from "react";
import FeedFilterSheet from "@/components/thread/FeedFilterSheet";
import { useClickOutside } from "@/hooks/useClickOutside";

const SORT_OPTIONS = [
  { value: "best", label: "Најдобри" },
  { value: "newest", label: "Најнови" },
  { value: "oldest", label: "Најстари" },
];

export default function CommentsHeader({ count, sort = "best", onSortChange }) {
  const listboxId = useId();
  const selectRef = useRef(null);
  const [open, setOpen] = useState(false);
  const [filtersOpen, setFiltersOpen] = useState(false);
  const selected =
    SORT_OPTIONS.find((option) => option.value === sort) ?? SORT_OPTIONS[0];
  const shownOptions = SORT_OPTIONS.filter(
    (option) => option.value !== selected.value,
  );

  useClickOutside(
    selectRef,
    useCallback(() => setOpen(false), []),
    open,
  );

  return (
    <>
      <button
        type="button"
        onClick={() => setFiltersOpen(true)}
        className="flex h-10 cursor-pointer items-center gap-2 self-start font-[family-name:var(--font-manrope)] text-[14px] leading-none text-black lg:hidden"
      >
        <Image
          src="/mobile version/filter.svg"
          alt=""
          width={24}
          height={24}
          className="size-6"
        />
        <span className="font-bold">Филтери</span>
        <span className="text-[12px] text-[#595959]">({count} коментари)</span>
      </button>

      <FeedFilterSheet
        open={filtersOpen}
        onClose={() => setFiltersOpen(false)}
        hiddenFrom="lg"
        sortOptions={SORT_OPTIONS}
        selectedSort={selected}
        onSelectSort={(option) => {
          onSortChange?.(option.value);
          setFiltersOpen(false);
        }}
      />

      <div className="hidden items-center justify-between gap-3 lg:flex">
        <h2 className="flex h-10 items-center text-[16px] font-bold leading-[19.5px] text-black">
          {count} коментари
        </h2>

        <div ref={selectRef} className="relative h-10 w-36 shrink-0">
          <button
            type="button"
            aria-haspopup="listbox"
            aria-expanded={open}
            aria-controls={listboxId}
            onClick={() => setOpen(!open)}
            className={`flex h-10 w-36 cursor-pointer items-center justify-center gap-2 px-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors ${
              open ? "rounded-t-xl bg-[#CFE9ED]" : "rounded-xl bg-white hover:bg-[#CFE9ED]"
            }`}
          >
            <span className="flex h-[19px] items-center">{selected.label}</span>
            <Image
              src="/chevron-down.svg"
              alt=""
              width={16}
              height={16}
              className={`size-4 shrink-0 transition-transform ${open ? "rotate-180" : ""}`}
            />
          </button>

          {open ? (
            <div
              id={listboxId}
              role="listbox"
              aria-label="Сортирај коментари"
              className="absolute left-0 top-10 z-20 flex w-36 flex-col overflow-hidden rounded-b-xl bg-white shadow-[0_12px_24px_rgba(0,0,0,0.12)]"
            >
              {shownOptions.map((option) => (
                <button
                  key={option.value}
                  type="button"
                  role="option"
                  aria-selected={selected.value === option.value}
                  onClick={() => {
                    onSortChange?.(option.value);
                    setOpen(false);
                  }}
                  className="flex h-10 w-full cursor-pointer items-center justify-center border-t border-[#CCCCCC] px-4 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black transition-colors hover:bg-[#E5E5E5]"
                >
                  {option.label}
                </button>
              ))}
            </div>
          ) : null}
        </div>
      </div>
    </>
  );
}
