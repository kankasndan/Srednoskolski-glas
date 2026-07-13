"use client";

import Image from "next/image";

const ROW =
  "flex h-12 w-[268px] cursor-pointer items-center gap-3 rounded-2xl border border-[#582FF5]/30 px-4 py-2 text-left font-[family-name:var(--font-manrope)] text-[14px] font-medium text-[#595959] transition-colors";

export default function NavItem({ label, defaultChecked = false, variant = "check" }) {
  if (variant === "plus") {
    return (
      <a href="#" onClick={(e) => e.preventDefault()} className={ROW}>
        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#582FF5]">
          <Image src="/plus.svg" alt="" width={16} height={16} />
        </span>
        <span>{label}</span>
      </a>
    );
  }

  return (
    <label
      className={`${ROW} group has-[:checked]:border-transparent has-[:checked]:bg-[#582FF5] has-[:checked]:text-white`}
    >
      <input
        type="radio"
        name="sidebar-nav"
        defaultChecked={defaultChecked}
        className="peer sr-only"
      />
      <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-[#582FF5]/40 group-has-[:checked]:border-white">
        <svg
          className="hidden group-has-[:checked]:block"
          width="14"
          height="14"
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
    </label>
  );
}
