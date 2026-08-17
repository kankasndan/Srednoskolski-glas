"use client";

import { useSearchParams } from "next/navigation";
import Threads from "@/components/thread/Threads";

export default function SearchResults() {
  const searchParams = useSearchParams();
  const q = searchParams.get("q") ?? "";
  const forum = searchParams.get("forum") || null;
  const trimmed = q.trim();

  return (
    <div className="flex w-[990px] max-w-full flex-col gap-3 lg:gap-8">
      {trimmed ? (
        <h1 className="font-[family-name:var(--font-manrope)] text-[24px] font-bold tracking-normal text-black">
          Пребарување за „
          <span className="text-[var(--color-primary-200)]">{trimmed}</span>“
        </h1>
      ) : null}
      <Threads key={`${trimmed}|${forum ?? ""}`} searchQuery={q} forum={forum} />
    </div>
  );
}
