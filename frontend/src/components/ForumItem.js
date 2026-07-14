"use client";

import Image from "next/image";
import Link from "next/link";
import { usePathname } from "next/navigation";

const BASE_CLASS =
  "group flex h-12 w-[268px] items-center gap-3 rounded-2xl border border-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-medium transition-colors";

export default function ForumItem({ forum, selectedKey, onSelect }) {
  const pathname = usePathname();
  const key = `forum:${forum.slug}`;
  const isCurrentPage = pathname === `/p/${forum.slug}`;
  const isActive = selectedKey === key;

  return (
    <Link
      href={`/p/${forum.slug}`}
      aria-current={isCurrentPage ? "page" : undefined}
      onClick={() => onSelect(key)}
      className={`${BASE_CLASS} ${
        isActive
          ? "bg-[#582FF5] font-bold text-white"
          : "text-[#595959] hover:bg-[#582FF5] hover:text-white"
      }`}
    >
      <Image
        src={forum.icon}
        alt=""
        width={55}
        height={55}
        className={`h-[55px] w-[55px] shrink-0 transition group-hover:brightness-0 group-hover:invert ${
          isActive ? "brightness-0 invert" : ""
        }`}
      />
      <span>{forum.name}</span>
    </Link>
  );
}
