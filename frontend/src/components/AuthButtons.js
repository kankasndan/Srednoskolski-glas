"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { API_BASE_URL } from "@/lib/api";

export default function AuthButtons() {
  const [user, setUser] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem("auth_token");

    if (!token) {
      return;
    }

    let isMounted = true;

    async function loadUser() {
      try {
        const response = await fetch(`${API_BASE_URL}/api/me`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          throw new Error("Unable to load user");
        }

        const data = await response.json();

        if (isMounted) {
          setUser(data.user);
        }
      } catch {
        localStorage.removeItem("auth_token");
        if (isMounted) {
          setUser(null);
        }
      } finally {
        // No-op: the component can render the signed-out state until the user loads.
      }
    }

    loadUser();

    return () => {
      isMounted = false;
    };
  }, []);

  if (user) {
    const displayName = user.name || user.username || "Профил";
    const avatarUrl = user.imageUrl;
    const fallbackInitial = displayName.charAt(0).toUpperCase() || "U";

    return (
      <div className="ml-auto flex shrink-0 items-center gap-3">
        <div className="flex items-center gap-3">
          <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#5B35F5] text-white shadow-[0_0_0_5px_rgba(91,53,245,0.08)]">
            {avatarUrl ? (
              <img
                src={avatarUrl}
                alt={displayName}
                className="h-full w-full object-cover"
              />
            ) : (
              <span className="font-[family-name:var(--font-manrope)] text-[18px] font-bold leading-none">
                {fallbackInitial}
              </span>
            )}
          </div>

          <span className="font-[family-name:var(--font-manrope)] text-[18px] font-medium leading-none text-[#0A0A0A]">
            {displayName}
          </span>
        </div>
      </div>
    );
  }

  return (
    <div className="ml-auto flex shrink-0 items-center gap-4">
      <Link
        href="/login"
        className="flex h-12 w-36 items-center justify-center gap-4 rounded-2xl border border-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-[#0A0A0A] transition-colors hover:border-[#F88DD5] hover:bg-[#F88DD5] hover:text-white"
      >
        Најави се
      </Link>
      <Link
        href="/register"
        className="flex h-12 w-36 items-center justify-center gap-4 rounded-2xl border border-[#582FF5] bg-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-bold text-white transition-colors hover:border-[#F88DD5] hover:bg-[#F88DD5] hover:text-white"
      >
        Регистрација
      </Link>
    </div>
  );
}
