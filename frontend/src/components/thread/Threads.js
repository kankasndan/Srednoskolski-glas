"use client";

import Image from "next/image";
import { useCallback, useId, useState, useEffect, useRef } from "react";
import { useClickOutside } from "@/hooks/useClickOutside";
import ForumEmptyState from "@/components/forum/ForumEmptyState";
import FeedFilterSheet from "@/components/thread/FeedFilterSheet";
import NoMoreThreads from "@/components/thread/NoMoreThreads";
import ThreadCard from "@/components/thread/ThreadCard";
import { API_BASE_URL } from "@/lib/api";

const SORT_OPTIONS = [
  { value: "trending", label: "Трендинг" },
  { value: "top", label: "Популарно" },
  { value: "newest", label: "Ново" },
  { value: "discussed", label: "Истакнато" },
];

const TIME_FILTER_OPTIONS = [
  { value: "all", label: "Сите" },
  { value: "day", label: "Денес" },
  { value: "week", label: "Оваа недела" },
  { value: "month", label: "Овој месец" },
  { value: "year", label: "Оваа година" },
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
  const shownOptions = options.filter((option) => option.value !== selected.value);

  return (
    <div className="relative h-10 w-36 shrink-0">
      <input type="hidden" name={name} value={selected.value} />

      <button
        type="button"
        aria-haspopup="listbox"
        aria-expanded={isOpen}
        aria-controls={listboxId}
        onClick={onToggle}
        className={`flex h-10 w-36 cursor-pointer items-center justify-center gap-2 px-3 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors ${
          isOpen ? "rounded-t-xl" : "rounded-xl"
        } ${isOpen ? "bg-[#CFE9ED]" : "bg-white hover:bg-[#CFE9ED]"}`}
      >
        <span className="flex h-[19px] items-center">{selected.label}</span>
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
          className="absolute left-0 top-10 z-20 flex w-36 flex-col overflow-hidden rounded-b-xl bg-white shadow-[0_12px_24px_rgba(0,0,0,0.12)]"
        >
          {shownOptions.map((option) => (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={selected.value === option.value}
              onClick={() => onSelect(option)}
              className="flex h-10 w-full cursor-pointer items-center justify-center border-t border-[#CCCCCC] px-4 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black transition-colors hover:bg-[#E5E5E5]"
            >
              {option.label}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

export default function Threads({
  forum = null,
  searchQuery = null,
  defaultSort = "trending",
  showSort = true,
  staticThreads = null,
  listPath = null,
}) {
  const isSearch = searchQuery !== null;
  const hasStaticThreads = Array.isArray(staticThreads);
  const sortListboxId = useId();
  const timeListboxId = useId();
  const [openSelect, setOpenSelect] = useState(null);
  const [filtersOpen, setFiltersOpen] = useState(false);
  const filterContainerRef = useRef(null);
  const [selectedSort, setSelectedSort] = useState(
    SORT_OPTIONS.find((option) => option.value === defaultSort) ?? SORT_OPTIONS[0],
  );
  const [selectedTimeFilter, setSelectedTimeFilter] = useState(
    TIME_FILTER_OPTIONS[0],
  );
  const [paginationPage, setPaginationPage] = useState(1);

  const [threads, setThreads] = useState(hasStaticThreads ? staticThreads : []);
  const [moreThreadsLoading, setMoreThreadsLoading] = useState(false);
  const [noMoreThreads, setNoMoreThreads] = useState(hasStaticThreads);
  const [hasLoaded, setHasLoaded] = useState(hasStaticThreads);
  const sentinelRef = useRef(null);
  const loadingRef = useRef(false);
  const noMoreRef = useRef(false);
  const pageRef = useRef(1);
  const hasLoadedRef = useRef(false);

  function buildListUrl({ page, sort, time }) {
    const params = new URLSearchParams({
      page: String(page),
      time: time.value,
      sort: sort.value,
    });

    if (isSearch) {
      if (searchQuery) params.set("q", searchQuery);
      if (forum) params.set("forum", forum);
      return `${API_BASE_URL}/api/search?${params}`;
    }

    if (listPath) {
      return `${API_BASE_URL}${listPath}?${params}`;
    }

    const path = forum === null ? "/api/feed" : `/api/p/${forum}/threads`;
    return `${API_BASE_URL}${path}?${params}`;
  }

  async function fetchThreads({
    append = false,
    sort = selectedSort,
    time = selectedTimeFilter,
    page = paginationPage,
  } = {}) {
    if (hasStaticThreads) return;

    if (append) {
      if (loadingRef.current) return;
      loadingRef.current = true;
      setMoreThreadsLoading(true);
    } else {
      setHasLoaded(false);
      setNoMoreThreads(false);
      noMoreRef.current = false;
      setPaginationPage(1);
      pageRef.current = 1;
      page = 1;
    }

    try {
      const response = await fetch(buildListUrl({ page, sort, time }), {
        credentials: "include",
      });

      if (!response.ok) {
        if (!append) setThreads([]);
        setNoMoreThreads(true);
        noMoreRef.current = true;
        return;
      }

      const payload = await response.json();
      const next = Array.isArray(payload.data) ? payload.data : [];

      setThreads((prev) => (append ? [...prev, ...next] : next));

      if (next.length < PAGE_SIZE) {
        setNoMoreThreads(true);
        noMoreRef.current = true;
      }
    } catch {
      if (!append) setThreads([]);
      setNoMoreThreads(true);
      noMoreRef.current = true;
    } finally {
      loadingRef.current = false;
      setMoreThreadsLoading(false);
      setHasLoaded(true);
    }
  }

  const selectSortOption = (option) => {
    setSelectedSort(option);
    setOpenSelect(null);
    if (!hasStaticThreads) {
      setHasLoaded(false);
      fetchThreads({ append: false, sort: option, page: 1 });
    }
  };

  const selectTimeFilterOption = (option) => {
    setSelectedTimeFilter(option);
    setOpenSelect(null);
    if (!hasStaticThreads) {
      setHasLoaded(false);
      fetchThreads({ append: false, time: option, page: 1 });
    }
  };

  useClickOutside(
    filterContainerRef,
    useCallback(() => setOpenSelect(null), []),
    Boolean(openSelect),
  );

  useEffect(() => {
    if (hasStaticThreads) return;

    const initialSort =
      SORT_OPTIONS.find((option) => option.value === defaultSort) ?? SORT_OPTIONS[0];

    void Promise.resolve().then(() => {
      setSelectedSort(initialSort);
      setHasLoaded(false);
      fetchThreads({ append: false, sort: initialSort, page: 1 });
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [forum, searchQuery, defaultSort, hasStaticThreads, staticThreads, listPath]);

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
    if (!node || !hasLoaded || noMoreThreads || hasStaticThreads) return;

    const observer = new IntersectionObserver(
      (entries) => {
        const entry = entries[0];
        if (!entry?.isIntersecting) return;
        if (loadingRef.current || noMoreRef.current || !hasLoadedRef.current) return;

        const nextPage = pageRef.current + 1;
        pageRef.current = nextPage;
        setPaginationPage(nextPage);
        fetchThreads({ append: true, page: nextPage });
      },
      { root: null, rootMargin: "240px 0px", threshold: 0 },
    );

    observer.observe(node);
    return () => observer.disconnect();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [hasLoaded, noMoreThreads, threads.length, forum, searchQuery, selectedSort, selectedTimeFilter, hasStaticThreads, listPath]);

  // Sort only reorders; an empty list with time=all means the forum truly has no threads.
  const isTrulyEmptyForum =
    !isSearch &&
    forum !== null &&
    hasLoaded &&
    threads.length === 0 &&
    selectedTimeFilter.value === TIME_FILTER_OPTIONS[0].value;

  if (isTrulyEmptyForum) {
    return (
      <section className="flex w-full max-w-[990px] flex-col items-center gap-8">
        <ForumEmptyState />
      </section>
    );
  }

  return (
    <section className="flex w-full max-w-[990px] flex-col items-center gap-8">
      {/* Na mobilen mesto dvata dropdown-a stoi kopce Филтери. */}
      <button
        type="button"
        onClick={() => setFiltersOpen(true)}
        className="flex h-10 cursor-pointer items-center gap-2 self-start rounded-xl p-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black md:hidden"
      >
        <Image src="/mobile version/filter.svg" alt="" width={24} height={24} className="size-6" />
        Филтери
      </button>

      <FeedFilterSheet
        open={filtersOpen}
        onClose={() => setFiltersOpen(false)}
        sortOptions={SORT_OPTIONS}
        selectedSort={selectedSort}
        onSelectSort={(option) => {
          selectSortOption(option);
          setFiltersOpen(false);
        }}
        timeOptions={TIME_FILTER_OPTIONS}
        selectedTime={selectedTimeFilter}
        onSelectTime={(option) => {
          selectTimeFilterOption(option);
          setFiltersOpen(false);
        }}
      />

      <div ref={filterContainerRef} className="hidden self-end gap-2 md:flex">
        {showSort ? (
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
        ) : null}

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
            {isSearch && searchQuery.trim()
              ? "Нема резултати за ова пребарување."
              : "Нема дискусии за избраните филтри."}
          </p>
        ) : (
          threads.map((thread) => (
            <ThreadCard key={thread.id} thread={thread} />
          ))
        )}
      </div>

      {!hasStaticThreads && hasLoaded && threads.length > 0 ? (
        <>
          {!noMoreThreads ? (
            <div ref={sentinelRef} className="h-1 w-full shrink-0" aria-hidden />
          ) : null}
          {moreThreadsLoading ? <LoadingLogo /> : null}
          {noMoreThreads && !moreThreadsLoading ? <NoMoreThreads /> : null}
        </>
      ) : null}
    </section>
  );
}
