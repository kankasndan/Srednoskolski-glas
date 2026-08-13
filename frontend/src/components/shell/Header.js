import { Suspense } from "react";
import Image from "next/image";
import Link from "next/link";
import SearchBar, { SearchBarFallback } from "@/components/shell/SearchBar";
import AuthButtons from "@/components/shell/AuthButtons";

export default function Header({ onMenuToggle, menuOpen = false }) {
  return (
    <header className="sticky top-0 z-50 w-full bg-white lg:shadow-sm">
      <div className="flex flex-wrap items-center gap-x-4 gap-y-10 px-6 py-3 lg:flex-nowrap lg:gap-x-6 lg:px-14 lg:py-4">
        <button
          type="button"
          onClick={onMenuToggle}
          aria-label={menuOpen ? "Затвори мени" : "Отвори мени"}
          aria-expanded={menuOpen}
          className="order-1 flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg transition-colors hover:bg-gray-100 lg:hidden"
        >
          <Image src="/menu-2-line.svg" alt="" width={24} height={24} className="size-6" />
        </button>

        <Link
          href="/feed"
          className="order-2 flex flex-1 cursor-pointer items-center justify-center gap-3 overflow-hidden lg:h-14 lg:w-60 lg:flex-none lg:justify-start"
        >
          <Image
            src="/logo.svg"
            alt="Средношколски глас"
            width={44}
            height={30}
            priority
            className="block h-8 w-auto sm:hidden"
          />
          <Image
            src="/logo-with-text.svg?v=large-header"
            alt="Средношколски глас"
            width={240}
            height={56}
            priority
            className="hidden h-10 w-auto sm:block lg:h-14 lg:w-60"
          />
        </Link>

        <div className="order-3 shrink-0 lg:order-4">
          <AuthButtons />
        </div>

        <div className="order-4 w-full lg:order-3 lg:w-[632px] lg:max-w-full lg:flex-1">
          <Suspense fallback={<SearchBarFallback />}>
            <SearchBar />
          </Suspense>
        </div>
      </div>
    </header>
  );
}
