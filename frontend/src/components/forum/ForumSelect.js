"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import Image from "next/image";
import { getProfileUser } from "@/api/profile";
import FieldLabel from "@/components/ui/FieldLabel";
import ForumIcon from "@/components/forum/ForumIcon";
import ForumOption from "@/components/forum/ForumOption";
import { useForums } from "@/hooks/useForums";

function findUserSchoolForum(schoolsByCity, user) {
  const studentData = user?.student_data ?? user?.studentData ?? null;
  const schoolId = studentData?.school?.id ?? studentData?.school_id ?? null;

  if (schoolId != null) {
    for (const group of schoolsByCity) {
      const match = (group.forums ?? []).find(
        (forum) => forum.school_id === schoolId || forum.school_id === Number(schoolId),
      );
      if (match) {
        return match;
      }
    }
  }

  const forum = studentData?.school?.forum;
  if (forum?.slug) {
    return forum;
  }

  return null;
}

export default function ForumSelect({ selected, onChange, onBlur, errorMessage }) {
  const { general, schoolsByCity, loading, error } = useForums();
  const [schoolForum, setSchoolForum] = useState(null);
  const [userLoading, setUserLoading] = useState(true);
  const [open, setOpen] = useState(false);
  const dropdownRef = useRef(null);

  useEffect(() => {
    let active = true;

    getProfileUser()
      .then((user) => {
        if (!active) return;
        setSchoolForum(findUserSchoolForum(schoolsByCity, user));
      })
      .catch(() => {
        if (active) setSchoolForum(null);
      })
      .finally(() => {
        if (active) setUserLoading(false);
      });

    return () => {
      active = false;
    };
  }, [schoolsByCity]);

  const options = useMemo(() => {
    if (!schoolForum) {
      return general;
    }

    const withoutDuplicate = general.filter((forum) => forum.slug !== schoolForum.slug);
    return [schoolForum, ...withoutDuplicate];
  }, [general, schoolForum]);

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

  const isLoading = loading || userLoading;

  return (
    <div className="mb-12 flex w-[268px] max-w-full flex-col gap-2">
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
          disabled={isLoading || !!error}
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
            {selected && (
              <ForumIcon
                src={selected.imageUrl}
                imageClassName={
                  selected.type === "school" ? "size-4" : "size-9 max-w-none"
                }
              />
            )}
            <span className="truncate leading-5">
              {selected?.name ??
                (error ? "Не успеа вчитувањето" : isLoading ? "Се вчитува…" : "Избери форум")}
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
          <div className="absolute left-0 top-11 z-10 flex max-h-72 w-full flex-col overflow-y-auto overflow-x-hidden rounded-xl border border-[#CCCCCC] bg-white py-1">
            {options.map((forum) => (
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
