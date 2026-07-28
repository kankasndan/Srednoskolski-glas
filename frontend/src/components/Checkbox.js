
export default function Checkbox({
  checked,
  onChange,
  required,
  className = "",
  children,
}) {
  return (
    <label className={`flex cursor-pointer items-center gap-2 ${className}`}>
      <input
        type="checkbox"
        checked={checked}
        onChange={onChange}
        required={required}
        className="peer sr-only"
      />
      <span
        className={`flex size-4 shrink-0 items-center justify-center rounded-[4px] border transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[var(--color-primary-200)] 2xl:size-5 ${
          checked
            ? "border-[var(--color-primary-200)] bg-[var(--color-primary-200)]"
            : "border-[#595959]"
        }`}
      >
        {checked && <CheckIcon />}
      </span>
      {children}
    </label>
  );
}

                                                                                                                              
function CheckIcon() {
  return (
    <svg
      viewBox="0 0 6.95997 5.33333"
      fill="none"
      aria-hidden="true"
      className="w-2 text-white"
    >
      <path
        d="M6.95997 0.560775L2.18742 5.33333L0 3.14591L0.560775 2.58513L2.18742 4.2078L6.3992 0L6.95997 0.560775Z"
        fill="currentColor"
      />
    </svg>
  );
}
