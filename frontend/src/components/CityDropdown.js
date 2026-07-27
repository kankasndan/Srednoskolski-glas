"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

// Inline so the stroke follows the summary's text colour: black normally, white
// once a school is selected and the row turns purple.
function ChevronDownIcon() {
  return (
    <svg
      width="16"
      height="16"
      viewBox="0 0 16 16"
      fill="none"
      aria-hidden="true"
      className="size-4 shrink-0 transition-transform duration-300 ease-out group-open:rotate-180"
    >
      <path
        d="M4 6L8 10L12 6"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
    </svg>
  );
}

export default function CityDropdown({ city, forums, selectedKey, onSelect }) {
  const pathname = usePathname();
  const activeSchool = forums.find(
    (school) =>
      pathname === `/p/${school.slug}` || selectedKey === `forum:${school.slug}`,
  );

  return (
    <details name="school-city" className="group w-[268px]">
      <summary
        className={`flex h-10 w-[268px] cursor-pointer list-none items-center justify-between gap-4 rounded-[12px] border border-[#CCCCCC] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none tracking-normal text-black transition-colors duration-300 ease-out hover:bg-[#CFE9ED] group-open:rounded-b-none group-open:bg-[#CFE9ED] [&::-webkit-details-marker]:hidden ${
          activeSchool ? "!bg-[var(--color-primary-200)] !text-white" : ""
        }`}
      >
        {/* Closed: the selected school (or the city when nothing is selected).
            Open: always the city, so the list below reads as its schools. */}
        <span className="flex h-[19px] min-w-0 flex-1 items-center truncate group-open:hidden">
          {activeSchool ? activeSchool.name : city.name}
        </span>
        <span className="hidden h-[19px] min-w-0 flex-1 items-center truncate group-open:flex">
          {city.name}
        </span>
        <ChevronDownIcon />
      </summary>
      <ul className="flex w-[268px] flex-col overflow-hidden rounded-b-[12px] border-x border-b border-[#CCCCCC] bg-white transition-all duration-300 ease-out">
        {forums.map((school, index) => {
          const key = `forum:${school.slug}`;
          const isCurrentPage = pathname === `/p/${school.slug}`;
          const isActive = selectedKey === key;
          const hasDivider = index < forums.length - 1;

          return (
            <li key={school.slug}>
              <Link
                href={`/p/${school.slug}`}
                scroll={false}
                aria-current={isCurrentPage ? "page" : undefined}
                onClick={() => onSelect(key)}
                className={`flex h-10 w-[268px] items-center gap-4 bg-white px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-normal leading-none tracking-normal text-black transition-colors duration-300 ease-out hover:bg-[#E5E5E5] ${
                  hasDivider ? "border-b border-[#CCCCCC]" : ""
                } ${
                  isActive ? "!bg-[var(--color-primary-200)] !text-white" : ""
                }`}
              >
                <span className="flex h-[19px] min-w-0 flex-1 items-center truncate">
                  {school.name}
                </span>
              </Link>
            </li>
          );
        })}
      </ul>
    </details>
  );
}
