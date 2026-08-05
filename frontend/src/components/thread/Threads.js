"use client";

import Image from "next/image";
import { useId, useState, useEffect, useRef } from "react";
import { formatDistanceToNow } from "date-fns";
import { mk } from "date-fns/locale";
import { API_BASE_URL } from "@/lib/api";
import { stripHtml } from "@/lib/html";
import { useRouter } from "next/navigation";
import Cookies from "js-cookie";
import ForumEmptyState from "@/components/forum/ForumEmptyState";
import ThreadAttachments from "@/components/thread/ThreadAttachments";
import ThreadPoll from "@/components/thread/ThreadPoll";

const SORT_OPTIONS = [
  { value: "trending", label: "Трендинг" },
  { value: "top", label: "Топ" },
  { value: "newest", label: "Најнови" },
  { value: "discussed", label: "Дискутирани" },
];

const TIME_FILTER_OPTIONS = [
  { value: "all", label: "Сите" },
  { value: "day", label: "Денес" },
  { value: "week", label: "Оваа недела" },
  { value: "month", label: "Овој месец" },
  { value: "six-months", label: "6 месеци" },
  { value: "year", label: "1 година" },
];

const PAGE_SIZE = 5;

function LoadingLogo() {
  return (
    <div className="flex items-center justify-center py-6" aria-busy="true" aria-label="Се вчитува">
      <Image
        src="/logo.svg"
        alt=""
        width={96}
        height={64}
        priority
        className="h-16 w-24 animate-pulse object-contain"
      />
    </div>
  );
}

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
  const router = useRouter();
  const threadHref = `/p/${thread.forum.slug}/${thread.id}`;
  const hasAttachments = (thread.attachments?.length ?? 0) > 0;
  const hasPoll = Boolean(thread.poll);

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

  return (
    <article className="relative flex flex-col gap-4 items-start justify-center bg-transparent border-b border-b-[#CFE9ED] hover:bg-gray-50 p-4 pt-6 rounded-3xl">
      <div className="flex w-full items-start justify-between gap-8">
        <div
          className="flex min-w-0 flex-1 cursor-pointer flex-col gap-4"
          onClick={() => router.push(threadHref)}
        >
          <div className="flex flex-wrap items-center gap-2">
            <div className="bg-gray-100 rounded-xl py-1 px-2 flex gap-2 items-center">
              {thread.forum.imageUrl ? (
                <img
                  src={thread.forum.imageUrl}
                  className="w-5 h-5 rounded-full object-cover"
                  alt=""
                />
              ) : (
                <img
                  src="/avatars/default-1.svg"
                  className="w-5 h-5 rounded-full object-cover"
                  alt=""
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
                      alt=""
                    />
                  ) : (
                    <img
                      src="/avatars/default-1.svg"
                      className="w-5 h-5 rounded-full object-cover"
                      alt=""
                    />
                  )}
                  {thread.author.username}
                </div>
                {thread.author.school?.name ? (
                  <div className="bg-gray-100 rounded-xl py-1 px-2 flex gap-2 items-center">
                    {thread.author.school.name}
                  </div>
                ) : null}
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
            {thread.description ? (
              <p className="font-[family-name:var(--font-manrope)] text-[16px] font-normal leading-[22px] text-[#595959]">
                {stripHtml(thread.description)}
              </p>
            ) : null}
          </div>

          <div className="text-xs flex items-center gap-0.5">
            <img src="/eye-line.svg" alt="" />
            <span className="text-primary-300 font-bold">
              {thread.views ?? 0}
            </span>
          </div>
        </div>

        <div className="relative z-10 flex shrink-0 flex-col gap-2">
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
            onclick={() => router.push(threadHref)}
          />
        </div>
      </div>

      {hasAttachments || hasPoll ? (
        <div
          className="relative z-10 flex w-full flex-col gap-4"
          onClick={(event) => event.stopPropagation()}
        >
          {hasAttachments ? (
            <ThreadAttachments attachments={thread.attachments} />
          ) : null}
          {hasPoll ? <ThreadPoll poll={thread.poll} /> : null}
        </div>
      ) : null}
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
        className="w-40 flex py-2 px-3 rounded-xl cursor-pointer items-center justify-center gap-1 bg-gray-100 font-bold hover:bg-gray-200 transition"
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

export default function Threads({ forum = null }) {
  const sortListboxId = useId();
  const timeListboxId = useId();
  const [openSelect, setOpenSelect] = useState(null);
  const [selectedSort, setSelectedSort] = useState(SORT_OPTIONS[0]);
  const [selectedTimeFilter, setSelectedTimeFilter] = useState(
    TIME_FILTER_OPTIONS[0],
  );
  const [paginationPage, setPaginationPage] = useState(1);

  const [threads, setThreads] = useState([]);
  const [moreThreadsLoading, setMoreThreadsLoading] = useState(false);
  const [noMoreThreads, setNoMoreThreads] = useState(false);
  const [hasLoaded, setHasLoaded] = useState(false);
  const sentinelRef = useRef(null);
  const loadingRef = useRef(false);
  const noMoreRef = useRef(false);
  const pageRef = useRef(1);
  const hasLoadedRef = useRef(false);
  const BASE_URL =
    API_BASE_URL +
    (forum === null ? "/api/feed" : "/api/p/" + forum + "/threads");

  async function fetchThreads({
    byPagination = false,
    sort = selectedSort,
    time = selectedTimeFilter,
    page = paginationPage,
  }) {
    try {
      if (byPagination) {
        if (loadingRef.current) return;
        loadingRef.current = true;
        setMoreThreadsLoading(true);

        const response = await fetch(
          BASE_URL +
            "?page=" +
            page +
            "&time=" +
            time.value +
            "&sort=" +
            sort.value,
          { credentials: "include" },
        );

        if (!response.ok) {
          setNoMoreThreads(true);
          noMoreRef.current = true;
          loadingRef.current = false;
          setMoreThreadsLoading(false);
          setHasLoaded(true);
          return;
        }

        const payload = await response.json();
        const next = Array.isArray(payload.data) ? payload.data : [];
        setThreads((prev) => [...prev, ...next]);
        if (next.length < PAGE_SIZE) {
          setNoMoreThreads(true);
          noMoreRef.current = true;
        }
        loadingRef.current = false;
        setMoreThreadsLoading(false);
        setHasLoaded(true);
      } else {
        setNoMoreThreads(false);
        noMoreRef.current = false;
        setPaginationPage(1);
        pageRef.current = 1;
        const response = await fetch(
          BASE_URL + "?page=1" + "&time=" + time.value + "&sort=" + sort.value,
          { credentials: "include" },
        );

        if (!response.ok) {
          setThreads([]);
          setNoMoreThreads(true);
          noMoreRef.current = true;
          setHasLoaded(true);
          return;
        }

        const payload = await response.json();
        const next = Array.isArray(payload.data) ? payload.data : [];
        setThreads(next);
        // Page size is 5 — if the first page is short, there is nothing more to load.
        if (next.length < PAGE_SIZE) {
          setNoMoreThreads(true);
          noMoreRef.current = true;
        }
        setHasLoaded(true);
      }
    } catch {
      setThreads([]);
      loadingRef.current = false;
      setMoreThreadsLoading(false);
      setNoMoreThreads(true);
      noMoreRef.current = true;
      setHasLoaded(true);
    }
  }

  const selectSortOption = (option) => {
    setSelectedSort(option);
    setOpenSelect(null);
    setHasLoaded(false);
    fetchThreads({ byPagination: false, sort: option, page: 1 });
  };

  const selectTimeFilterOption = (option) => {
    setSelectedTimeFilter(option);
    setOpenSelect(null);
    setHasLoaded(false);
    fetchThreads({ byPagination: false, time: option, page: 1 });
  };

  useEffect(() => {
    setThreads([]);
    setHasLoaded(false);
    setNoMoreThreads(false);
    setPaginationPage(1);
    fetchThreads({ byPagination: false, page: 1 });
    // Initial / forum change load — filter changes call fetchThreads directly.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [forum]);

  useEffect(() => {
    loadingRef.current = moreThreadsLoading;
  }, [moreThreadsLoading]);

  useEffect(() => {
    noMoreRef.current = noMoreThreads;
  }, [noMoreThreads]);

  useEffect(() => {
    pageRef.current = paginationPage;
  }, [paginationPage]);

  useEffect(() => {
    hasLoadedRef.current = hasLoaded;
  }, [hasLoaded]);

  useEffect(() => {
    const node = sentinelRef.current;
    if (!node || !hasLoaded || noMoreThreads) return;

    const observer = new IntersectionObserver(
      (entries) => {
        const entry = entries[0];
        if (!entry?.isIntersecting) return;
        if (loadingRef.current || noMoreRef.current || !hasLoadedRef.current) return;

        const nextPage = pageRef.current + 1;
        pageRef.current = nextPage;
        setPaginationPage(nextPage);
        fetchThreads({ byPagination: true, page: nextPage });
      },
      { root: null, rootMargin: "240px 0px", threshold: 0 },
    );

    observer.observe(node);
    return () => observer.disconnect();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [hasLoaded, noMoreThreads, threads.length, forum, selectedSort, selectedTimeFilter]);

  // Sort only reorders; an empty list with time=all means the forum truly has no threads.
  const isTrulyEmptyForum =
    forum !== null &&
    hasLoaded &&
    threads.length === 0 &&
    selectedTimeFilter.value === "all";

  if (isTrulyEmptyForum) {
    return (
      <section className="flex w-full max-w-[990px] flex-col items-center gap-8">
        <ForumEmptyState />
      </section>
    );
  }

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

      <div className="flex w-full flex-col" aria-label="Дискусии">
        {!hasLoaded ? (
          <LoadingLogo />
        ) : threads.length === 0 ? (
          <p className="py-8 text-center font-[family-name:var(--font-manrope)] text-[16px] text-[#595959]">
            Нема дискусии за избраните филтри.
          </p>
        ) : (
          threads.map((thread) => (
            <ThreadItem key={thread.id} thread={thread} />
          ))
        )}
      </div>

      {hasLoaded && threads.length > 0 ? (
        <>
          {!noMoreThreads ? (
            <div ref={sentinelRef} className="h-1 w-full shrink-0" aria-hidden />
          ) : null}
          {moreThreadsLoading ? <LoadingLogo /> : null}
          {noMoreThreads && !moreThreadsLoading ? (
            <span className="font-bold text-primary-300 text-xl">Нема веќе :/</span>
          ) : null}
        </>
      ) : null}
    </section>
  );
}
