"use client";

import Image from "next/image";
import Link from "next/link";
import { useId, useState } from "react";
import { FEED_THREAD_LIST } from "@/lib/threads";

const SORT_OPTIONS = [
  { value: "trending", label: "Трендинг" },
  { value: "popular", label: "Популарно" },
  { value: "new", label: "Ново" },
  { value: "featured", label: "Истакнато" },
];

const TIME_FILTER_OPTIONS = [
  { value: "today", label: "Денес" },
  { value: "week", label: "Оваа недела" },
  { value: "month", label: "Овој месец" },
  { value: "year", label: "Оваа година" },
];


function ThreadTag({ tag }) {
  return (
    <span
      className={`flex h-6 shrink-0 items-center rounded-[6px] px-2 font-[family-name:var(--font-roboto)] text-[12px] font-normal leading-4 text-black ${
        tag.tone === "highlight"
          ? "bg-[#F0E92F] border-[0.5px] border-[#CCCCCC]"
          : "bg-[#E6E6E6] border-[0.5px] border-[#CCCCCC]"
      }`}
      style={{ gap: tag.icon ? "8px" : undefined }}
    >
      {tag.icon ? (
        <span className="relative size-4 shrink-0 overflow-hidden">
          <Image
            src={tag.icon}
            alt=""
            width={tag.iconZoom ? 40 : 16}
            height={tag.iconZoom ? 40 : 16}
            className={`absolute left-1/2 top-1/2 max-w-none -translate-x-1/2 -translate-y-1/2 ${
              tag.iconZoom ? "size-10" : "size-4"
            }`}
          />
        </span>
      ) : null}
      {tag.label}
    </span>
  );
}

function TimestampTag({ label }) {
  return (
    <span className="shrink-0 font-[family-name:var(--font-roboto)] text-[12px] font-normal leading-4 text-[#595959]">
      {label}
    </span>
  );
}

function ActionButton({ icon, label, count, onClick, noHover = false }) {
  return (
    <button
      type="button"
      aria-label={label}
      onClick={onClick}
      className={`flex h-10 w-24 items-center justify-center gap-4 rounded-[12px] border border-[#CCCCCC] bg-white px-4 py-2 opacity-80 transition-all ${
        noHover
          ? ""
          : "hover:border-[#582FF5] hover:bg-[#F5F2FF] hover:opacity-100 hover:shadow-sm cursor-pointer"
      }`}
    >
      <Image src={icon} alt="" width={24} height={24} className="size-6 shrink-0" />
      <span className="min-w-0 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-[19px] text-black">
        {count}
      </span>
    </button>
  );
}



function ThreadItem({ thread }) {
  const metadataRow = (
    <div className="flex max-w-full flex-wrap items-center gap-2">
      {thread.tags.map((tag) => (
        <ThreadTag key={`${thread.id}-${tag.label}`} tag={tag} />
      ))}
      <TimestampTag label={thread.postedAgo} />
    </div>
  );

  const textContent = (
    <div className="flex flex-col gap-2">
      <Link
        href={`/feed/thread/${thread.id}`}
        className="overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-[27px] text-[#000000] transition-colors hover:text-[#582FF5]"
      >
        {thread.title}
      </Link>
      <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
        {thread.excerpt}
      </p>
    </div>
  );

  const actionButtons = (
    <>
      <ActionButton icon="/eye.svg" label="Прегледи" count={thread.views} noHover={true} />
      <ActionButton icon="/chat-1-line.svg" label="Коментари" count={thread.comments} />
      <ActionButton icon="/Chevrons up.svg" label="Гласај нагоре" count={thread.votes} />
    </>
  );

  if (thread.image) {
    return (
      <article className="relative flex w-full max-w-full flex-col gap-4 bg-transparent pb-6 after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:rounded-full after:bg-[#CFE9ED]">
        <div className="flex flex-col gap-4">
          {metadataRow}
          {textContent}
        </div>
        <Link href={`/feed/thread/${thread.id}`} className="block">
          <Image
            src={thread.image}
            alt=""
            width={990}
            height={421}
            className="h-[421px] w-full rounded-[24px] object-cover transition-opacity hover:opacity-90"
            priority={thread.id === 2}
          />
        </Link>
        <div className="flex flex-row items-center justify-end gap-2">
          {actionButtons}
        </div>
      </article>
    );
  }

  return (
    <article className="relative flex w-full max-w-full items-start justify-between gap-8 bg-transparent py-5 after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:rounded-full after:bg-[#CFE9ED]">
      <div className="flex min-w-0 flex-1 flex-col gap-4">
        {metadataRow}
        {textContent}
      </div>
      <div className="flex shrink-0 flex-col gap-2">
        {actionButtons}
      </div>
    </article>
  );
}

