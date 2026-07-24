"use client";

import Link from "next/link";
import Image from "next/image";
import { usePathname } from "next/navigation";

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
        <span className="flex h-[19px] min-w-0 flex-1 items-center truncate">
          {activeSchool ? activeSchool.name : city.name}
        </span>
        <Image
          src="/chevron-down.svg"
          alt=""
          width={16}
          height={16}
          className="size-4 shrink-0 transition-transform duration-300 ease-out group-open:rotate-180"
        />
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
