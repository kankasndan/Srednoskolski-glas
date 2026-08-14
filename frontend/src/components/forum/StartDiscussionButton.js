"use client";

import Image from "next/image";
import Link from "next/link";
import { useProfile } from "@/hooks/useProfile";
import { canCreateThreads } from "@/lib/capabilities";

export default function StartDiscussionButton({ className = "", full = false }) {
  const { user, loading } = useProfile();

  if (loading || !canCreateThreads(user)) {
    return null;
  }

  if (full) {
    return (
      <Link
        href="/new"
        className={`flex h-10 w-full max-w-[268px] shrink-0 cursor-pointer items-center justify-center gap-3 rounded-xl bg-[#582FF5] px-4 py-2 text-white transition-colors hover:bg-[#4B25E0] ${className}`}
      >
        <Image src="/plus.svg" alt="" width={24} height={24} className="size-6" />
        <span className="flex h-[19px] items-center whitespace-nowrap leading-none">
          Започни нова дискусија
        </span>
      </Link>
    );
  }

  return (
    <Link
      href="/new"
      className={`flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-(--color-primary-200) bg-white transition-colors hover:bg-[#F5F5F5] lg:h-10 lg:w-[268px] lg:justify-center lg:gap-3 lg:border-0 lg:bg-[#582FF5] lg:px-4 lg:py-2 lg:text-white lg:hover:bg-[#4B25E0] ${className}`}
    >
      <Image src="/plus-black.svg" alt="" width={24} height={24} className="size-6 lg:hidden" />
      <Image src="/plus.svg" alt="" width={24} height={24} className="hidden size-6 lg:block" />
      <span className="sr-only flex h-[19px] items-center leading-none lg:not-sr-only lg:w-[168px] lg:whitespace-nowrap!">
        Започни нова дискусија
      </span>
    </Link>
  );
}
