"use client";

import { useCallback, useEffect, useId, useRef, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import "@fortawesome/fontawesome-svg-core/styles.css";
import { config } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faXmark } from "@fortawesome/free-solid-svg-icons";
import { useClickOutside } from "@/hooks/useClickOutside";
import { getForum } from "@/api/forums";
import { searchContent } from "@/api/search";
import ForumIcon from "@/components/forum/ForumIcon";
import HighlightedText from "@/components/search/HighlightedText";
import { stripHtml } from "@/lib/html";

config.autoAddCss = false;

const DEBOUNCE_MS = 280;
const DROPDOWN_PAGE_SIZE = 5;

function forumSlugFromPath(pathname) {
  const match = pathname?.match(/^\/p\/([^/]+)/);
  return match?.[1] ?? null;
}

function snippetAround(text, query, max = 90) {
  const plain = stripHtml(text);
  if (!plain) return "";
  const q = query.trim();
  if (!q) return plain.slice(0, max);
  const index = plain.toLowerCase().indexOf(q.toLowerCase());
  if (index < 0) return plain.slice(0, max);
  const start = Math.max(0, index - 24);
  const end = Math.min(plain.length, index + q.length + 48);
  const slice = `${start > 0 ? "…" : ""}${plain.slice(start, end)}${end < plain.length ? "…" : ""}`;
  return slice;
}

function SectionLabel({ children }) {
  return (
    <li
      role="presentation"
      className="px-4 pb-1 pt-2 font-[family-name:var(--font-manrope)] text-[11px] font-bold uppercase tracking-wide text-[#8A8A8A]"
    >
      {children}
    </li>
  );
}

function ForumOption({ forum, query, onSelect }) {
  return (
    <li>
      <Link
        href={`/p/${forum.slug}`}
        role="option"
        onClick={onSelect}
        className="flex cursor-pointer items-center gap-3 px-4 py-2.5 hover:bg-[#F5F5F5]"
      >
        <ForumIcon src={forum.imageUrl} className="size-5" />
        <p className="min-w-0 flex-1 truncate font-[family-name:var(--font-manrope)] text-[14px] font-medium text-black">
          <HighlightedText text={forum.name} query={query} />
        </p>
      </Link>
    </li>
  );
}

