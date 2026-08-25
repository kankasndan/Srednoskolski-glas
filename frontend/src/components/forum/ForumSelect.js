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

export default function ForumSelect({
  selected,
  onChange,
  onBlur,
  errorMessage,
  widthClassName = "w-[310px]",
  className = "",
}) {
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
        const match = findUserSchoolForum(schoolsByCity, user);
        if (!match) {
          setSchoolForum(null);
          return;
        }

        const listed = schoolsByCity
          .flatMap((group) => group.forums ?? [])
          .find((forum) => forum.slug === match.slug);
        setSchoolForum(listed ? { ...match, ...listed } : match);
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
  const selectedDescription = selected?.description?.trim() || "";
  const describedBy = [
    errorMessage ? "forum-error" : null,
    selectedDescription ? "forum-description" : null,
  ]
    .filter(Boolean)
    .join(" ") || undefined;

  return (
    <div className={`mb-6 flex max-w-full flex-col gap-2 ${widthClassName} ${className}`}>
      <FieldLabel htmlFor="forum-select" required>
        Каде сакаш да започнеш дискусија?
      </FieldLabel>
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
          id="forum-select"
          type="button"
          disabled={isLoading || !!error}
          aria-haspopup="listbox"
          aria-expanded={open}
          aria-invalid={!!errorMessage}
          aria-describedby={describedBy}
          onClick={() => setOpen((prev) => !prev)}
          className={`flex h-10 w-full cursor-pointer items-center justify-between gap-4 rounded-xl border px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none transition-colors ${
            open || selected ? "bg-[#CFE9ED]" : "bg-white hover:bg-[#DCEBED]"
          } ${errorMessage ? "border-[var(--color-error)]" : "border-[#CCCCCC]"} ${
            selected ? "text-black" : "text-[#595959]"
          }`}
        >
          <span className="flex min-w-0 items-center gap-3">
            {selected && (
              <ForumIcon src={selected.imageUrl} />
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
          <div
            role="listbox"
            aria-labelledby="forum-select"
            className="absolute left-0 top-11 z-10 flex max-h-72 w-full flex-col overflow-y-auto overflow-x-hidden rounded-xl border border-[#CCCCCC] bg-white py-1"
          >
            {options.map((forum) => (
              <ForumOption
                key={forum.slug}
                forum={forum}
                selected={forum.slug === selected?.slug}
                onSelect={handleSelect}
              />
            ))}
          </div>
        )}
      </div>
      {errorMessage ? (
        <p
          id="forum-error"
          className="font-[family-name:var(--font-manrope)] text-[11px] leading-4 text-[var(--color-error)]"
        >
          {errorMessage}
        </p>
      ) : null}
      {selectedDescription ? (
        <p
          id="forum-description"
          className="font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-5 text-[#595959]"
        >
          {selectedDescription}
        </p>
      ) : null}
    </div>
  );
}
