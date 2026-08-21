"use client";

import Link from "next/link";
import Image from "next/image";
import { useCallback, useId, useRef, useState } from "react";
import Avatar from "@/components/ui/Avatar";
import LogoutDialogs from "@/components/shell/LogoutDialogs";
import { useClickOutside } from "@/hooks/useClickOutside";
import { useLogout } from "@/hooks/useLogout";
import { useProfile } from "@/hooks/useProfile";

const HEADER_AVATAR_SIZE_CLASS =
  "h-[56px] w-[56px] min-h-[56px] min-w-[56px] max-h-[56px] max-w-[56px]";

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
        <div ref={menuRef} className="flex shrink-0 items-center gap-1">
          <Link
            href="/profile"
            aria-label="Профил"
            onClick={() => setMenuOpen(false)}
            className={`flex ${HEADER_AVATAR_SIZE_CLASS} shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5] focus-visible:ring-offset-2`}
          >
            <Avatar
              src={avatarUrl}
              size="xl"
              sizeClassName={HEADER_AVATAR_SIZE_CLASS}
              alt={displayName}
            />
          </Link>

          <div className="relative h-10 w-fit max-w-[180px] shrink-0">
            <button
              type="button"
              onClick={() => setMenuOpen((open) => !open)}
              aria-haspopup="menu"
              aria-expanded={menuOpen}
              aria-controls={menuId}
              title={displayName}
              className={`flex h-10 w-fit max-w-[180px] cursor-pointer items-center justify-center gap-1 p-1 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-black transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5] focus-visible:ring-offset-2 ${
                menuOpen ? "rounded-t-xl bg-[#CFE9ED]" : "rounded-xl bg-white hover:bg-[#CFE9ED]"
              }`}
            >
              <span className="flex min-w-0 max-w-[140px] items-center truncate font-[family-name:var(--font-roboto)] text-[16px] font-normal leading-4 tracking-normal">
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
                className="absolute left-0 top-10 z-50 flex min-w-full flex-col overflow-hidden rounded-b-xl bg-white shadow-[0_12px_24px_rgba(0,0,0,0.12)]"
              >
                <Link
                  href="/profile"
                  role="menuitem"
                  onClick={() => setMenuOpen(false)}
                  className="flex h-10 w-full cursor-pointer items-center justify-center whitespace-nowrap border-t border-[#CCCCCC] p-1 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-black transition-colors hover:bg-[#E5E5E5]"
                >
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
                  className="flex h-10 w-full cursor-pointer items-center justify-center whitespace-nowrap border-t border-[#CCCCCC] p-1 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-[#DC2626] transition-colors hover:bg-[#E5E5E5] disabled:cursor-not-allowed disabled:opacity-60"
                >
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
            className="flex h-10 w-36 cursor-pointer items-center justify-center gap-4 rounded-xl border border-[#CCCCCC] px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-[#0A0A0A] transition-colors hover:border-[#CCCCCC] hover:bg-[#E5E5E5] hover:text-(--color-grays-900)"
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
