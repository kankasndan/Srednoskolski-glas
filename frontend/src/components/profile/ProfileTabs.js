"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

const TABS = [
  { href: "/profile", label: "Твои дискусии", countKey: "threads" },
  { href: "/profile/comments", label: "Коментари", countKey: "comments" },
  { href: "/profile/following", label: "Следиш", countKey: "follows" },
];

function TabLink({ href, label, count, active }) {
  return (
    <Link
      href={href}
      className={`relative flex items-center gap-2 pb-3 font-(family-name:--font-manrope) text-[16px] font-bold leading-none transition-colors ${
        active ? "text-(--color-primary-200)" : "text-(--color-grays-800) hover:text-black"
      }`}
    >
      {label}

      {count != null ? (
        <span
          className={`flex items-center justify-center rounded-full px-1.5 py-0.5 font-(family-name:--font-manrope) text-[11px] font-bold leading-4.125 ${
            active
              ? "bg-(--color-secondary-100) text-(--color-primary-200)"
              : "bg-(--color-grays-300) text-(--color-grays-700)"
          }`}
        >
          {count}
        </span>
      ) : null}

      {active ? (
        <span className="absolute inset-x-0 bottom-0 h-px bg-(--color-primary-200)" />
      ) : null}
    </Link>
  );
}

export default function ProfileTabs({ counts }) {
  const pathname = usePathname();

  return (
    <nav className="flex items-center gap-8 border-b border-(--color-grays-300)">
      {TABS.map((tab) => (
        <TabLink
          key={tab.href}
          href={tab.href}
          label={tab.label}
          count={counts?.[tab.countKey]}
          active={pathname === tab.href}
        />
      ))}
    </nav>
  );
}
