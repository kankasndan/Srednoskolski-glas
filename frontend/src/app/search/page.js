import { Suspense } from "react";
import AppShell from "@/components/shell/AppShell";
import SearchResults from "@/components/search/SearchResults";

function SearchHeadingFallback() {
  return (
    <div className="flex w-[990px] max-w-full flex-col gap-8">
      <h1 className="font-[family-name:var(--font-manrope)] text-[28px] font-bold leading-none text-black">
        Истражи
      </h1>
    </div>
  );
}

export default function SearchPage() {
  return (
    <AppShell>
      <Suspense fallback={<SearchHeadingFallback />}>
        <SearchResults />
      </Suspense>
    </AppShell>
  );
}
