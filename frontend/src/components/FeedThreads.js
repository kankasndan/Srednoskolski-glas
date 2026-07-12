"use client";

import Image from "next/image";
import { useId, useState } from "react";

const SORT_OPTIONS = [
  { value: "trending", label: "Трендинг" },
  { value: "newest", label: "Најново" },
];

const THREADS = [
  {
    tags: [
      { label: "Државна матура", icon: "school" },
      { label: "марко_2026", icon: "user" },
      { label: "Гим. Орце Николов" },
    ],
    title: "Кои се најдобрите ресурси за подготовка на матура по математика?",
    excerpt: "Здраво на сите. Секој совет е добредојден...",
    votes: 24,
    comments: 8,
  },
  {
    tags: [
      { label: "Препорачано", tone: "highlight" },
      { label: "Факултети", icon: "school" },
      { label: "елена.к", icon: "user" },
      { label: "СУГС Михајло Пупин" },
    ],
    title: "Brainster Next vs ЕТФ - кој е подобар за софтверско инженерство?",
    excerpt: "Размислувам помеѓу овие два факултета и ме интересира мислење на постари ученици...",
    votes: 18,
    comments: 11,
  },
  {
    tags: [
      { label: "Ментално здравје", icon: "heart" },
      { label: "анонимен_111", icon: "user" },
      { label: "Гим. Никола Карев" },
    ],
    title: "Како се справувате со стрес пред испити?",
    excerpt: "Имам матура за два месеца и не можам да спијам нормално...",
    votes: 31,
    comments: 14,
  },
  {
    tags: [
      { label: "СУГС Михајло Пупин", icon: "school" },
      { label: "стефан_22", icon: "user" },
    ],
    title: "Кога ќе се одржи екскурзијата за матуранти?",
    excerpt: "Дали некој има информација...",
    votes: 9,
    comments: 5,
  },
];

const THREAD_LIST = Array.from({ length: 16 }, (_, index) => ({
  ...THREADS[index % THREADS.length],
  id: index,
}));

function TagIcon({ type }) {
  if (type === "user") {
    return (
      <svg aria-hidden="true" width="14" height="14" viewBox="0 0 16 16" fill="none">
        <path
          d="M8 8C9.38071 8 10.5 6.88071 10.5 5.5C10.5 4.11929 9.38071 3 8 3C6.61929 3 5.5 4.11929 5.5 5.5C5.5 6.88071 6.61929 8 8 8Z"
          stroke="#595959"
          strokeWidth="1.5"
        />
        <path
          d="M3.75 13C4.359 11.801 5.766 11 8 11C10.234 11 11.641 11.801 12.25 13"
          stroke="#595959"
          strokeLinecap="round"
          strokeWidth="1.5"
        />
      </svg>
    );
  }

  if (type === "heart") {
    return (
      <svg aria-hidden="true" width="14" height="14" viewBox="0 0 16 16" fill="none">
        <path
          d="M8 13.2L7.15 12.44C4.13 9.72 2.2 7.98 2.2 5.85C2.2 4.11 3.56 2.75 5.3 2.75C6.28 2.75 7.22 3.21 7.84 3.93C8.46 3.21 9.4 2.75 10.38 2.75C12.12 2.75 13.48 4.11 13.48 5.85C13.48 7.98 11.55 9.72 8.53 12.44L8 13.2Z"
          fill="#582FF5"
        />
      </svg>
    );
  }

  if (type === "school") {
    return (
      <svg aria-hidden="true" width="14" height="14" viewBox="0 0 16 16" fill="none">
        <path d="M2 6L8 2.5L14 6H2Z" fill="#582FF5" />
        <path d="M3.25 6H12.75V7.5H3.25V6Z" fill="#582FF5" />
        <path d="M4 7.5H5.5V12H4V7.5ZM7.25 7.5H8.75V12H7.25V7.5ZM10.5 7.5H12V12H10.5V7.5Z" fill="#582FF5" />
        <path d="M2.75 12H13.25V13.5H2.75V12Z" fill="#582FF5" />
      </svg>
    );
  }

  return null;
}

