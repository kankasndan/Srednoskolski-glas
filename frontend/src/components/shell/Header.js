"use client";

import { Suspense, useState } from "react";
import Image from "next/image";
import Link from "next/link";
import { useRouter } from "next/navigation";
import SearchBar, { SearchBarFallback } from "@/components/shell/SearchBar";
import AuthButtons from "@/components/shell/AuthButtons";

export default function Header({ onMenuOpen }) {
  const router = useRouter();
  const [leavingToLogin, setLeavingToLogin] = useState(false);

  // Kratka animacija pred da se otvori najavata.
  function goToLogin(event) {
    event.preventDefault();
    if (leavingToLogin) return;
    setLeavingToLogin(true);
    setTimeout(() => router.push("/login"), 200);
  }

  return (
    <header className="sticky top-0 z-50 flex w-full flex-col bg-white shadow-sm">
      {/* Mobilna lenta: menu, logo i najava. */}
      <div className="flex items-center justify-between px-6 py-2 lg:hidden">
        <button
          type="button"
          onClick={onMenuOpen}
          aria-label="Отвори мени"
          className="flex size-8 shrink-0 cursor-pointer items-center justify-center"
        >
          <Image src="/menu-2-line.svg" alt="" width={32} height={32} className="size-8" />
        </button>

        <Link href="/feed" className="flex h-12 w-[71px] shrink-0 items-center justify-center">
          <Image
            src="/logo.svg"
            alt="Средношколски глас"
            width={71}
            height={48}
            priority
            className="h-12 w-[71px] object-contain"
          />
        </Link>

        <Link
          href="/login"
          onClick={goToLogin}
          aria-label="Најави се"
          className={`flex size-8 shrink-0 cursor-pointer items-center justify-center transition-all duration-200 ease-out ${
            leavingToLogin ? "scale-90 opacity-40" : "active:scale-90"
          }`}
        >
          <Image src="/login-rounded.svg" alt="" width={32} height={32} className="size-8" />
        </Link>
      </div>

      <div className="flex w-full items-center justify-between gap-6 px-6 pb-4 pt-8 lg:px-14 lg:py-4">
        <Link
          href="/feed"
          className="hidden h-14 w-60 shrink-0 cursor-pointer items-center justify-center gap-3 overflow-hidden lg:flex"
        >
          <Image
            src="/logo-with-text.svg?v=large-header"
            alt="Средношколски глас"
            width={240}
            height={56}
            priority
            className="block h-14 w-60"
          />
        </Link>
        <Suspense fallback={<SearchBarFallback />}>
          <SearchBar />
        </Suspense>
        <div className="hidden lg:block">
          <AuthButtons />
        </div>
      </div>
    </header>
  );
}
