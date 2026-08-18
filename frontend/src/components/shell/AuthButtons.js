"use client";

import Link from "next/link";
import Image from "next/image";
import { useCallback, useRef, useState } from "react";
import Avatar from "@/components/ui/Avatar";
import LogoutDialogs from "@/components/shell/LogoutDialogs";
import { useClickOutside } from "@/hooks/useClickOutside";
import { useLogout } from "@/hooks/useLogout";
import { useProfile } from "@/hooks/useProfile";

export default function AuthButtons() {
  // Zaednichka sesija, za da reagira i na odjava od druga strana.
  const { user } = useProfile();
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef(null);
  const logout = useLogout({ onLoggedOut: () => setMenuOpen(false) });

  useClickOutside(menuRef, useCallback(() => setMenuOpen(false), []), menuOpen);

  const displayName = user?.username || "Профил";
  const avatarUrl = user?.imageUrl;

  // Dijalozite se nadvor od granite: po odjava `user` e null i bi ischeznale.
  return (
    <>
      {user ? (
        <div ref={menuRef} className="relative flex shrink-0 items-center gap-3">
          <button
            type="button"
            onClick={() => setMenuOpen((open) => !open)}
            aria-haspopup="menu"
            aria-expanded={menuOpen}
            className="flex items-center gap-1 rounded-full transition-opacity hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5] focus-visible:ring-offset-2 cursor-pointer group"
          >
            <Avatar src={avatarUrl} size="xl" alt={displayName} />

            <span className="font-(family-name:--font-manrope) text-[18px] font-medium leading-none text-[#0A0A0A] group-hover:text-[#582FF5] transition">
              {displayName}
            </span>

            <Image
              src="/chevron-down.svg"
              alt=""
              width={16}
              height={16}
              className={`size-4`}
            />
          </button>

          {menuOpen && (
            <div
              role="menu"
              className="absolute top-full right-0 z-50 mt-3 w-56 overflow-hidden rounded-2xl border border-[#E5E5E5] bg-white py-2 shadow-[0_8px_24px_rgba(0,0,0,0.12)]"
            >
              <Link
                href="/profile"
                role="menuitem"
                onClick={() => setMenuOpen(false)}
                className="flex w-full cursor-pointer items-center gap-3 px-5 py-3 text-left font-(family-name:--font-manrope) text-[15px] font-medium leading-none text-[#0A0A0A] transition-colors hover:bg-[#F5F5F5]"
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
                className="flex w-full cursor-pointer items-center gap-3 px-5 py-3 text-left font-(family-name:--font-manrope) text-[15px] font-medium leading-none text-[#DC2626] transition-colors hover:bg-[#FEF2F2] disabled:cursor-not-allowed disabled:opacity-60"
              >
                {logout.loggingOut ? "Се одјавува…" : "Одјави се"}
              </button>
            </div>
          )}
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
