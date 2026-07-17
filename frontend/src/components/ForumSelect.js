"use client";

import { useState } from "react";
import Image from "next/image";
import FieldLabel from "@/components/FieldLabel";
import ForumIcon from "@/components/ForumIcon";
import ForumOption from "@/components/ForumOption";
import { useForums } from "@/hooks/useForums";

export default function ForumSelect() {
  const { general, loading, error } = useForums();
  const [selected, setSelected] = useState(null);
  const [open, setOpen] = useState(false);

  function handleSelect(forum) {
    setSelected(forum);
    setOpen(false);
  }

  return (
    <div className="flex w-[310px] max-w-full flex-col gap-2">
      <FieldLabel required>Каде сакаш да започнеш дискусија?</FieldLabel>

      <div
        className="relative w-full"
        onBlur={(event) => {
          if (!event.currentTarget.contains(event.relatedTarget)) setOpen(false);
        }}
      >
        <button
          type="button"
          disabled={loading || !!error}
          aria-expanded={open}
          onClick={() => setOpen((prev) => !prev)}
          className={`flex h-10 w-full items-center justify-between gap-4 rounded-xl border border-[#CCCCCC] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none transition-colors ${
            open || selected ? "bg-[#CFE9ED]" : "bg-white hover:bg-[#DCEBED]"
          } ${selected ? "text-black" : "text-[#595959]"}`}
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
            width={20}
            height={20}
            className={`size-5 shrink-0 transition-transform ${open ? "rotate-180" : ""}`}
          />
        </button>

        {open && (
          <div className="absolute left-0 top-11 z-10 flex w-full flex-col overflow-hidden rounded-xl border border-[#CCCCCC] bg-white py-1">
            {general.map((forum) => (
              <ForumOption key={forum.slug} forum={forum} onSelect={handleSelect} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
