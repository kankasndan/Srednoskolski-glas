import CityDropdown from "@/components/CityDropdown";
import { CITIES } from "@/lib/schools";

export default function SchoolForums() {
  return (
    <div className="mt-8 flex w-[268px] flex-col gap-2">
      <h2 className="w-[268px] font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-none text-black">
        Училишни форуми
      </h2>
      {CITIES.map(({ city, schools }) => (
        <CityDropdown key={city} city={city} schools={schools} />
      ))}
    </div>
  );
}
