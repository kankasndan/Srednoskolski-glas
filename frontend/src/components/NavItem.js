"use client";

const ROW =
  "flex h-10 w-[268px] cursor-pointer items-center gap-3 rounded-[10px] border border-[#CCCCCC] px-4 py-2 text-left font-[family-name:var(--font-manrope)] text-[14px] font-medium leading-none text-[#595959] transition-colors";

export default function NavItem({ label, defaultChecked = false }) {
  return (
    <label
      className={`${ROW} group has-[:checked]:border-transparent has-[:checked]:bg-[#582FF5] has-[:checked]:font-bold has-[:checked]:text-white`}
    >
      <input
        type="radio"
        name="sidebar-nav"
        defaultChecked={defaultChecked}
        className="peer sr-only"
      />
      <span className="flex size-4 shrink-0 items-center justify-center rounded-[3px] border border-[#000000] group-has-[:checked]:border-white">
        <svg
          className="hidden group-has-[:checked]:block"
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
    </label>
  );
}
