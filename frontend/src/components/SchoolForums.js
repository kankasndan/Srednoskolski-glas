import CityDropdown from "@/components/CityDropdown";

export default function SchoolForums({ schoolsByCity, loading, error, selectedKey, onSelect }) {
  return (
    <div className="mt-8 flex w-[268px] flex-col gap-2">
      <h2 className="w-[268px] font-[family-name:var(--font-manrope)] text-[16px] font-bold leading-none text-black">
        Училишни форуми
      </h2>
      {loading ? (
        <p className="w-[268px] font-[family-name:var(--font-manrope)] text-[13px] font-normal text-[#595959]">
          Се вчитува…
        </p>
      ) : error ? (
        <p className="w-[268px] font-[family-name:var(--font-manrope)] text-[13px] font-normal text-[#595959]">
          Не успеа вчитувањето на училишните форуми.
        </p>
      ) : (
        schoolsByCity.map(({ city, forums }) => (
          <CityDropdown
            key={city.id}
            city={city.name}
            forums={forums}
            selectedKey={selectedKey}
            onSelect={onSelect}
          />
        ))
      )}
    </div>
  );
}
