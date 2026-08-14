"use client";

import Image from "next/image";
import { useId, useState, useEffect } from "react";
import { formatDistanceToNow } from "date-fns";
import { mk } from "date-fns/locale";
import { API_BASE_URL } from "@/lib/api";
import { useRouter } from "next/navigation";
import Cookies from "js-cookie";

const SORT_OPTIONS = [{ value: "trending", label: "Трендинг" }];

const TIME_FILTER_OPTIONS = [
  { value: "week", label: "Оваа недела" },
  { value: "month", label: "Овој месец" },
  { value: "six-months", label: "6 месеци" },
  { value: "year", label: "1 година" },
];

function ActionButton({ icon, label, count, onclick, upvoteToggle }) {
  return (
    <button
      onClick={onclick}
      type="button"
      aria-label={label}
      className={
        !upvoteToggle
          ? "flex items-center justify-center gap-1 rounded-2xl border border-[#CCCCCC] hover:bg-gray-200 transition px-4 py-2 cursor-pointer"
          : "bg-primary-300 text-white flex items-center justify-center gap-1 rounded-2xl border border-primary-300 hover:bg-primary-100 transition px-4 py-2 cursor-pointer"
      }
    >
      {!upvoteToggle ? (
        <Image src={icon} alt="" width={24} height={24} className="size-6" />
      ) : (
        <Image
          src={icon}
          alt=""
          width={24}
          height={24}
          className="size-6 -scale-y-100 white-icon"
        />
      )}
      <span>{!upvoteToggle ? (count ?? 0) : (count + 1 ?? 0)}</span>
    </button>
  );
}

function ThreadItem({ thread }) {
  const [upvoteToggle, setUpvoteToggle] = useState(false);
  async function upvote() {
    const path = API_BASE_URL + "/api/threads/" + thread.id + "/upvote";
    setUpvoteToggle((prev) => !prev);
    await fetch(path, {
      method: "POST",
      credentials: "include",
      headers: {
        "X-XSRF-TOKEN": Cookies.get("XSRF-TOKEN"),
      },
    });
  }

  const router = useRouter();
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
        onclick={upvote}
        upvoteToggle={upvoteToggle}
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
      <article className="relative flex flex-col gap-4 items-start justify-center bg-transparent border-b border-b-[#CFE9ED] hover:bg-gray-50 p-4 pt-6 rounded-3xl cursor-pointer">
        <div
          onClick={() => router.push(`/p/${thread.forum.slug}/${thread.id}`)}
          className="w-full"
        >
          {content}
        </div>
        <Image
          onClick={() => router.push(`/p/${thread.forum.slug}/${thread.id}`)}
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
    <article className="relative flex items-start justify-center bg-transparent border-b border-b-[#CFE9ED] hover:bg-gray-50 p-4 pt-6 rounded-3xl cursor-pointer">
      <div
        className="w-full"
        onClick={() => router.push(`/p/${thread.forum.slug}/${thread.id}`)}
      >
        {content}
      </div>
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
        className="flex h-10 w-36 cursor-pointer items-center justify-center gap-2 rounded-[12px] bg-white py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black"
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
              className="flex h-10 w-full cursor-pointer items-center px-4 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors hover:bg-[#E5E5E5]"
            >
              {option.label}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

export default function Threads({ forum = null }) {
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
  const BASE_URL =
    API_BASE_URL +
    (forum === null ? "/api/feed" : "/api/p/" + forum + "/threads");

  async function fetchThreads({
    byPagination = false,
    sort = selectedSort,
    time = selectedTimeFilter,
  }) {
    if (byPagination) {
      setMoreThreadsLoading(true);

      const response = await fetch(
        BASE_URL +
          "?page=" +
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
        BASE_URL + "?page=1" + "&time=" + time.value + "&sort=" + sort.value,
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

      <div className="flex w-full flex-col" aria-label="Дискусии">
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
