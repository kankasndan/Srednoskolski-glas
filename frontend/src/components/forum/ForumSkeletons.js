/**
 * Pulsing grey placeholders that mimic sidebar forum rows / city dropdowns.
 */
export function ForumItemSkeleton({ collapsed = false }) {
  if (collapsed) {
    return (
      <div
        className="flex h-10 w-10 items-center justify-center rounded-xl border border-[#E5E5E5] bg-[#F5F5F5]"
        aria-hidden
      >
        <div className="size-4 animate-pulse rounded-full bg-[#D9D9D9]" />
      </div>
    );
  }

  return (
    <div
      className="flex h-10 w-[268px] items-center gap-3 rounded-xl border border-[#E5E5E5] bg-[#F5F5F5] px-4 py-2"
      aria-hidden
    >
      <div className="size-4 shrink-0 animate-pulse rounded-full bg-[#D9D9D9]" />
      <div className="h-3.5 flex-1 animate-pulse rounded bg-[#D9D9D9]" />
    </div>
  );
}

export function CityDropdownSkeleton() {
  return (
    <div
      className="flex h-10 w-[268px] items-center justify-between gap-4 rounded-[12px] border border-[#E5E5E5] bg-[#F5F5F5] px-4 py-2"
      aria-hidden
    >
      <div className="h-3.5 w-2/3 animate-pulse rounded bg-[#D9D9D9]" />
      <div className="size-4 shrink-0 animate-pulse rounded bg-[#D9D9D9]" />
    </div>
  );
}

export function ThematicForumsSkeleton({ collapsed = false, count = 6 }) {
  return (
    <div className="flex flex-col gap-2" role="status" aria-label="Се вчитуваат форумите">
      {Array.from({ length: count }, (_, index) => (
        <ForumItemSkeleton key={index} collapsed={collapsed} />
      ))}
    </div>
  );
}

export function SchoolForumsSkeleton({ count = 4 }) {
  return (
    <div className="flex flex-col gap-2" role="status" aria-label="Се вчитуваат училишните форуми">
      {Array.from({ length: count }, (_, index) => (
        <CityDropdownSkeleton key={index} />
      ))}
    </div>
  );
}
