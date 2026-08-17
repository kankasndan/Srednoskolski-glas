import { Suspense } from "react";
import AppShell from "@/components/shell/AppShell";
import SearchResults from "@/components/search/SearchResults";

export default function SearchPage() {
  return (
    <AppShell>
      <Suspense fallback={null}>
        <SearchResults />
      </Suspense>
    </AppShell>
  );
}