export default function SearchBar() {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const listboxId = useId();
  const rootRef = useRef(null);
  const debounceRef = useRef(null);

  const qFromUrl = pathname === "/search" ? (searchParams.get("q") ?? "") : "";
  const forumFromUrl = pathname === "/search" ? searchParams.get("forum") : null;
  const forumFromPath = forumSlugFromPath(pathname);
  const routeForumSlug = forumFromUrl || forumFromPath;

  const [query, setQuery] = useState(qFromUrl);
  const [focused, setFocused] = useState(false);
  const [filterDismissed, setFilterDismissed] = useState(false);
  const [forumMeta, setForumMeta] = useState(null);
  const [results, setResults] = useState({ threads: [], forums: [] });
  const [loading, setLoading] = useState(false);
  const [dropdownOpen, setDropdownOpen] = useState(false);

  const activeForumSlug = filterDismissed ? null : routeForumSlug;
  const showForumChip =
    Boolean(activeForumSlug) &&
    (focused || query.length > 0 || pathname === "/search");

  useEffect(() => {
    setQuery(qFromUrl);
  }, [qFromUrl]);

  useEffect(() => {
    setFilterDismissed(false);
  }, [routeForumSlug]);

  useEffect(() => {
    if (!showForumChip || !activeForumSlug) {
      if (!showForumChip) setForumMeta(null);
      return;
    }

    let cancelled = false;
    getForum(activeForumSlug)
      .then((forum) => {
        if (!cancelled) setForumMeta(forum);
      })
      .catch(() => {
        if (!cancelled) setForumMeta({ slug: activeForumSlug, name: activeForumSlug });
      });

    return () => {
      cancelled = true;
    };
  }, [showForumChip, activeForumSlug]);

  const runSearch = useCallback(async (value, forumSlug) => {
    const q = value.trim();
    if (!q) {
      setResults({ threads: [], forums: [] });
      setLoading(false);
      setDropdownOpen(false);
      return;
    }

    setLoading(true);
    setDropdownOpen(true);
    try {
      const payload = await searchContent({
        q,
        forum: forumSlug,
        page: 1,
        perPage: DROPDOWN_PAGE_SIZE,
      });
      setResults({
        threads: Array.isArray(payload.data) ? payload.data : [],
        forums: Array.isArray(payload.forums) ? payload.forums : [],
      });
    } catch {
      setResults({ threads: [], forums: [] });
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (debounceRef.current) clearTimeout(debounceRef.current);
    const q = query.trim();
    if (!q) {
      setResults({ threads: [], forums: [] });
      setLoading(false);
      setDropdownOpen(false);
      return;
    }

    debounceRef.current = setTimeout(() => {
      void runSearch(query, activeForumSlug);
    }, DEBOUNCE_MS);

    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, [query, activeForumSlug, runSearch]);

  useClickOutside(
    rootRef,
    useCallback(() => {
      setFocused(false);
      setDropdownOpen(false);
    }, []),
  );

  function goToResults(nextQuery = query, forumSlug = activeForumSlug) {
    const params = new URLSearchParams();
    const trimmed = nextQuery.trim();
    if (trimmed) params.set("q", trimmed);
    if (forumSlug) params.set("forum", forumSlug);
    const qs = params.toString();
    setDropdownOpen(false);
    setFocused(false);
    router.push(qs ? `/search?${qs}` : "/search");
  }

  function removeForumFilter(event) {
    event.preventDefault();
    event.stopPropagation();
    setFilterDismissed(true);
    setForumMeta(null);
    if (pathname === "/search") {
      const params = new URLSearchParams(searchParams.toString());
      params.delete("forum");
      const qs = params.toString();
      router.replace(qs ? `/search?${qs}` : "/search");
    }
  }

  function handleSubmit(event) {
    event.preventDefault();
    goToResults();
  }

  const hasHits = results.forums.length > 0 || results.threads.length > 0;
  const generalForums = results.forums.filter((forum) => forum.type !== "school");
  const schoolForums = results.forums.filter((forum) => forum.type === "school");

  const closeDropdown = useCallback(() => {
    setDropdownOpen(false);
    setFocused(false);
  }, []);
  const showDropdown = focused && dropdownOpen && query.trim().length > 0;

  return (
    <div ref={rootRef} className="relative w-[632px] max-w-full">
      <form
        role="search"
        onSubmit={handleSubmit}
        className="flex h-10 items-center gap-3 rounded-xl border border-[#CCCCCC] px-3"
      >
        <Image
          src="/search.svg"
          alt=""
          width={16}
          height={16}
          className="shrink-0"
        />
        <input
          type="search"
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          onFocus={() => {
            setFocused(true);
            if (query.trim()) setDropdownOpen(true);
          }}
          onKeyDown={(event) => {
            if (event.key === "Escape") {
              setDropdownOpen(false);
              event.currentTarget.blur();
            }
          }}
          placeholder="Пребарај дискусии..."
          aria-autocomplete="list"
          aria-controls={listboxId}
          aria-expanded={showDropdown}
          className="min-w-0 flex-1 bg-transparent font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-[#595959] placeholder:text-[#595959] outline-none [&::-webkit-search-cancel-button]:hidden"
        />
        {showForumChip ? (
          <button
            type="button"
            onClick={removeForumFilter}
            aria-label="Отстрани филтер"
            title={forumMeta?.name ?? "Отстрани филтер"}
            className="flex h-[18px] shrink-0 cursor-pointer items-center gap-1 rounded-xl bg-[#F5F5F5] px-2 py-0.5 font-[family-name:var(--font-manrope)] text-[10px] font-normal leading-none text-black transition-colors hover:bg-[#EBEBEB]"
          >
            <span>{forumMeta?.name ?? "Форум"}</span>
            <FontAwesomeIcon icon={faXmark} className="size-2 shrink-0" />
          </button>
        ) : null}
      </form>

      {showDropdown ? (
        <div
          id={listboxId}
          aria-label="Предлози за пребарување"
          className="absolute left-0 right-0 top-full z-[60] mt-2 overflow-hidden rounded-xl border border-[#CCCCCC] bg-white shadow-[0_8px_24px_rgba(0,0,0,0.08)]"
        >
          {loading && !hasHits ? (
            <p className="px-4 py-3 font-[family-name:var(--font-manrope)] text-[13px] text-[#595959]">
              Се пребарува…
            </p>
          ) : !hasHits ? (
            <p className="px-4 py-3 font-[family-name:var(--font-manrope)] text-[13px] text-[#595959]">
              Нема резултати.
            </p>
          ) : (
            <ul role="listbox" aria-label="Предлози за пребарување" className="flex flex-col py-1">
              {generalForums.length > 0 ? (
                <SectionLabel>Општи форуми</SectionLabel>
              ) : null}
              {generalForums.map((forum) => (
                <ForumOption
                  key={`forum-${forum.id}`}
                  forum={forum}
                  query={query}
                  onSelect={closeDropdown}
                />
              ))}

              {schoolForums.length > 0 ? (
                <SectionLabel>Училишни форуми</SectionLabel>
              ) : null}
              {schoolForums.map((forum) => (
                <ForumOption
                  key={`forum-${forum.id}`}
                  forum={forum}
                  query={query}
                  onSelect={closeDropdown}
                />
              ))}

              {results.threads.length > 0 ? (
                <SectionLabel>Дискусии</SectionLabel>
              ) : null}
              {results.threads.map((thread) => {
                const slug = thread.forum?.slug;
                const href = slug ? `/p/${slug}/${thread.id}` : "/search";
                const preview = snippetAround(thread.description, query);
                return (
                  <li key={`thread-${thread.id}`}>
                    <Link
                      href={href}
                      role="option"
                      onClick={closeDropdown}
                      className="flex cursor-pointer flex-col gap-0.5 px-4 py-2.5 hover:bg-[#F5F5F5]"
                    >
                      <p className="truncate font-[family-name:var(--font-manrope)] text-[14px] font-medium text-black">
                        <HighlightedText text={thread.title} query={query} />
                      </p>
                      {preview ? (
                        <p className="line-clamp-1 font-[family-name:var(--font-manrope)] text-[12px] text-[#595959]">
                          <HighlightedText text={preview} query={query} />
                        </p>
                      ) : null}
                      {thread.forum?.name ? (
                        <p className="font-[family-name:var(--font-manrope)] text-[12px] text-[#8A8A8A]">
                          {thread.forum.name}
                        </p>
                      ) : null}
                    </Link>
                  </li>
                );
              })}
            </ul>
          )}

          {/* Enter vodi do celosnata strana so rezultati; ova e vidliviot ekvivalent. */}
          <button
            type="button"
            onClick={() => goToResults()}
            className="flex w-full cursor-pointer items-center gap-2 border-t border-[#EBEBEB] px-4 py-2.5 text-left font-[family-name:var(--font-manrope)] text-[13px] font-medium text-[var(--color-primary-200)] hover:bg-[#F5F5F5]"
          >
            Прикажи ги сите резултати за „{query.trim()}“
          </button>
        </div>
      ) : null}
    </div>
  );
}

export function SearchBarFallback() {
  return (
    <div className="flex h-10 w-[632px] max-w-full items-center gap-4 rounded-xl border border-[#CCCCCC] px-4 py-2">
      <Image
        src="/search.svg"
        alt=""
        width={16}
        height={16}
        className="shrink-0"
      />
      <span className="w-full font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-[#595959]">
        Пребарај дискусии...
      </span>
    </div>
  );
}
