"use client";

import Link from "next/link";
import { useEffect, useRef, useState } from "react";
import { useRouter } from "next/navigation";
import { apiFetch } from "@/lib/api";

export default function AuthButtons() {
  const router = useRouter();
  const [user, setUser] = useState(null);
  const [menuOpen, setMenuOpen] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);
  const menuRef = useRef(null);

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
      setUser(null);
      setMenuOpen(false);
      setLoggingOut(false);
      router.replace("/feed");
    }
  }

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
          className="flex items-center gap-1 rounded-full transition-opacity hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#582FF5] focus-visible:ring-offset-2 cursor-pointer"
        >
          <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full">
            <img
              src={avatarUrl}
              alt={displayName}
              className="h-full w-full object-cover"
            />
          </div>

          <span className="font-[family-name:var(--font-manrope)] text-[18px] font-medium leading-none text-[#0A0A0A]">
            {displayName}
          </span>
        </button>

        {menuOpen && (
          <div
            role="menu"
            className="absolute top-full right-0 z-50 mt-3 w-56 overflow-hidden rounded-2xl border border-[#E5E5E5] bg-white py-2 shadow-[0_8px_24px_rgba(0,0,0,0.12)]"
          >
            <button
              type="button"
              role="menuitem"
              onClick={handleLogout}
              disabled={loggingOut}
              className="flex w-full items-center gap-3 px-5 py-3 text-left font-[family-name:var(--font-manrope)] text-[15px] font-medium leading-none text-[#DC2626] transition-colors hover:bg-[#FEF2F2] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {loggingOut ? "Се одјавува…" : "Одјави се"}
            </button>
          </div>
        )}
      </div>
    );
  }

  return (
    <div className="ml-auto flex h-10 w-[300px] shrink-0 items-center gap-3">
      <Link
        href="/login"
        className="flex h-10 w-36 items-center justify-center gap-4 rounded-xl border border-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-[#0A0A0A] transition-colors hover:border-[var(--color-secondary-200)] hover:bg-[var(--color-secondary-200)] hover:text-[var(--color-grays-900)]"
      >
        Најави се
      </Link>
      <Link
        href="/register"
        className="flex h-10 w-36 items-center justify-center gap-4 rounded-xl border border-[#582FF5] bg-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold leading-none text-white transition-colors hover:border-[var(--color-secondary-200)] hover:bg-[var(--color-secondary-200)] hover:text-[var(--color-grays-900)]"
      >
        Регистрација
      </Link>
    </div>
  );
}
