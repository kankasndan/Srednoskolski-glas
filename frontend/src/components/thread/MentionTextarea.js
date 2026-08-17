"use client";

import { useCallback, useEffect, useId, useRef, useState } from "react";
import { searchUsers } from "@/api/users";
import Avatar from "@/components/ui/Avatar";
import { useClickOutside } from "@/hooks/useClickOutside";
import { getMentionDraft } from "@/lib/mentions";

const DEBOUNCE_MS = 200;

export default function MentionTextarea({
  value,
  onChange,
  maxLength,
  placeholder,
  className,
  disabled = false,
  autoFocus = false,
  "aria-label": ariaLabel,
}) {
  const listboxId = useId();
  const rootRef = useRef(null);
  const textareaRef = useRef(null);
  const debounceRef = useRef(null);
  const requestIdRef = useRef(0);

  const [draft, setDraft] = useState(null);
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  const [activeIndex, setActiveIndex] = useState(0);
  const open = draft !== null;

  const listRef = useRef(null);
  const optionRefs = useRef([]);

  const close = useCallback(() => {
    setDraft(null);
    setResults([]);
    setLoading(false);
    setActiveIndex(0);
  }, []);

  useClickOutside(rootRef, close, open);

  function updateDraftFromCaret() {
    const textarea = textareaRef.current;
    if (!textarea) return;
    setDraft(getMentionDraft(textarea.value, textarea.selectionStart));
  }

  function handleChange(event) {
    onChange(event.target.value);
    const textarea = event.target;
    setDraft(getMentionDraft(textarea.value, textarea.selectionStart));
  }

  useEffect(() => {
    if (!open) return;

    const query = draft.query;
    const requestId = ++requestIdRef.current;
    setLoading(true);

    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(async () => {
      try {
        const users = await searchUsers(query);
        if (requestId !== requestIdRef.current) return;
        setResults(users);
        setActiveIndex(0);
        optionRefs.current = [];
      } catch {
        if (requestId !== requestIdRef.current) return;
        setResults([]);
      } finally {
        if (requestId === requestIdRef.current) setLoading(false);
      }
    }, DEBOUNCE_MS);

    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, [open, draft?.query]);

  useEffect(() => {
    const list = listRef.current;
    const option = optionRefs.current[activeIndex];
    if (!list || !option) return;

    const listTop = list.scrollTop;
    const listBottom = listTop + list.clientHeight;
    const optionTop = option.offsetTop;
    const optionBottom = optionTop + option.offsetHeight;

    if (optionBottom > listBottom) {
      list.scrollTop = optionBottom - list.clientHeight;
    } else if (optionTop < listTop) {
      list.scrollTop = optionTop;
    }
  }, [activeIndex, results]);

  function insertMention(user) {
    if (!draft || !user?.username) return;

    const before = value.slice(0, draft.start);
    const after = value.slice(draft.cursor);
    let inserted = `@${user.username}`;
    if (after !== "" && !/^\s/.test(after)) {
      inserted += " ";
    } else if (after === "") {
      inserted += " ";
    }

    let next = `${before}${inserted}${after}`;
    if (maxLength && next.length > maxLength) {
      next = next.slice(0, maxLength);
    }

    onChange(next);
    close();

    const caret = Math.min(before.length + inserted.length, next.length);
    requestAnimationFrame(() => {
      const textarea = textareaRef.current;
      if (!textarea) return;
      textarea.focus();
      textarea.setSelectionRange(caret, caret);
    });
  }

  function handleKeyDown(event) {
    if (!open) return;

    if (event.key === "Escape") {
      event.preventDefault();
      close();
      return;
    }

    if (event.key === "ArrowDown") {
      event.preventDefault();
      if (results.length === 0) return;
      setActiveIndex((index) => (index + 1) % results.length);
      return;
    }

    if (event.key === "ArrowUp") {
      event.preventDefault();
      if (results.length === 0) return;
      setActiveIndex((index) => (index - 1 + results.length) % results.length);
      return;
    }

    if ((event.key === "Enter" || event.key === "Tab") && results[activeIndex]) {
      event.preventDefault();
      insertMention(results[activeIndex]);
    }
  }

  return (
    <div ref={rootRef} className="relative w-full">
      <textarea
        ref={textareaRef}
        value={value}
        onChange={handleChange}
        onKeyDown={handleKeyDown}
        onKeyUp={updateDraftFromCaret}
        onClick={updateDraftFromCaret}
        maxLength={maxLength}
        placeholder={placeholder}
        aria-label={ariaLabel}
        aria-autocomplete="list"
        aria-expanded={open}
        aria-controls={open ? listboxId : undefined}
        autoFocus={autoFocus}
        disabled={disabled}
        className={className}
      />

      {open ? (
        <div
          id={listboxId}
          role="listbox"
          aria-label="Корисници"
          className="absolute bottom-full left-0 z-30 mb-2 w-full max-w-72 overflow-hidden rounded-xl border border-[#CCCCCC] bg-white shadow-lg"
        >
          {loading && results.length === 0 ? (
            <p className="px-3 py-2.5 font-[family-name:var(--font-manrope)] text-[13px] text-[#595959]">
              Се пребарува…
            </p>
          ) : results.length === 0 ? (
            <p className="px-3 py-2.5 font-[family-name:var(--font-manrope)] text-[13px] text-[#595959]">
              Нема пронајден корисник
            </p>
          ) : (
            <ul ref={listRef} className="max-h-56 overflow-y-auto py-1">
              {results.map((user, index) => {
                const active = index === activeIndex;
                return (
                  <li
                    key={user.id}
                    ref={(node) => {
                      optionRefs.current[index] = node;
                    }}
                    role="option"
                    aria-selected={active}
                  >
                    <button
                      type="button"
                      onMouseDown={(event) => event.preventDefault()}
                      onClick={() => insertMention(user)}
                      className={`flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-left font-[family-name:var(--font-manrope)] transition-colors ${
                        active ? "bg-[#DCEBED]" : "hover:bg-[#F7F7F7]"
                      }`}
                    >
                      <Avatar src={user.imageUrl} size="sm" alt="" />
                      <span className="truncate text-[14px] font-bold text-black">
                        @{user.username}
                      </span>
                    </button>
                  </li>
                );
              })}
            </ul>
          )}
        </div>
      ) : null}
    </div>
  );
}
