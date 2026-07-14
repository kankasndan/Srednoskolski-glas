import { labelClass, fieldClass } from "@/components/fieldStyles";

export default function TextField({
  id,
  label,
  required = false,
  placeholder,
  minLength,
  maxLength,
}) {
  return (
    <div className="flex flex-col gap-2">
      <label htmlFor={id} className={labelClass}>
        {required && <span className="text-red-500">*</span>}
        {label}
      </label>
      <input
        id={id}
        name={id}
        type="text"
        required={required}
        minLength={minLength}
        maxLength={maxLength}
        placeholder={placeholder}
        className={`${fieldClass} text-[#000000] placeholder:text-[#595959]`}
      />
    </div>
  );
}
