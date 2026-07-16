"use client";

import Link from "next/link";

const ROW =
  "flex h-10 w-[268px] cursor-pointer items-center gap-3 rounded-[10px] border border-[#CCCCCC] px-4 py-2 text-left font-[family-name:var(--font-manrope)] text-[14px] font-medium leading-none text-[#595959] transition-colors hover:bg-[#CFE9ED]";

export default function NavItem({ label, href, active = false, onSelect }) {
  const className = `${ROW} ${
    active ? "bg-[#CFE9ED] font-bold text-black" : ""
  }`;
  const checkClassName = `flex size-4 shrink-0 items-center justify-center rounded-[3px] border ${
    active ? "border-[#582FF5] bg-[#582FF5]" : "border-[#000000]"
  }`;
  const content = (
    <>
      <span className={checkClassName}>
        <svg
          className={active ? "block" : "hidden"}
          width="12"
          height="12"
          viewBox="0 0 24 24"
          fill="none"
        >
          <path
            d="M20 6 9 17l-5-5"
            stroke="white"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      </span>
      <span>{label}</span>
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
