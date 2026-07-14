import { labelClass, fieldClass } from "@/components/fieldStyles";

export default function SelectField({
  id,
  label,
  required = false,
  value,
  onChange,
  placeholder,
  options,
}) {
  return (
    <div className="flex flex-col gap-2">
      <label htmlFor={id} className={labelClass}>
        {required && <span className="text-red-500">*</span>}
        {label}
      </label>
      <div className="relative">
        <select
          id={id}
          name={id}
          required={required}
          value={value}
          onChange={onChange}
          className={`${fieldClass} appearance-none pr-11 ${
            value ? "text-[#000000]" : "text-[#595959]"
          }`}
        >
          <option value="" disabled>
            {placeholder}
          </option>
          {options.map((name) => (
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
