"use client";

import Image from "next/image";
import { useId, useState, useEffect } from "react";
import { formatDistanceToNow } from "date-fns";
import { mk } from "date-fns/locale";
import { API_BASE_URL } from "@/lib/api";

const SORT_OPTIONS = [{ value: "trending", label: "Трендинг" }];

const TIME_FILTER_OPTIONS = [
  { value: "week", label: "Оваа недела" },
  { value: "month", label: "Овој месец" },
  { value: "six-months", label: "6 месеци" },
  { value: "year", label: "1 година" },
];

function ActionButton({ icon, label, count }) {
  return (
    <button
      type="button"
      aria-label={label}
      className="flex items-center justify-center gap-1 rounded-2xl border border-[#CCCCCC] hover:bg-gray-200 transition px-4 py-2 cursor-pointer"
    >
      <Image src={icon} alt="" width={24} height={24} className="size-6" />
      <span>{count ?? 0}</span>
    </button>
  );
}

function ThreadItem({ thread }) {
  const content = (
    <div className="flex w-full items-start justify-between gap-8">
      <div className="flex flex-col gap-4">
        <div className="flex items-center gap-2">
          <div className="bg-gray-100 rounded-xl py-1 px-2 flex gap-2 items-center">
            {thread.forum.imageUrl ? (
              <img
                src={thread.forum.imageUrl}
                className="w-5 h-5 rounded-full object-cover"
              />
            ) : (
              <img
                src="/avatars/default-1.svg"
                className="w-5 h-5 rounded-full object-cover"
              />
            )}
            {thread.forum.name}
          </div>

          {thread.author ? (
            <>
              <div className="bg-gray-100 rounded-xl py-1 px-2 flex gap-1 items-center">
                {thread.author.imageUrl ? (
                  <img
                    src={thread.author.imageUrl}
                    className="w-5 h-5 rounded-full object-cover"
                  />
                ) : (
                  <img
                    src="/avatars/default-1.svg"
                    className="w-5 h-5 rounded-full object-cover"
                  />
                )}
                {thread.author.username}
              </div>
              <div className="bg-gray-100 rounded-xl py-1 px-2 flex gap-2 items-center">
                {thread.author.school ? thread.author.school.name : null}
              </div>
            </>
          ) : null}

          <span className="text-sm">
            {formatDistanceToNow(new Date(thread.created_at), {
              addSuffix: true,
              locale: mk,
            })}
          </span>
        </div>

        <div className="flex min-h-[57px] w-[681px] max-w-full flex-col gap-2">
          <h3 className="w-fit max-w-full overflow-hidden text-ellipsis whitespace-nowrap font-[family-name:var(--font-manrope)] text-[20px] font-bold leading-[27px] text-black">
            {thread.title}
          </h3>
          <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
            {thread.description}
          </p>
        </div>

        <div className="text-xs flex items-center gap-0.5">
          <img src="/eye-line.svg" />
          <span className="text-primary-300 font-bold">
            {thread.views ?? 0}
          </span>
        </div>
      </div>
    </div>
  );

  const actions = (
    <>
      <ActionButton
        icon="/Chevrons up.svg"
        label="Гласај нагоре"
        count={thread.upvotes}
      />
      <ActionButton
        icon="/chat-1-line.svg"
        label="Коментари"
        count={thread.comments_count}
      />
    </>
  );

  if (thread.image) {
    return (
      <article className="relative flex flex-col gap-4 items-start justify-center bg-transparent border-b border-b-[#CFE9ED] hover:bg-gray-50 p-4 rounded-3xl cursor-pointer">
        <div className="w-full">{content}</div>
        <Image
          src={thread.image}
          alt=""
          width={990}
          height={421}
          className="h-[421px] w-full rounded-t-3xl rounded-b-2xl object-cover"
          priority={thread.id === 2}
        />
        <div className="flex gap-2">{actions}</div>
      </article>
    );
  }

  return (
    <article className="relative flex items-start justify-center bg-transparent border-b border-b-[#CFE9ED] hover:bg-gray-50 p-4 rounded-3xl cursor-pointer">
      {content}
      <div className="flex flex-col gap-2">{actions}</div>
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
  onToggle,
  onSelect,
}) {
  return (
    <div className="relative">
      <input type="hidden" name={name} value={selected.value} />

      <button
        type="button"
        aria-haspopup="listbox"
        aria-expanded={isOpen}
        aria-controls={listboxId}
        onClick={onToggle}
        className="w-36 flex py-2 px-3 rounded-xl cursor-pointer items-center justify-center gap-1 bg-gray-100 font-bold hover:bg-gray-200 tranistion"
      >
        <span className={`text-nowrap`}>{selected.label}</span>
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
          className="box-border absolute left-0 top-12 z-20 flex w-full flex-col overflow-hidden rounded-xl bg-white py-2 shadow-xl"
        >
          {options.map((option) => (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={selected.value === option.value}
              onClick={() => onSelect(option)}
              className="flex w-full p-2 items-center px-4 leading-none transition-colors hover:bg-gray-100 cursor-pointer"
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
  const [paginationPage, setPaginationPage] = useState(1);
  const [paginating, setPaginating] = useState(true);

  const [threads, setThreads] = useState([]);
  const [moreThreadsLoading, setMoreThreadsLoading] = useState(false);
  const [noMoreThreads, setNoMoreThreads] = useState(false);

  async function fetchThreads({
    byPagination = false,
    sort = selectedSort,
    time = selectedTimeFilter,
  }) {
    if (byPagination) {
      setMoreThreadsLoading(true);

      const response = await fetch(
        API_BASE_URL +
          "/api/feed?page=" +
          paginationPage +
          "&time=" +
          time.value +
          "&sort=" +
          sort.value,
      );

      const threads = await response.json();
      setThreads((prev) => [...prev, ...threads.data]);
      if (threads.data.length === 0) {
        setNoMoreThreads(true);
      }
      setMoreThreadsLoading(false);
    } else {
      setNoMoreThreads(false);
      setPaginationPage(1);
      const response = await fetch(
        API_BASE_URL +
          "/api/feed?page=1" +
          "&time=" +
          time.value +
          "&sort=" +
          sort.value,
      );

      const threads = await response.json();

      setThreads(threads.data);
    }
  }

  const selectSortOption = (option) => {
    setSelectedSort(option);
    setOpenSelect(null);
    fetchThreads({ byPagination: false, sort: option });
  };

  const selectTimeFilterOption = (option) => {
    setSelectedTimeFilter(option);
    setOpenSelect(null);
    fetchThreads({ byPagination: false, time: option });
  };

  useEffect(() => {
    fetchThreads({ byPagination: true });
  }, [paginating]);

  return (
    <section className="flex w-full max-w-[990px] flex-col items-center gap-8">
      <div className="flex self-end gap-2">
        <FeedSelect
          name="sort"
          label="Сортирај дискусии"
          options={SORT_OPTIONS}
          selected={selectedSort}
          isOpen={openSelect === "sort"}
          listboxId={sortListboxId}
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
          onToggle={() =>
            setOpenSelect((current) => (current === "time" ? null : "time"))
          }
          onSelect={selectTimeFilterOption}
        />
      </div>

      <div className="flex w-full flex-col gap-6" aria-label="Дискусии">
        {threads.map((thread) => (
          <ThreadItem key={thread.id} thread={thread} />
        ))}
        {/* {THREAD_LIST.map((thread) => (
          <ThreadItem key={thread.id} thread={thread} />
        ))} */}
      </div>

      {noMoreThreads ? (
        <span className="font-bold text-primary-300 text-xl group-hover:opacity-80 transition">
          Нема веќе :/
        </span>
      ) : (
        <button
          onClick={() => {
            setPaginationPage((prev) => prev + 1);
            setPaginating((prev) => !prev);
          }}
          className="flex gap-2 items-center cursor-pointer group"
        >
          <span className="font-bold text-primary-300 text-xl group-hover:opacity-80 transition">
            Прочитај повеќе
          </span>
          {moreThreadsLoading ? (
            <div className="bg-primary-300 rounded-full w-6 h-6 text-white font-bold flex items-center justify-center group-hover:opacity-80 transition animate-spin">
              <img src="/plus.svg" className="size-5" />
            </div>
          ) : (
            <div className="bg-primary-300 rounded-full w-6 h-6 text-white font-bold flex items-center justify-center group-hover:opacity-80 transition">
              <img src="/plus.svg" className="size-5" />
            </div>
          )}
        </button>
      )}
    </section>
  );
}
