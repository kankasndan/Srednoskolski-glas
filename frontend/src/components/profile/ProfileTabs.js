"use client";

import Image from "next/image";
import Link from "next/link";
import { useRef, useState } from "react";
import { usePathname } from "next/navigation";

function buildTabs(basePath, isOwnProfile) {
  const tabs = [
    {
      href: basePath,
      label: isOwnProfile ? "Твои дискусии" : "Дискусии",
      shortLabel: "Дискусии",
      countKey: "threads",
    },
    {
      href: `${basePath}/comments`,
      label: "Коментари",
      countKey: "comments",
    },
    {
      href: `${basePath}/following`,
      label: isOwnProfile ? "Следиш" : "Следи",
      countKey: "follows",
    },
  ];

  if (isOwnProfile) {
    tabs.push({
      href: `${basePath}/people`,
      label: "Корисници",
      countKey: "followingUsers",
    });
  }

  return tabs;
}

// Na telefon tabovite se lizgaat vo pole so ista ramka kako prebaruvanjeto,
// a od md natamu se obichni podvlecheni tabovi.
function TabLink({ href, label, shortLabel, count, active }) {
  return (
    <Link
      href={href}
      className={`relative flex h-10 shrink-0 basis-1/3 cursor-pointer snap-start items-center justify-center gap-2 rounded-lg px-2 font-(family-name:--font-manrope) text-[14px] leading-none transition-colors md:h-auto md:basis-auto md:justify-start md:rounded-none md:px-0 md:pb-3 md:text-[16px] md:font-bold ${
        active
          ? "bg-(--color-grays-200) font-bold text-(--color-primary-200) md:bg-transparent"
          : "text-black active:bg-(--color-grays-200) md:text-(--color-grays-800) md:hover:text-black md:active:bg-transparent md:active:text-black"
      }`}
    >
      <span className="truncate md:hidden">{shortLabel ?? label}</span>
      <span className="hidden md:inline">{label}</span>

      {count != null ? (
        <span
          className={`hidden items-center justify-center rounded-full px-1.5 py-0.5 font-(family-name:--font-manrope) text-[11px] font-bold leading-4.125 md:flex ${
            active
              ? "bg-(--color-secondary-100) text-(--color-primary-200)"
              : "bg-(--color-grays-300) text-(--color-grays-700)"
          }`}
        >
          {count}
        </span>
      ) : null}

      {active ? (
        <span className="absolute inset-x-0 bottom-0 hidden h-px bg-(--color-primary-200) md:block" />
      ) : null}
    </Link>
  );
}

function ScrollArrow({ direction, disabled, onClick }) {
  return (
    <button
      type="button"
      onClick={onClick}
      disabled={disabled}
      aria-label={direction === "left" ? "Претходни табови" : "Следни табови"}
      className="flex size-6 shrink-0 cursor-pointer items-center justify-center rounded-lg transition-opacity hover:bg-(--color-grays-200) active:bg-(--color-grays-300) disabled:cursor-default disabled:opacity-25 md:hidden"
    >
      <Image
        src="/chevron-down.svg"
        alt=""
        width={16}
        height={16}
        className={`size-4 ${direction === "left" ? "rotate-90" : "-rotate-90"}`}
      />
    </button>
  );
}

export default function ProfileTabs({
  counts,
  basePath = "/profile",
  isOwnProfile = true,
}) {
  const pathname = usePathname();
  const tabs = buildTabs(basePath, isOwnProfile);
  const rowRef = useRef(null);
  const [edges, setEdges] = useState({ start: true, end: true });

  function readEdges(row) {
    if (!row) return;

    setEdges({
      start: row.scrollLeft <= 0,
      end: row.scrollLeft + row.clientWidth >= row.scrollWidth - 1,
    });
  }

  // Edno lizganje pokazuva sledniot komplet od tri taba.
  function scroll(direction) {
    const row = rowRef.current;
    row?.scrollBy({ left: direction * row.clientWidth, behavior: "smooth" });
  }

  return (
    <nav className="flex h-12 items-center gap-1 rounded-xl border border-[#CCCCCC] px-1.5 md:h-auto md:gap-8 md:rounded-none md:border-0 md:border-b md:border-(--color-grays-300) md:px-0">
      <ScrollArrow direction="left" disabled={edges.start} onClick={() => scroll(-1)} />

      <div
        ref={(node) => {
          rowRef.current = node;
          readEdges(node);
        }}
        onScroll={(event) => readEdges(event.currentTarget)}
        className="flex min-w-0 flex-1 snap-x snap-mandatory items-center overflow-x-auto [scrollbar-width:none] md:contents [&::-webkit-scrollbar]:hidden"
      >
        {tabs.map((tab) => (
          <TabLink
            key={tab.href}
            href={tab.href}
            label={tab.label}
            shortLabel={tab.shortLabel}
            count={counts?.[tab.countKey]}
            active={pathname === tab.href}
          />
        ))}
      </div>

      <ScrollArrow direction="right" disabled={edges.end} onClick={() => scroll(1)} />
    </nav>
  );
}