function FeedSelect({ name, label, options, selected, isOpen, listboxId, onToggle, onSelect }) {
  return (
    <div className="relative h-10 w-40 shrink-0">
      <input type="hidden" name={name} value={selected.value} />

      <button
        type="button"
        aria-haspopup="listbox"
        aria-expanded={isOpen}
        aria-controls={listboxId}
        onClick={onToggle}
        className={`flex h-10 w-40 cursor-pointer items-center justify-between gap-2 rounded-[12px] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors ${
          isOpen ? "bg-[#CFE9ED]" : "bg-white border border-[#CCCCCC] hover:bg-[#F5F5F5]"
        }`}
      >
        <span className="flex h-[19px] items-center overflow-hidden text-ellipsis whitespace-nowrap">{selected.label}</span>
        <Image
          src="/chevron-down.svg"
          alt=""
          width={16}
          height={16}
          className={`size-4 shrink-0 transition-transform ${isOpen ? "rotate-180" : ""}`}
        />
      </button>

      {isOpen && (
        <div
          id={listboxId}
          role="listbox"
          aria-label={label}
          className="absolute left-0 top-12 z-20 flex w-40 flex-col overflow-hidden rounded-[12px] border border-[#CCCCCC] bg-white shadow-[0_12px_24px_rgba(88,47,245,0.14)]"
        >
          {options.map((option, idx) => (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={selected.value === option.value}
              onClick={() => onSelect(option)}
              className={`flex h-10 w-full items-center justify-center px-4 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#CFE9ED]/50 ${
                idx < options.length - 1 ? "border-b border-[#CCCCCC]/60" : ""
              } ${selected.value === option.value ? "bg-[#CFE9ED]/30" : ""}`}
            >
              {option.label}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

export default function FeedThreads() {
  const sortListboxId = useId();
  const timeListboxId = useId();
  const [openSelect, setOpenSelect] = useState(null);
  const [selectedSort, setSelectedSort] = useState(SORT_OPTIONS[0]);
  const [selectedTimeFilter, setSelectedTimeFilter] = useState(TIME_FILTER_OPTIONS[1]); // Default to "Оваа недела"

  const selectSortOption = (option) => {
    setSelectedSort(option);
    setOpenSelect(null);
  };

  const selectTimeFilterOption = (option) => {
    setSelectedTimeFilter(option);
    setOpenSelect(null);
  };

  // Perform dynamic filtering and sorting
  const filteredAndSortedThreads = FEED_THREAD_LIST.filter((thread) => {
    const age = thread.ageInDays;
    if (selectedTimeFilter.value === "today") {
      return age === 0;
    }
    if (selectedTimeFilter.value === "week") {
      return age <= 7;
    }
    if (selectedTimeFilter.value === "month") {
      return age <= 30;
    }
    if (selectedTimeFilter.value === "year") {
      return age <= 365;
    }
    return true;
  }).sort((a, b) => {
    if (selectedSort.value === "popular") {
      return b.votes - a.votes;
    }
    if (selectedSort.value === "new") {
      if (a.ageInDays !== b.ageInDays) {
        return a.ageInDays - b.ageInDays;
      }
      return b.id - a.id;
    }
    if (selectedSort.value === "featured") {
      const aFeatured = a.tags.some((t) => t.tone === "highlight" || t.label === "Препорачано") ? 1 : 0;
      const bFeatured = b.tags.some((t) => t.tone === "highlight" || t.label === "Препорачано") ? 1 : 0;
      return bFeatured - aFeatured;
    }
    // "trending": combined sorting by score
    return (b.votes + b.comments) - (a.votes + a.comments);
  });

  return (
    <section className="flex w-[990px] max-w-full flex-col gap-8">
      <div className="flex h-10 gap-2 self-end">
        <FeedSelect
          name="sort"
          label="Сортирај дискусии"
          options={SORT_OPTIONS}
          selected={selectedSort}
          isOpen={openSelect === "sort"}
          listboxId={sortListboxId}
          onToggle={() => setOpenSelect((current) => (current === "sort" ? null : "sort"))}
          onSelect={selectSortOption}
        />
        <FeedSelect
          name="timeFilter"
          label="Филтрирај по време"
          options={TIME_FILTER_OPTIONS}
          selected={selectedTimeFilter}
          isOpen={openSelect === "time"}
          listboxId={timeListboxId}
          onToggle={() => setOpenSelect((current) => (current === "time" ? null : "time"))}
          onSelect={selectTimeFilterOption}
        />
      </div>

      <div className="flex w-[990px] max-w-full flex-col gap-6 bg-transparent" aria-label="Дискусии">
        {filteredAndSortedThreads.length > 0 ? (
          filteredAndSortedThreads.map((thread) => (
            <ThreadItem key={thread.id} thread={thread} />
          ))
        ) : (
          <div className="flex h-40 w-full items-center justify-center rounded-[12px] border border-[#CCCCCC]/40 bg-white font-[family-name:var(--font-manrope)] text-[16px] font-medium text-[#595959]">
            Нема пронајдено дискусии за избраниот период.
          </div>
        )}
      </div>
    </section>
  );
}
