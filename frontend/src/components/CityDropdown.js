"use client";

import Link from "next/link";
import Image from "next/image";
import { usePathname } from "next/navigation";
import { createSchoolForum } from "@/lib/forums";

export default function CityDropdown({ city, schools, selectedKey, onSelect }) {
  const pathname = usePathname();
  const schoolForums = schools.map(createSchoolForum);
  const hasActiveSchool = schoolForums.some(
    (school) =>
      pathname === `/p/${school.slug}` || selectedKey === `forum:${school.slug}`,
  );

  return (
    <details name="school-city" className="group w-[268px]" open={hasActiveSchool || undefined}>
      <summary
        className="flex h-12 w-[268px] cursor-pointer list-none items-center justify-between gap-4 rounded-2xl border border-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-medium text-[#595959] transition-colors hover:bg-[#F3F0FF] [&::-webkit-details-marker]:hidden"
      >
        <span>{city}</span>
        <Image
          src="/arrow.svg"
          alt=""
          width={20}
          height={20}
          className="h-5 w-5 shrink-0 transition-transform group-[[open]]:rotate-180"
        />
      </summary>
      <ul className="flex flex-col gap-1 py-2 pl-4">
        {schoolForums.map((school) => {
          const key = `forum:${school.slug}`;
          const isCurrentPage = pathname === `/p/${school.slug}`;
          const isActive = selectedKey === key;

          return (
            <li key={school.slug}>
              <Link
                href={`/p/${school.slug}`}
                aria-current={isCurrentPage ? "page" : undefined}
                onClick={() => onSelect(key)}
                className={`block rounded-md px-2 py-1 font-[family-name:var(--font-manrope)] text-[13px] transition-all hover:font-bold ${
                  isActive ? "bg-[#582FF5] font-bold text-white" : ""
                }`}
              >
                {school.name}
              </Link>
            </li>
          );
        })}
      </ul>
    </details>
  );
}
