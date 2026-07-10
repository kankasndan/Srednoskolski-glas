import { slugify } from "@/lib/forums";

export default function CityDropdown({ city, schools }) {
  return (
    <details name="school-city" className="group w-[268px]">
      <summary className="flex h-12 w-[268px] cursor-pointer list-none items-center justify-between gap-4 rounded-2xl border border-[#582FF5] px-4 py-2 font-[family-name:var(--font-manrope)] text-[14px] font-medium text-[#595959] transition-colors group-[[open]]:bg-[#582FF5] group-[[open]]:text-white [&::-webkit-details-marker]:hidden">
        <span>{city}</span>
        <img
          src="/arrow.svg"
          alt=""
          width={20}
          height={20}
          className="h-5 w-5 shrink-0 transition-transform group-[[open]]:rotate-180 group-[[open]]:brightness-0 group-[[open]]:invert"
        />
      </summary>
      <ul className="flex flex-col gap-1 py-2 pl-4">
        {schools.map((school) => (
          <li key={school}>
            <a
              href={`/p/${slugify(school)}`}
              className="block font-[family-name:var(--font-manrope)] text-[13px] transition-all hover:font-bold"
            >
              {school}
            </a>
          </li>
        ))}
      </ul>
    </details>
  );
}
