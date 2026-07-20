"use client";

import Image from "next/image";
import { useId, useState } from "react";

const SORT_OPTIONS = [{ value: "trending", label: "Трендинг" }];

const TIME_FILTER_OPTIONS = [
  { value: "week", label: "Оваа недела" },
  { value: "month", label: "Овој месец" },
  { value: "six-months", label: "6 месеци" },
  { value: "year", label: "1 година" },
];

const THREADS = [
  {
    tags: [
      {
        label: "Државна матура",
        icon: "/icons/drzhavna_matura.svg",
        iconZoom: true,
      },
      { label: "марко_2026", icon: "/Generic avatar.svg" },
      { label: "Гим. Орце Николов", icon: "/icons/uchilishte.svg" },
    ],
    title: "Кои се најдобрите ресурси за подготовка на матура по математика?",
    excerpt: "Здраво на сите. Секој совет е добредојден...",
    postedAgo: "пред 2ч.",
    votes: 24,
    comments: 8,
  },
  {
    tags: [
      { label: "Препорачано", tone: "highlight" },
      { label: "Факултети", icon: "/icons/fakulteti.svg", iconZoom: true },
      { label: "елена.к", icon: "/Generic avatar.svg" },
      { label: "СУГС Михајло Пупин", icon: "/icons/uchilishte.svg" },
    ],
    title: "Brainster Next vs ЕТФ - кој е подобар за софтверско инженерство?",
    excerpt:
      "Размислувам помеѓу овие два факултета и ме интересира мислење на постари ученици...",
    postedAgo: "пред 4ч.",
    votes: 18,
    comments: 11,
  },
  {
    tags: [
      {
        label: "Ментално здравје",
        icon: "/icons/mentalno_zdravje.svg",
        iconZoom: true,
      },
      { label: "анонимен_111", icon: "/Generic avatar.svg" },
      { label: "Гим. Никола Карев", icon: "/icons/uchilishte.svg" },
    ],
    title: "Како се справувате со стрес пред испити?",
    excerpt: "Имам матура за два месеца и не можам да спијам нормално...",
    postedAgo: "пред 1д.",
    votes: 31,
    comments: 14,
  },
  {
    tags: [
      { label: "СУГС Михајло Пупин", icon: "/icons/uchilishte.svg" },
      { label: "стефан_22", icon: "/Generic avatar.svg" },
    ],
    title: "Кога ќе се одржи екскурзијата за матуранти?",
    excerpt: "Дали некој има информација...",
    postedAgo: "пред 3д.",
    votes: 9,
    comments: 5,
  },
];

const THREAD_LIST = Array.from({ length: 16 }, (_, index) => ({
  ...THREADS[index % THREADS.length],
  id: index,
  image: index === 2 || index === 6 ? "/thread-placeholder.png" : null,
}));

function ThreadTag({ tag }) {
  return (
    <span
      className={`flex h-6 shrink-0 items-center gap-1 rounded-md px-2 font-[family-name:var(--font-manrope)] text-[12px] font-bold leading-none text-black ${
        tag.tone === "highlight" ? "bg-[#F0E92F]" : "bg-[#F5F5F5]"
      }`}
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
    <span className="flex h-6 w-[63px] shrink-0 items-center justify-center gap-2 rounded-md px-2 py-1 font-[family-name:var(--font-manrope)] text-[12px] font-normal leading-4 tracking-normal text-[#595959]">
      <span className="flex h-4 w-[47px] items-center gap-1 overflow-hidden whitespace-nowrap">
        {label}
      </span>
    </span>
  );
}

function ActionButton({ icon, label, count }) {
  return (
    <button
      type="button"
      aria-label={label}
      className="flex h-12 w-24 items-center justify-center gap-4 rounded-2xl border border-[#CCCCCC] px-4 py-2 text-black opacity-80"
    >
      <Image src={icon} alt="" width={24} height={24} className="size-6" />
      <span className="flex h-[19px] w-[17px] items-center font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none tracking-normal">
        {count}
      </span>
    </button>
  );
}

