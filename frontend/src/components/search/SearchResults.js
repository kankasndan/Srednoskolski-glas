"use client";

import { useSearchParams } from "next/navigation";
import Threads from "@/components/thread/Threads";

export default function SearchResults() {
  const searchParams = useSearchParams();
  const q = searchParams.get("q") ?? "";
  const forum = searchParams.get("forum") || null;
  const trimmed = q.trim();

  return (
    <div className="flex w-[990px] max-w-full flex-col gap-8">
      <header className="flex w-full flex-col gap-2">
        <h1 className="font-[family-name:var(--font-manrope)] text-[28px] font-bold leading-none text-black">
          Истражи
        </h1>
        <p className="font-[family-name:var(--font-manrope)] text-[16px] text-[#595959]">
          {trimmed
            ? `Резултати за „${trimmed}”`
            : "Откриј дискусии низ заедницата."}
        </p>
      </header>
      <Threads key={`${trimmed}|${forum ?? ""}`} searchQuery={q} forum={forum} />
    </div>
  );
}
