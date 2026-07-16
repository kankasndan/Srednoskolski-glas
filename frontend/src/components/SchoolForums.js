import CityDropdown from "@/components/CityDropdown";
import { CITIES } from "@/lib/schools";

export default function SchoolForums({ selectedKey, onSelect }) {
  return (
    <div className="mt-8 flex w-[268px] flex-col">
      <h2 className="mb-3 w-[268px] font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-none text-black">
        Училишни форуми
      </h2>
      <div className="flex flex-col gap-2">
        {CITIES.map(({ city, schools }) => (
          <CityDropdown
            key={city}
            city={city}
            schools={schools}
            selectedKey={selectedKey}
            onSelect={onSelect}
          />
        ))}
      </div>
    </div>
  );
}
