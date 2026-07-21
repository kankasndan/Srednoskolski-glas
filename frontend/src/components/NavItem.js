"use client";

import Link from "next/link";
import ForumIcon from "@/components/ForumIcon";

const ROW =
  "flex h-10 cursor-pointer items-center overflow-hidden whitespace-nowrap rounded-[12px] border border-[#CCCCCC] text-left font-[family-name:var(--font-manrope)] text-[14px] font-medium leading-none text-[#595959] transition-all duration-300 ease-in-out hover:bg-[var(--color-grays-300)]";

export default function NavItem({ label, href, icon, active = false, onSelect, collapsed }) {
  const layout = collapsed
    ? "w-10 justify-center"
    : "w-[268px] gap-3 px-4 py-2";
  const className = `${ROW} ${layout} ${
    active ? "!bg-[var(--color-primary-200)] font-bold text-white" : ""
  }`;
  const content = (
    <>
      <ForumIcon
        src={icon}
        active={active}
        imageClassName="max-h-5 max-w-5 object-contain"
      />
      {!collapsed && <span>{label}</span>}
    </>
  );

  if (href) {
    return (
      <Link href={href} aria-current={active ? "page" : undefined} onClick={onSelect} className={className}>
        {content}
      </Link>
    );
  }

  return (
    <button type="button" onClick={onSelect} className={className}>
      {content}
    </button>
  );
}
