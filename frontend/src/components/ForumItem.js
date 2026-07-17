"use client";

import Image from "next/image";
import Link from "next/link";
import { usePathname } from "next/navigation";

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
      <span className="relative size-4 shrink-0">
        <Image
          src={forum.imageUrl}
          alt=""
          width={36}
          height={36}
          className="absolute left-1/2 top-1/2 size-9 max-w-none -translate-x-1/2 -translate-y-1/2"
        />
      </span>
      <span>{forum.name}</span>
    </Link>
  );
}