function ThreadTag({ tag }) {
  return (
    <span
      className={`flex h-6 shrink-0 items-center gap-1 rounded-md px-2 font-[family-name:var(--font-manrope)] text-[12px] font-bold leading-none text-black ${
        tag.tone === "highlight" ? "bg-[#F0E92F]" : "bg-[#F5F5F5]"
      }`}
    >
      <TagIcon type={tag.icon} />
      {tag.label}
    </span>
  );
}

function ActionButton({ icon, label, count }) {
  return (
    <button
      type="button"
      aria-label={label}
      className="flex h-12 w-24 items-center justify-center gap-4 rounded-2xl border border-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black opacity-80"
    >
      <Image src={icon} alt="" width={24} height={24} className="size-6" />
      <span>{count}</span>
    </button>
  );
}

function ThreadItem({ thread }) {
  return (
    <article className="flex h-40 w-[990px] max-w-full items-start justify-center gap-[140px] rounded-3xl border-b border-[#CCCCCC] bg-transparent pt-6 pb-8">
      <div className="flex h-[97px] w-[681px] shrink-0 flex-col gap-4">
        <div className="flex h-6 max-w-[451px] items-center gap-2 overflow-hidden">
          {thread.tags.map((tag) => (
            <ThreadTag key={`${thread.id}-${tag.label}`} tag={tag} />
          ))}
        </div>

        <div className="flex h-[57px] w-[681px] flex-col gap-2">
          <h3 className="h-[27px] w-fit max-w-[681px] whitespace-nowrap font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-none text-black">
            {thread.title}
          </h3>
          <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
            {thread.excerpt}
          </p>
        </div>
      </div>

      <div className="flex h-[104px] w-24 shrink-0 flex-col gap-2">
        <ActionButton icon="/Chevrons up.svg" label="Гласај нагоре" count={thread.votes} />
        <ActionButton icon="/chat-1-line.svg" label="Коментари" count={thread.comments} />
      </div>
    </article>
  );
}

export default function FeedThreads() {
  const listboxId = useId();
  const [isOpen, setIsOpen] = useState(false);
  const [selected, setSelected] = useState(SORT_OPTIONS[0]);

  const selectOption = (option) => {
    setSelected(option);
    setIsOpen(false);
  };

  return (
    <section className="flex h-[3060px] w-[990px] max-w-full flex-col gap-8">
      <div className="relative w-36">
        <input type="hidden" name="sort" value={selected.value} />

        <button
          type="button"
          aria-haspopup="listbox"
          aria-expanded={isOpen}
          aria-controls={listboxId}
          onClick={() => setIsOpen((open) => !open)}
          className="flex h-12 w-36 cursor-pointer items-center justify-center gap-3 rounded-2xl bg-[#F88DD5] px-4 py-2"
        >
          <span className="flex h-[19px] w-[67px] items-center font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black">
            {selected.label}
          </span>
          <Image
            src="/chevron-down.svg"
            alt=""
            width={16}
            height={16}
            className={`size-4 transition-transform ${isOpen ? "rotate-180" : ""}`}
          />
        </button>

        {isOpen && (
          <div
            id={listboxId}
            role="listbox"
            aria-label="Сортирај дискусии"
            className="absolute left-0 top-[56px] z-20 flex w-36 flex-col overflow-hidden rounded-2xl bg-[#F88DD5] py-2 shadow-[0_12px_24px_rgba(88,47,245,0.16)]"
          >
            {SORT_OPTIONS.map((option) => (
              <button
                key={option.value}
                type="button"
                role="option"
                aria-selected={selected.value === option.value}
                onClick={() => selectOption(option)}
                className="flex h-10 w-full items-center px-4 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#F7A6DE]"
              >
                {option.label}
              </button>
            ))}
          </div>
        )}
      </div>

      <div className="flex h-[2980px] w-[990px] max-w-full flex-col gap-6 bg-transparent" aria-label="Дискусии">
        {THREAD_LIST.map((thread) => (
          <ThreadItem key={thread.id} thread={thread} />
        ))}
      </div>
    </section>
  );
}