function ThreadItem({ thread }) {
  const content = (
    <div className="flex w-full items-start justify-between gap-8 cursor-pointer hover:bg-gray-50 p-4 rounded-3xl">
      <div className="flex min-h-[97px] w-[681px] max-w-[calc(100%-128px)] shrink-0 flex-col gap-4">
        <div className="flex h-6 max-w-full items-center gap-2 overflow-hidden">
          {thread.tags.map((tag) => (
            <ThreadTag key={`${thread.id}-${tag.label}`} tag={tag} />
          ))}
          <TimestampTag label={thread.postedAgo} />
        </div>

        <div className="flex min-h-[57px] w-[681px] max-w-full flex-col gap-2">
          <h3 className="w-fit max-w-full overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-[27px] text-black">
            {thread.title}
          </h3>
          <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
            {thread.excerpt}
          </p>
        </div>
      </div>

      <div className="flex h-[104px] w-24 shrink-0 flex-col gap-2">
        <ActionButton
          icon="/Chevrons up.svg"
          label="Гласај нагоре"
          count={thread.votes}
        />
        <ActionButton
          icon="/chat-1-line.svg"
          label="Коментари"
          count={thread.comments}
        />
      </div>
    </div>
  );

  if (thread.image) {
    return (
      <article className="relative flex h-[574px] w-[990px] max-w-full flex-col overflow-hidden rounded-t-3xl bg-transparent after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:rounded-full after:bg-[#CFE9ED]">
        <Image
          src={thread.image}
          alt=""
          width={990}
          height={421}
          className="h-[421px] w-full rounded-t-3xl object-cover"
          priority={thread.id === 2}
        />
        <div className="px-0 pt-6 pb-8">{content}</div>
      </article>
    );
  }

  return (
    <article className="relative flex h-40 w-[990px] max-w-full items-start justify-center rounded-3xl bg-transparent px-0 pt-6 pb-8 after:absolute after:bottom-0 after:left-0 after:h-px after:w-full after:rounded-full after:bg-[#CFE9ED]">
      {content}
    </article>
  );
}

function FeedSelect({
  name,
  label,
  options,
  selected,
  isOpen,
  listboxId,
  textWidthClassName,
  onToggle,
  onSelect,
}) {
  return (
    <div className="relative h-10 w-36 shrink-0">
      <input type="hidden" name={name} value={selected.value} />

      <button
        type="button"
        aria-haspopup="listbox"
        aria-expanded={isOpen}
        aria-controls={listboxId}
        onClick={onToggle}
        className="flex h-10 w-36 cursor-pointer items-center justify-center gap-2 rounded-[12px] bg-gray-100 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black"
      >
        <span className={`flex h-[19px] items-center ${textWidthClassName}`}>
          {selected.label}
        </span>
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
          className="absolute left-0 top-12 z-20 flex w-36 flex-col overflow-hidden rounded-[12px] bg-white py-2 shadow-[0_12px_24px_rgba(88,47,245,0.14)]"
        >
          {options.map((option) => (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={selected.value === option.value}
              onClick={() => onSelect(option)}
              className="flex h-10 w-full items-center px-4 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#F5F5F5]"
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
  const [selectedTimeFilter, setSelectedTimeFilter] = useState(
    TIME_FILTER_OPTIONS[0],
  );

  const selectSortOption = (option) => {
    setSelectedSort(option);
    setOpenSelect(null);
  };

  const selectTimeFilterOption = (option) => {
    setSelectedTimeFilter(option);
    setOpenSelect(null);
  };

  return (
    <section className="flex w-[990px] max-w-full flex-col gap-8">
      <div className="flex h-10 w-[288px] self-end gap-2">
        <FeedSelect
          name="sort"
          label="Сортирај дискусии"
          options={SORT_OPTIONS}
          selected={selectedSort}
          isOpen={openSelect === "sort"}
          listboxId={sortListboxId}
          textWidthClassName="w-[67px]"
          onToggle={() =>
            setOpenSelect((current) => (current === "sort" ? null : "sort"))
          }
          onSelect={selectSortOption}
        />
        <FeedSelect
          name="timeFilter"
          label="Филтрирај по време"
          options={TIME_FILTER_OPTIONS}
          selected={selectedTimeFilter}
          isOpen={openSelect === "time"}
          listboxId={timeListboxId}
          textWidthClassName="w-[89px]"
          onToggle={() =>
            setOpenSelect((current) => (current === "time" ? null : "time"))
          }
          onSelect={selectTimeFilterOption}
        />
      </div>

      <div
        className="flex w-[990px] max-w-full flex-col gap-6 bg-transparent"
        aria-label="Дискусии"
      >
        {THREAD_LIST.map((thread) => (
          <ThreadItem key={thread.id} thread={thread} />
        ))}
      </div>
    </section>
  );
}
