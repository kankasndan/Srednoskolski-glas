"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import ForumIcon from "@/components/ForumIcon";

const BASE_CLASS =
  "group flex h-10 w-[268px] items-center gap-3 rounded-xl border border-[var(--color-grays-300)] bg-white px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-[var(--color-grays-900)] transition-colors hover:bg-[var(--color-secondary-200)]";

export default function ForumItem({ forum, active = false, onSelect }) {
  const pathname = usePathname();
  const key = `forum:${forum.slug}`;
  const isCurrentPage = pathname === `/p/${forum.slug}`;
  const className = `${BASE_CLASS} ${active ? "!bg-[var(--color-secondary-200)]" : ""}`;

  return (
    <Link
      href={`/p/${forum.slug}`}
      scroll={false}
      aria-current={isCurrentPage ? "page" : undefined}
      onClick={() => onSelect(key)}
      className={className}
    >
      <ForumIcon src={forum.imageUrl} />
      <span>{forum.name}</span>
    </Link>
  );
}
