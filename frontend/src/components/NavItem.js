"use client";

import Link from "next/link";
import Image from "next/image";

const ROW =
  "flex h-10 cursor-pointer items-center overflow-hidden whitespace-nowrap rounded-[12px] border border-[#CCCCCC] text-left font-[family-name:var(--font-manrope)] text-[14px] font-medium leading-none text-[#595959] transition-all duration-300 ease-in-out hover:bg-[var(--color-grays-300)]";

export default function NavItem({ label, href, icon, active = false, onSelect, collapsed }) {
  const layout = collapsed
    ? "w-10 justify-center"
    : "w-[268px] gap-3 px-4 py-2";
  const className = `${ROW} ${layout} ${
    active ? "!bg-[var(--color-primary-200)] font-bold text-white" : ""
  }`;
  const checkClassName = `flex size-4 shrink-0 items-center justify-center rounded-[3px] border ${
    active ? "border-white bg-white" : "border-[#000000]"
  }`;
  const content = collapsed ? (
    <Image
      src={icon}
      alt=""
      width={24}
      height={24}
      className={`max-h-6 max-w-8 object-contain ${active ? "brightness-0 invert" : ""}`}
    />
  ) : (
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
            stroke="var(--color-primary-200)"
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
