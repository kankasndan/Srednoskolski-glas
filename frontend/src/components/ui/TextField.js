import { labelClass, fieldClass } from "@/lib/fieldStyles";

export default function TextField({
  id,
  label,
  required = false,
  placeholder,
  maxLength,
  value,
  onChange,
  error,
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
        value={value}
        onChange={onChange}
        maxLength={maxLength}
        placeholder={placeholder}
        aria-invalid={error ? true : undefined}
        aria-describedby={error ? `${id}-error` : undefined}
        className={`${fieldClass} text-[#000000] placeholder:text-[#595959] ${
          error ? "border-[var(--color-error)] focus:border-[var(--color-error)]" : ""
        }`}
      />
      {error && (
        <p
          id={`${id}-error`}
          className="-mt-1 font-(family-name:--font-manrope) text-[12px] leading-[16px] text-[var(--color-error)] 2xl:text-[14px]"
        >
          {error}
        </p>
      )}
    </div>
  );
}
