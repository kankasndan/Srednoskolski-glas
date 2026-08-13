"use client";

import Link from "next/link";
import Image from "next/image";
import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import Avatar from "@/components/ui/Avatar";
import { apiFetch } from "@/lib/api";
import { needsOnboarding } from "@/lib/capabilities";
import {
  clearSessionUser,
  getCachedSessionUser,
  loadSessionUser,
  subscribeSessionUser,
} from "@/lib/sessionUser";

function AuthButtonsSkeleton() {
  return (
    <div
      className="ml-auto flex h-10 w-75 shrink-0 items-center justify-end gap-3"
      aria-hidden="true"
    >
      <div className="h-10 w-36 animate-pulse rounded-xl bg-[#E5E5E5]" />
      <div className="h-10 w-36 animate-pulse rounded-xl bg-[#E5E5E5]" />
    </div>
  );
}

export default function AuthButtons() {
  const router = useRouter();
  const cached = getCachedSessionUser();
  const [user, setUser] = useState(() => (cached === undefined ? null : cached));
  const [resolved, setResolved] = useState(() => cached !== undefined);
  const [menuOpen, setMenuOpen] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);
  const menuRef = useRef(null);

  useEffect(() => {
    const unsubscribe = subscribeSessionUser((next) => {
      if (next === undefined) return;
      setUser(next);
      setResolved(true);
    });

    let isMounted = true;

    loadSessionUser()
      .then((nextUser) => {
        if (!isMounted) return;
        setUser(nextUser);
      })
      .finally(() => {
        if (isMounted) setResolved(true);
      });

    return () => {
      isMounted = false;
      unsubscribe();
    };
  }, []);

  useEffect(() => {
    if (!menuOpen) return;

    function handlePointerDown(event) {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setMenuOpen(false);
      }
    }

    function handleKeyDown(event) {
      if (event.key === "Escape") setMenuOpen(false);
    }

    document.addEventListener("mousedown", handlePointerDown);
    document.addEventListener("keydown", handleKeyDown);

    return () => {
      document.removeEventListener("mousedown", handlePointerDown);
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [menuOpen]);

  async function handleLogout() {
    setLoggingOut(true);

    try {
      await apiFetch("/api/logout", { method: "POST" });
    } catch {
      // Even if the request fails, clear local state so the UI reflects a
      // signed-out session; the cookie will expire regardless.
    } finally {
      localStorage.removeItem("onboarding_pending");
      clearSessionUser();
      setUser(null);
      setMenuOpen(false);
      setLoggingOut(false);
      router.replace("/feed");
    }
  }

  if (!resolved && !user) {
    return <AuthButtonsSkeleton />;
  }

  if (user) {
    if (needsOnboarding(user)) {
      return (
        <div className="ml-auto flex h-10 shrink-0 items-center gap-3">
          <Link
            href="/register/onboarding"
            className="flex h-10 w-auto min-w-36 cursor-pointer items-center justify-center gap-2 rounded-xl border border-[#582FF5] bg-[#582FF5] px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-white transition-colors hover:bg-[#4B25E0]"
          >
            Заврши регистрација
          </Link>
          <button
            type="button"
            onClick={handleLogout}
            disabled={loggingOut}
            className="flex h-10 cursor-pointer items-center justify-center rounded-xl border border-[#CCCCCC] px-4 py-2 font-(family-name:--font-manrope) text-[14px] font-bold leading-none text-[#0A0A0A] transition-colors hover:bg-[#E5E5E5] disabled:opacity-60"
          >
            {loggingOut ? "…" : "Одјави се"}
          </button>
        </div>
      );
    }

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
              onClick={handleLogout}
              disabled={loggingOut}
              className="flex w-full cursor-pointer items-center gap-3 px-5 py-3 text-left font-(family-name:--font-manrope) text-[15px] font-medium leading-none text-[#DC2626] transition-colors hover:bg-[#FEF2F2] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {loggingOut ? "Се одјавува…" : "Одјави се"}
            </button>
          </div>
        )}
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
    </div>
  );
}
