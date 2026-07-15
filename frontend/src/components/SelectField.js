import { labelClass, fieldClass } from "@/components/fieldStyles";

export default function SelectField({
  id,
  label,
  required = false,
  value,
  onChange,
  placeholder,
  options,
  groups,
  disabled = false,
  tooltip,
}) {
  return (
    <div className="flex flex-col gap-2">
      <label htmlFor={id} className={labelClass}>
        {required && <span className="text-red-500">*</span>}
        {label}
      </label>
      <div className="group relative">
        {tooltip && (
          <div className="pointer-events-none absolute bottom-full left-1/2 z-20 mb-2 w-max max-w-[240px] -translate-x-1/2 rounded-lg bg-[#0A0A0A] px-3 py-2 text-center font-(family-name:--font-manrope) text-[12px] leading-snug text-white opacity-0 transition-opacity group-hover:opacity-100">
            {tooltip}
            <div className="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#0A0A0A]" />
          </div>
        )}
        <select
          id={id}
          name={id}
          required={required}
          disabled={disabled}
          value={value}
          onChange={onChange}
          className={`${fieldClass} appearance-none pr-11 disabled:cursor-not-allowed disabled:bg-[#F5F5F5] disabled:text-[#B3B3B3] ${
            value ? "text-[#000000]" : "text-[#595959]"
          }`}
        >
          <option value="" disabled>
            {placeholder}
          </option>
          {groups
            ? groups.map(({ city, schools }) => (
                <optgroup key={city} label={city.toUpperCase()}>
                  {schools.map((school) => (
                    <option
                      key={`${city}-${school}`}
                      value={`${school} — ${city}`}
                      className="text-[#000000]"
                    >
                      {school}
                    </option>
                  ))}
                </optgroup>
              ))
            : options.map((name) => (
                <option key={name} value={name} className="text-[#000000]">
                  {name}
                </option>
              ))}
        </select>
        <img
          src="/chevron-down.svg"
          alt=""
          aria-hidden="true"
          className="pointer-events-none absolute right-4 top-1/2 h-5 w-5 -translate-y-1/2 2xl:h-6 2xl:w-6"
        />
      </div>
    </div>
  );
}
