"use client";

import Link from "next/link";
import Image from "next/image";
import { useCallback, useId, useRef, useState } from "react";
import Avatar from "@/components/ui/Avatar";
import LogoutDialogs from "@/components/shell/LogoutDialogs";
import NotificationBell from "@/components/shell/NotificationBell";
import { useClickOutside } from "@/hooks/useClickOutside";
import { useLogout } from "@/hooks/useLogout";
import { useProfile } from "@/hooks/useProfile";

const menuItemClass =
  "flex h-12 w-full cursor-pointer items-center gap-3 px-4 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none transition-colors hover:bg-[#E5E5E5]";

export default function AuthButtons() {
  // Zaednichka sesija, za da reagira i na odjava od druga strana.
  const { user } = useProfile();
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef(null);
  const menuId = useId();
  const logout = useLogout({ onLoggedOut: () => setMenuOpen(false) });

  useClickOutside(menuRef, useCallback(() => setMenuOpen(false), []), menuOpen);

  const displayName = user?.username || "Профил";
  const avatarUrl = user?.imageUrl;

  // Dijalozite se nadvor od granite: po odjava `user` e null i bi ischeznale.
  return (
    <>
      {user ? (
        <div className="flex h-14 shrink-0 items-center gap-2">
          <NotificationBell />
          <div ref={menuRef} className="relative h-14 w-fit shrink-0">
            <button
              type="button"
              onClick={() => setMenuOpen((open) => !open)}
              aria-haspopup="menu"
              aria-expanded={menuOpen}
              aria-controls={menuId}
              title={displayName}
              className={`flex h-14 w-full cursor-pointer items-center gap-3 px-3 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5] focus-visible:ring-offset-2 ${
                menuOpen ? "rounded-t-xl bg-[#CFE9ED]" : "rounded-xl bg-white hover:bg-[#DCEBED]"
              }`}
            >
              <Avatar src={avatarUrl} size="lg" alt={displayName} />

              <span className="min-w-0 max-w-[140px] text-left font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black">
                {displayName}
              </span>

              <Image
                src="/chevron-down.svg"
                alt=""
                width={16}
                height={16}
                className={`size-4 shrink-0 transition-transform ${menuOpen ? "rotate-180" : ""}`}
              />
            </button>

            {menuOpen && (
              <div
                id={menuId}
                role="menu"
                className="absolute left-0 top-14 z-50 flex w-full flex-col overflow-hidden rounded-b-xl border-x border-b border-[#CCCCCC] bg-white"
              >
                <Link
                  href="/profile"
                  role="menuitem"
                  onClick={() => setMenuOpen(false)}
                  className={`${menuItemClass} text-black`}
                >
                  <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" className="size-4 shrink-0">
                    <circle cx="8" cy="5.5" r="2.5" stroke="currentColor" strokeWidth="1.5" />
                    <path
                      d="M3 13.5C3 11.29 5.24 9.5 8 9.5C10.76 9.5 13 11.29 13 13.5"
                      stroke="currentColor"
                      strokeWidth="1.5"
                      strokeLinecap="round"
                    />
                  </svg>
                  Профил
                </Link>

                <button
                  type="button"
                  role="menuitem"
                  onClick={() => {
                    setMenuOpen(false);
                    logout.ask();
                  }}
                  disabled={logout.loggingOut}
                  className={`${menuItemClass} border-t border-[#CCCCCC] text-[var(--color-error)] disabled:cursor-not-allowed disabled:opacity-60`}
                >
                  <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" className="size-4 shrink-0">
                    <path
                      d="M6 2.5H3.5C2.95 2.5 2.5 2.95 2.5 3.5V12.5C2.5 13.05 2.95 13.5 3.5 13.5H6"
                      stroke="currentColor"
                      strokeWidth="1.5"
                      strokeLinecap="round"
                    />
                    <path
                      d="M10 11L13.5 8L10 5"
                      stroke="currentColor"
                      strokeWidth="1.5"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                    <path d="M13.5 8H6" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" />
                  </svg>
                  {logout.loggingOut ? "Се одјавува…" : "Одјави се"}
                </button>
              </div>
            )}
          </div>
        </div>
      ) : (
        <div className="ml-auto flex h-10 w-75 shrink-0 items-center gap-3">
          <Link
            href="/login"
            className="flex h-10 w-36 cursor-pointer items-center justify-center gap-4 rounded-xl border border-[#582FF5] px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-[#0A0A0A] transition-colors hover:border-[#582FF5] hover:bg-[#E5E5E5] hover:text-(--color-grays-900)"
          >
            Најави се
          </Link>
          <Link
            href="/register"
            className="flex h-10 w-36 cursor-pointer items-center justify-center gap-4 rounded-xl border border-[#582FF5] bg-[#582FF5] px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-white transition-colors hover:border-[#3300F5] hover:bg-[#3300F5]"
          >
            Регистрација
          </Link>
        </div>
      )}

      <LogoutDialogs logout={logout} />
    </>
  );
}
