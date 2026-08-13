"use client";

import Link from "next/link";
import Image from "next/image";
import { useCallback, useEffect, useRef, useState } from "react";
import Avatar from "@/components/ui/Avatar";
import LogoutDialogs from "@/components/shell/LogoutDialogs";
import { useClickOutside } from "@/hooks/useClickOutside";
import { useLogout } from "@/hooks/useLogout";
import { apiFetch } from "@/lib/api";

export default function AuthButtons() {
  const [user, setUser] = useState(null);
  const [menuOpen, setMenuOpen] = useState(false);
  const menuRef = useRef(null);
  const logout = useLogout({
    onLoggedOut: () => {
      setUser(null);
      setMenuOpen(false);
    },
  });

  useEffect(() => {
    let isMounted = true;

    // The session cookie is httpOnly, so we can't inspect it directly; we ask
    // the backend who we are. A 401 simply means "signed out".
    async function loadUser() {
      try {
        const response = await apiFetch("/api/me");

        if (!response.ok) {
          throw new Error("Unable to load user");
        }

        const data = await response.json();

        if (isMounted) {
          setUser(data.user);
        }
      } catch {
        if (isMounted) {
          setUser(null);
        }
      }
    }

    loadUser();

    return () => {
      isMounted = false;
    };
  }, []);

  useClickOutside(menuRef, useCallback(() => setMenuOpen(false), []), menuOpen);

  // Dijalozite se nadvor od granata za najaven korisnik, za da ostanat i otkako
  // `user` kje stane null po odjavata.
  if (user) {
    const displayName = user.username || "Профил";
    const avatarUrl = user.imageUrl;

    return (
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
              onClick={logout.ask}
              disabled={logout.loggingOut}
              className="flex w-full cursor-pointer items-center gap-3 px-5 py-3 text-left font-(family-name:--font-manrope) text-[15px] font-medium leading-none text-[#DC2626] transition-colors hover:bg-[#FEF2F2] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {logout.loggingOut ? "Се одјавува…" : "Одјави се"}
            </button>
          </div>
        )}

        <LogoutDialogs logout={logout} />
      </div>
    );
  }

  return (
    <div className="ml-auto flex h-10 w-75 shrink-0 items-center gap-3">
      <Link
        href="/login"
        className="flex h-10 w-36 cursor-pointer items-center justify-center gap-4 rounded-xl border border-[#CCCCCC] px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-[#0A0A0A] transition-colors hover:border-[#CCCCCC] hover:bg-[#E5E5E5] hover:text-(--color-grays-900)"
      >
        Најави се
      </Link>
      <Link
        href="/register"
        className="flex h-10 w-36 cursor-pointer items-center justify-center gap-4 rounded-xl border border-[#582FF5] bg-[#582FF5] px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-white transition-colors hover:border-[#CCCCCC] hover:bg-[#E5E5E5] hover:text-(--color-grays-900)"
      >
        Регистрација
      </Link>

      <LogoutDialogs logout={logout} />
    </div>
  );
}
