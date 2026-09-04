"use client";

import Link from "next/link";
import { useCallback, useId, useRef, useState } from "react";
import Avatar from "@/components/ui/Avatar";
import { useClickOutside } from "@/hooks/useClickOutside";
import { useNotifications } from "@/hooks/useNotifications";
import { formatPostedAgo } from "@/lib/time";
import { normalizeNotificationHref } from "@/lib/commentLink";

function BellIcon({ className }) {
  return (
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" className={className}>
      <path
        d="M6 9.5C6 6.46 8.46 4 11.5 4H12.5C15.54 4 18 6.46 18 9.5V13L20 16H4L6 13V9.5Z"
        stroke="currentColor"
        strokeWidth="1.5"
        strokeLinejoin="round"
      />
      <path
        d="M10 19C10.35 19.94 11.18 20.5 12 20.5C12.82 20.5 13.65 19.94 14 19"
        stroke="currentColor"
        strokeWidth="1.5"
        strokeLinecap="round"
      />
    </svg>
  );
}

export default function NotificationBell({ compact = false }) {
  const [open, setOpen] = useState(false);
  const menuRef = useRef(null);
  const menuId = useId();
  const { items, unreadCount, loading, markRead, markAllRead } = useNotifications(true);

  useClickOutside(menuRef, useCallback(() => setOpen(false), []), open);

  const badge = unreadCount > 9 ? "9+" : String(unreadCount);
  const label =
    unreadCount > 0
      ? `Известувања, ${unreadCount} непрочитани`
      : "Известувања";

  return (
    <div ref={menuRef} className="relative shrink-0">
      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        aria-label={label}
        aria-haspopup="menu"
        aria-expanded={open}
        aria-controls={menuId}
        title="Известувања"
        className={`relative flex cursor-pointer items-center justify-center text-black transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5] focus-visible:ring-offset-2 ${
          compact
            ? "size-11 rounded-full hover:bg-[#DCEBED] md:size-13"
            : `size-14 rounded-full ${open ? "bg-[#CFE9ED]" : "bg-white hover:bg-[#DCEBED]"}`
        }`}
      >
        <BellIcon className={compact ? "size-6" : "size-7"} />
        {unreadCount > 0 && (
          <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#582FF5] px-1 font-[family-name:var(--font-manrope)] text-[10px] font-bold leading-none text-white">
            {badge}
          </span>
        )}
      </button>

      {open && (
        <div
          id={menuId}
          role="menu"
          className={`z-50 overflow-hidden rounded-xl border border-[#CCCCCC] bg-white shadow-lg ${
            compact
              ? "fixed right-4 top-16 w-[min(20rem,calc(100vw-2rem))] md:right-6"
              : "absolute right-0 top-14 w-[320px]"
          }`}
        >
          <div className="flex items-center justify-between gap-2 border-b border-[#CCCCCC] px-4 py-3">
            <span className="font-[family-name:var(--font-manrope)] text-[14px] font-bold text-black">
              Известувања
            </span>
            {unreadCount > 0 && (
              <button
                type="button"
                onClick={() => markAllRead()}
                className="cursor-pointer font-[family-name:var(--font-manrope)] text-[11px] font-semibold text-[#582FF5] hover:underline"
              >
                Обележи ги сите како прочитани
              </button>
            )}
          </div>

          <div className="max-h-80 overflow-y-auto">
            {loading && items.length === 0 ? (
              <p className="px-4 py-6 text-center font-[family-name:var(--font-manrope)] text-[13px] text-[#808080]">
                Се вчитува…
              </p>
            ) : items.length === 0 ? (
              <p className="px-4 py-6 text-center font-[family-name:var(--font-manrope)] text-[13px] text-[#808080]">
                Нема известувања.
              </p>
            ) : (
              items.map((item) => {
                const unread = item.read_at == null;

                return (
                  <Link
                    key={item.id}
                    href={normalizeNotificationHref(item.url)}
                    role="menuitem"
                    onClick={() => {
                      setOpen(false);
                      if (unread) markRead(item.id);
                    }}
                    className={`flex w-full items-start gap-3 px-4 py-3 text-left transition-colors hover:bg-[#E5E5E5] ${
                      unread ? "bg-[#F4F0FF]" : "bg-white"
                    }`}
                  >
                    <Avatar
                      src={item.actor_image_url}
                      size="md"
                      alt={item.actor_username || ""}
                    />
                    <span className="min-w-0 flex-1">
                      <span className="flex items-start justify-between gap-2">
                        <span className="font-[family-name:var(--font-manrope)] text-[13px] font-bold leading-tight text-black">
                          {item.title}
                        </span>
                        {unread && (
                          <span className="mt-1 size-2 shrink-0 rounded-full bg-[#582FF5]" />
                        )}
                      </span>
                      <span className="mt-0.5 block font-[family-name:var(--font-manrope)] text-[12px] leading-snug text-[#595959]">
                        {item.message}
                      </span>
                      <span className="mt-1 block font-[family-name:var(--font-manrope)] text-[11px] text-[#808080]">
                        {formatPostedAgo(item.created_at)}
                      </span>
                    </span>
                  </Link>
                );
              })
            )}
          </div>
        </div>
      )}
    </div>
  );
}
