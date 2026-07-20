"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import ForumIcon from "@/components/ForumIcon";

const BASE_CLASS =
  "group flex h-10 items-center overflow-hidden whitespace-nowrap rounded-xl border border-[var(--color-grays-300)] bg-white font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none text-[var(--color-grays-900)] transition-all duration-300 ease-in-out hover:bg-[var(--color-grays-300)]";

export default function ForumItem({ forum, active = false, onSelect, collapsed }) {
  const pathname = usePathname();
  const key = `forum:${forum.slug}`;
  const isCurrentPage = pathname === `/p/${forum.slug}`;
  const layout = collapsed ? "w-10 justify-center" : "w-[268px] gap-3 px-4 py-2";
  const className = `${BASE_CLASS} ${layout} ${
    active ? "!bg-[var(--color-primary-200)] !text-white" : ""
  }`;

  return (
    <Link
      href={`/p/${forum.slug}`}
      scroll={false}
      aria-current={isCurrentPage ? "page" : undefined}
      onClick={() => onSelect(key)}
      className={className}
    >
      <ForumIcon src={forum.imageUrl} active={active} />
      {!collapsed && <span>{forum.name}</span>}
    </Link>
  );
}
