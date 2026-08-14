
export default function Checkbox({
  checked,
  onChange,
  required,
  className = "",
  boxClassName = "size-4 rounded-[4px] 2xl:size-5",
  checkClassName = "w-2",
  children,
}) {
  return (
    <label className={`flex h-[20px] w-[400px] max-w-full cursor-pointer items-center gap-2 ${className}`}>
      <input
        type="checkbox"
        checked={checked}
        onChange={onChange}
        required={required}
        className="peer sr-only"
      />
      <span
        className={`flex shrink-0 items-center justify-center border transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[var(--color-primary-200)] ${boxClassName} ${
          checked
            ? "border-[var(--color-primary-200)] bg-[var(--color-primary-200)]"
            : "border-[#595959]"
        }`}
      >
        {checked && <CheckIcon className={checkClassName} />}
      </span>
      {children}
    </label>
  );
}

                                                                                                                              
function CheckIcon({ className }) {
  return (
    <svg
      viewBox="0 0 6.95997 5.33333"
      fill="none"
      aria-hidden="true"
      className={`${className} text-white`}
    >
      <path
        d="M6.95997 0.560775L2.18742 5.33333L0 3.14591L0.560775 2.58513L2.18742 4.2078L6.3992 0L6.95997 0.560775Z"
        fill="currentColor"
      />
    </svg>
  );
}
